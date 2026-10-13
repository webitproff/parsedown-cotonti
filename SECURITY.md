
# Безопасность плагина Parsedown Markdown для Cotonti
___
**Руководство по безопасности сайта при использовании плагина Parsedown Markdown**
___

> **Файл:** `SECURITY.md`
> 
> **Репозиторий:** https://github.com/webitproff/parsedown-cotonti
> 
> **Относится к версии:** 1.0.1 и выше
> 
> **Обновлено:** 2026-10-13
> 
___


1. Как устроена защита контента в связке **Parsedown + HTML Purifier**.
2. Какие правки внесены в **плагин HTML Purifier** и **зачем**.
3. Какие правки внесены в **пресеты HTML Purifier** и **зачем**.
4. Правила эксплуатации, чтобы защита работала.
5. Что делать, если что-то не работает.

---

## Содержание

- [1. Модель угроз](#1-модель-угроз)
- [2. Двухслойная архитектура защиты](#2-двухслойная-архитектура-защиты)
- [3. Что изменено в плагине HTML Purifier](#3-что-изменено-в-плагине-html-purifier)
- [4. Что изменено в пресетах HTML Purifier](#4-что-изменено-в-пресетах-html-purifier)
- [5. Пошаговое руководство по применению правок](#5-пошаговое-руководство-по-применению-правок)
- [6. Правила эксплуатации](#6-правила-эксплуатации)
- [7. Чек-лист после установки](#7-чек-лист-после-установки)
- [8. Диагностика](#8-диагностика)
- [9. Что делать при инциденте](#9-что-делать-при-инциденте)
- [10. Контакты и обратная связь](#10-контакты-и-обратная-связь)

---

## 1. Модель угроз

Parsedown сам по себе — **парсер**, а не санитайзер. Если использовать его без
дополнительной защиты, злоумышленник через контент может:

| Вектор | Что делает | Последствия |
|---|---|---|
| `<script>` | Исполняет JS в браузере читателя | Кража cookie, сессии, перенаправление, кейлоггер |
| `onclick`, `onerror`, `onload` | То же, что `<script>`, но через атрибут | То же |
| `javascript:` в `href` | Исполняет JS при клике по ссылке | То же |
| `<iframe src="/admin.php">` | Прозрачное встраивание админки | Действия от имени жертвы (CSRF) |
| `<style>body{display:none}</style>` | Ломает вёрстку сайта | DoS для читателей |
| `<meta http-equiv="refresh">` | Автоматический редирект | Уводит на фишинг |
| `<base href="//evil.example">` | Переопределяет базовый URL | Все относительные ссылки ведут на чужой домен |
| `<form>`, `<input>` | Подделка форм сайта | Фишинг, CSRF |
| `<svg><script>...` | XSS через SVG | То же, что `<script>` |
| `data:text/html;base64,...` | Исполняемый HTML в ссылке | XSS |

**Задача системы:** гарантированно вырезать всё из этого списка, при этом
не мешая легитимному Markdown и безопасной HTML-вёрстке.

---

## 2. Двухслойная архитектура защиты

Плагин Parsedown и плагин HTML Purifier работают **последовательно** в одном
запросе:

```
Пользователь пишет Markdown
        │
        ▼
Сохранение в БД  ─────────────────────────────────────────┐
   (сырой Markdown + возможный сырой HTML)               │
        │                                                 │
        ▼                                                 │
Вывод страницы                                            │
   cot_parse($text, true, 'parsedown')                    │
        │                                                 │
        ▼                                                 │
Слой 1: Parsedown                                         │
   • превращает Markdown → HTML                           │
   • safemode=1 экранирует встроенный HTML                │
   • safemode=0 пропускает HTML как есть                  │
        │                                                 │
        ▼                                                 │
Слой 2: HTML Purifier (cot_import_filters['HTM'])         │
   • вырезает всё опасное из получившегося HTML           │
   • применяет whitelist тегов и атрибутов                │
   • применяет чёрный список                             │
   • применяет URI-whitelist                             │
        │                                                 │
        ▼                                                 │
Готовый безопасный HTML → шаблон                          │
        │                                                 │
        ▼                                                 │
Браузер читателя                                          │
```

**Ключевое правило:** если один слой что-то пропустит — второй закроет.
Никогда не полагайтесь только на один слой.

---

## 3. Что изменено в плагине HTML Purifier

### 3.1. Файл `plugins/htmlpurifier/htmlpurifier.php`

**Проблема:** в оригинале функция `htmlpurifier_filter()` работала только
если активен парсер `html`:

```php
// Было (оригинал):
if (Cot::$sys['parser'] == 'html') {
    // фильтрация ...
} else {
    return $value;   // ← при parsedown фильтр молча ничего не делал
}
```

При переходе на `parsedown` в качестве активного парсера условие
**никогда не выполнялось**, фильтр не запускался, и в БД летел сырой HTML.

**Решение:** расширить условие так, чтобы фильтр работал и для
`parsedown` (и любых других парсеров, которые могут понадобиться):

```php
// Стало:
if (in_array(Cot::$sys['parser'], ['html', 'parsedown'], true)) {
    // фильтрация ...
} else {
    return $value;
}
```

**Где менять:** `plugins/htmlpurifier/htmlpurifier.php`, функция
`htmlpurifier_filter()`, самая первая строка тела функции.

**Что это даёт:** HTML Purifier теперь срабатывает и тогда, когда активен
парсер `parsedown`. Это обязательно для нормальной работы всей цепочки.

### 3.2. Если добавляете свой парсер

Если когда-нибудь понадобится подключить ещё один парсер (например,
`commonmark`), достаточно дописать его имя в массив:

```php
if (in_array(Cot::$sys['parser'], ['html', 'parsedown', 'commonmark'], true)) {
```

Порядок элементов не важен — важен сам факт наличия.

### 3.3. Полный текст функции `htmlpurifier_filter()` после правки

Для сверки — вот как должна выглядеть функция целиком после применения
правки. Начало файла (`defined('COT_CODE') or die()`, комментарии,
подключение библиотеки) оставляем без изменений, правится **только
условие `if`**.

```php
<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=global
[END_COT_EXT]
==================== */

defined('COT_CODE') or die('Wrong URL');

function htmlpurifier_filter($value, $name = '')
{
    if (in_array(Cot::$sys['parser'], ['html', 'parsedown'], true)) {

        static $purifier = null;

        // Lazy loading to save performance
        if (is_null($purifier)) {
            define('HTMLPURIFIER_PREFIX', Cot::$cfg['plugins_dir'] . '/htmlpurifier/lib/standalone');
            require_once Cot::$cfg['plugins_dir'] . '/htmlpurifier/lib/HTMLPurifier.standalone.php';

            $cacheDir = Cot::$cfg['cache_dir'] . DIRECTORY_SEPARATOR . 'htmlpurifier';
            if (!file_exists($cacheDir)) {
                mkdir($cacheDir, 0775, true);
            }
            $cacheDir = realpath($cacheDir);

            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.TidyLevel', Cot::$cfg['plugin']['htmlpurifier']['tidylevel']);

            // protects theme IDs from duplication
            if ('nemesis' == Cot::$usr['theme']) {
                $config->set('Attr.IDBlacklist', [
                    'main', 'footer', 'account', 'powered', 'magnifer',
                    'search', 'header', 'nav', 'slider', 'front_image'
                ]);
            }

            if (
                isset(Cot::$cfg['plugin']['htmlpurifier']['videoiframe']) &&
                Cot::$cfg['plugin']['htmlpurifier']['videoiframe'] &&
                Cot::$usr['id'] > 0
            ) {
                $config->set('HTML.SafeIframe', true);
                $config->set(
                    'URI.SafeIframeRegexp',
                    '%^(http:|https:)?//(www.youtube(?:-nocookie)?.com/embed/|player.vimeo.com/video/)%'
                );
            }

            $config->set('URI.Base', COT_ABSOLUTE_URL);
            $config->set('URI.Host', Cot::$sys['domain']);

            if (Cot::$cfg['plugin']['htmlpurifier']['rel2abs']) {
                $config->set('URI.MakeAbsolute', true);
            }
            $config->set('Cache.SerializerPath', $cacheDir);

            // Load preset
            if (Cot::$usr['id'] > 0) {
                $preset_name = 'group_' . Cot::$usr['maingrp'];
                if (!file_exists(
                    Cot::$cfg['plugins_dir'] . '/htmlpurifier/presets/htmlpurifier.'
                    . "$preset_name.preset.php"
                )) {
                    $preset_name = 'default';
                }
            } else {
                $preset_name = 'group_1';
            }
            require_once Cot::$cfg['plugins_dir']
                . "/htmlpurifier/presets/htmlpurifier.$preset_name.preset.php";

            /* config extension */
            foreach (cot_getextplugins('htmlpurifier.config') as $pl) {
                include $pl;
            }

            foreach ($htmlpurifier_preset as $key => $val) {
                $config->set($key, $val);
            }

            $doctype = Cot::$cfg['plugin']['htmlpurifier']['doctype'];

            if ('HTML5' == $doctype) {
                require_once cot_incfile('htmlpurifier', 'plug', 'html5');
            } else {
                $config->set('HTML.Doctype', $doctype);
            }

            $purifier = new HTMLPurifier($config);
        }

        return $purifier->purify($value);

    } else {
        return $value;
    }
}

$cot_import_filters['HTM'][] = 'htmlpurifier_filter';
```

**Что изменилось относительно оригинала:** только строка

```php
if (Cot::$sys['parser'] == 'html') {
```

заменена на

```php
if (in_array(Cot::$sys['parser'], ['html', 'parsedown'], true)) {
```

Всё остальное — без изменений.

### 3.4. Как HTML Purifier применяется к полям

Важно понимать: **в настройках плагина HTML Purifier списка полей нет.**
Плагин регистрируется одним вызовом:

```php
$cot_import_filters['HTM'][] = 'htmlpurifier_filter';
```

Фильтр `htmlpurifier_filter()` срабатывает автоматически **при каждом**
вызове `cot_import()` с третьим параметром `'HTM'`. Например, в модуле
`page`:

```php
$rpage['page_text'] = cot_import('rpage_text', 'P', 'HTM');
//                                              ^^^
//                                    вот здесь срабатывает HTML Purifier
```

Это значит: **если поле импортируется с фильтром `'HTM'` — оно
фильтруется; если с `'TXT'` или другим — не фильтруется.** Никаких
«списков полей» в админке HTML Purifier нет и не было.

---

## 4. Что изменено в пресетах HTML Purifier

Оба пресета (`default` и `group_5`) полностью переписаны так, чтобы
гарантированно резать опасное **даже при `HTML.Trusted = true`**.
Оригинальные настройки сохранены в Блоке 1 каждого файла; всё, что
добавлено, помечено отдельными блоками и прокомментировано.

### 4.1. Файл `htmlpurifier.default.preset.php`

Пресет для всех, кроме админов. Применяется по умолчанию, если у группы
пользователя нет собственного файла `htmlpurifier.group_N.preset.php`.

| Блок | Что добавлено | Зачем |
|---|---|---|
| **2** | `HTML.Allowed` — жёсткий whitelist | Всё, чего нет в списке, вырезается даже при ошибочном `Trusted = true` |
| **3** | `HTML.ForbiddenElements` — 17 тегов | `script`, `style`, `link`, `meta`, `base`, `form`, `input`, `iframe`, `object`, `embed`, `svg`, `math`, `canvas` и т.д. |
| **4** | `HTML.ForbiddenAttributes` — `*@on*` и др. | Убивает `onclick`, `onerror`, `onload` и сотни других обработчиков |
| **5** | `URI.AllowedSchemes` — whitelist схем | Отсекает `javascript:`, `vbscript:`, `data:text/html`, `file:` |
| **6** | `HTML.SafeIframe` + regexp | Разрешает `<iframe>` только из YouTube/Vimeo/VK |
| **7** | `Attr.AllowedFrameTargets`, `Attr.EnabledID` | Урезает `target` до `_blank`, запрещает `id` у юзеров |
| **8** | Отключены `SafeObject`, `SafeEmbed`, `FlashCompat` | Устаревшие технологии встраивания закрыты |

**Что НЕ пострадало:** базовое форматирование из Markdown — `p`, `br`,
`strong`, `em`, `del`, `ul`, `ol`, `li`, `blockquote`, `pre`, `code`,
`h1–h6`, `table`, `thead`, `tbody`, `tr`, `th`, `td`, `hr`, `a`, `img`.

**Полный текст файла, который нужно положить в
`plugins/htmlpurifier/presets/htmlpurifier.default.preset.php`:**

```php
<?php
/**
 * ============================================================================
 * HTML Purifier — пресет по умолчанию (fallback)
 * ============================================================================
 *
 * Файл:    plugins/htmlpurifier/presets/htmlpurifier.default.preset.php
 * Тип:     резервный пресет для всех групп без своего файла
 *          htmlpurifier.group_N.preset.php
 *
 * Задача:  пропустить безопасный HTML (жирный, курсив, списки, таблицы,
 *          ссылки, картинки) и при этом гарантированно вырезать всё, чем
 *          можно атаковать сайт или читателя — с учётом того, что
 *          контент парсится Parsedown (Markdown → HTML).
 *
 * ВАЖНО: пресет автоматически применяется ко всем полям, которые
 * импортируются в Cotonti с фильтром 'HTM' (см. вызовы cot_import
 * в модулях page, comments, forums и т.д.).
 *
 * @package    HTML Purifier
 * @subpackage Presets
 * @author     Cotonti Team
 * @copyright  (c) Cotonti Team
 * @license    https://github.com/Cotonti/Cotonti/blob/master/License.txt
 * @link       http://htmlpurifier.org/live/configdoc/plain.html
 * ============================================================================
 */

defined('COT_CODE') or die('Wrong URL');

$htmlpurifier_preset = [

    /* ========================================================================
     * БЛОК 1. ОРИГИНАЛЬНЫЕ НАСТРОЙКИ — ОСТАВЛЕНЫ КАК ЕСТЬ
     * ====================================================================== */

    // Автоформатирование
    'AutoFormat.AutoParagraph'                => false,
    'AutoFormat.DisplayLinkURI'               => false,
    'AutoFormat.Linkify'                      => false,
    'AutoFormat.RemoveEmpty.RemoveNbsp'       => false,
    'AutoFormat.RemoveEmpty'                  => true,
    'AutoFormat.RemoveSpansWithoutAttributes' => true,

    // Пользовательские фильтры
    'Filter.Custom'                           => [],

    // HTML и вывод.
    // null переопределяется ниже Блоком 2.
    'HTML.Allowed'                            => null,

    'HTML.FlashAllowFullScreen'               => false,
    'HTML.SafeObject'                         => true,

    // Режим доверия — для пользовательского контента только false
    'HTML.Trusted'                            => false,

    'Output.FlashCompat'                      => true,

    // URI
    'URI.DisableExternal'                     => false,
    'URI.DisableExternalResources'            => false,


    /* ========================================================================
     * БЛОК 2. ЯВНЫЙ WHITELIST ТЕГОВ
     * ====================================================================== */
    'HTML.Allowed'                            =>
          'p[align|style|class],br,'
        . 'div[align|class|style|id],'
        . 'span[class|style],'
        . 'strong,b,em,i,u,s,strike,del,ins,sup,sub,small,big,'
        . 'a[href|title|rel|target|class],'
        . 'img[src|alt|title|width|height|class|style],'
        . 'ul[class|style],ol[class|style],li[class|style],'
        . 'blockquote[class|style|cite],'
        . 'pre[class|style],code[class|style],'
        . 'h1[class|style|id],h2[class|style|id],h3[class|style|id],'
        . 'h4[class|style|id],h5[class|style|id],h6[class|style|id],'
        . 'table[class|style|border|cellpadding|cellspacing],'
        . 'thead[class],tbody[class],tfoot[class],'
        . 'tr[class|style],'
        . 'th[class|style|colspan|rowspan],'
        . 'td[class|style|colspan|rowspan],'
        . 'hr[class|style],'
        . 'kbd,mark,abbr[title],cite,figure,figcaption,details,summary',


    /* ========================================================================
     * БЛОК 3. ЧЁРНЫЙ СПИСОК ТЕГОВ
     * ====================================================================== */
    'HTML.ForbiddenElements'                  => [
        'script',
        'style',
        'link',
        'meta',
        'base',
        'form',
        'input',
        'textarea',
        'select',
        'option',
        'button',
        'iframe',
        'object',
        'embed',
        'applet',
        'canvas',
        'svg',
        'math',
    ],


    /* ========================================================================
     * БЛОК 4. ЧЁРНЫЙ СПИСОК АТРИБУТОВ
     * ====================================================================== */
    'HTML.ForbiddenAttributes'                => [
        '*@on*',
        '*@formaction',
        '*@xmlns',
        '*@srcdoc',
        'a@ping',
        'a@download',
    ],


    /* ========================================================================
     * БЛОК 5. WHITELIST СХЕМ ССЫЛОК
     * ====================================================================== */
    'URI.AllowedSchemes'                      => [
        'http'   => true,
        'https'  => true,
        'mailto' => true,
        'tel'    => true,
    ],


    /* ========================================================================
     * БЛОК 6. WHITELIST ДЛЯ IFRAME
     * ====================================================================== */
    'HTML.SafeIframe'                         => true,
    'URI.SafeIframeRegexp'                    => '%^(https?:)?//('
        . 'www\.youtube(?:-nocookie)?\.com/embed/|'
        . 'player\.vimeo\.com/video/|'
        . 'vk\.com/video_ext\.php'
        . ')%i',


    /* ========================================================================
     * БЛОК 7. КОНТРОЛЬ АТРИБУТОВ
     * ====================================================================== */
    'Attr.AllowedFrameTargets'                => ['_blank'],
    'Attr.AllowedRel'                         => ['nofollow', 'noreferrer', 'noopener'],
    'Attr.EnableID'                           => false,
    'Attr.AllowedClasses'                     => null,


    /* ========================================================================
     * БЛОК 8. FLASH / OBJECT / EMBED — ВЫКЛЮЧЕНО
     * ====================================================================== */
    'HTML.FlashAllowFullScreen'               => false,
    'HTML.SafeObject'                         => false,
    'HTML.SafeEmbed'                          => false,
    'Output.FlashCompat'                      => false,
    'HTML.Nofollow'                           => false,
    'Filter.YouTube'                          => false,
];
```

### 4.2. Файл `htmlpurifier.group_5.preset.php`

Пресет для суперадминов (группа 5). Даёт свободу верстки, но закрывает
опасное вручную, потому что `HTML.Trusted = true` отключает большинство
встроенных проверок Purifier'а.

| Блок | Что добавлено | Зачем |
|---|---|---|
| **2** | `HTML.ForbiddenElements` — расширен с `['script']` до полного списка | При `Trusted = true` только этот список ловит `<iframe>`, `<style>`, `<form>`, `<svg>` и прочее |
| **3** | `HTML.ForbiddenAttributes` — `*@on*`, `*@srcdoc`, `a@ping`, `a@download` | При `Trusted = true` без этого списка `onclick` проходит |
| **4** | `URI.AllowedSchemes` | При `Trusted = true` Purifier не проверяет схемы — нужен явный whitelist |
| **5** | `HTML.SafeIframe` + regexp | `<iframe src="//evil.example">` режется; YouTube/Vimeo/VK — проходят |
| **6** | Отключены `SafeObject`, `SafeEmbed`, `FlashCompat` | Ослабляют защиту при `Trusted = true` |

**Что сохранено для админа:**
- `<p align="center">`, `<div class="...">`, `<span style="...">` — врезки.
- `<kbd>`, `<mark>`, `<abbr>`, `<details>`, `<summary>` — кастомные теги.
- `<iframe>` из YouTube/Vimeo/VK — встраивание видео.
- `id` у элементов — якорные ссылки `#section`.
- `class` — любые классы (Bootstrap, тема и т.п.).

**Что вырезано даже у админа:** `<script>`, `onclick`, `javascript:`,
`<iframe src="/admin.php">`, `<style>`, `<form>`, `<object>`, `<svg>`.

**Полный текст файла, который нужно положить в
`plugins/htmlpurifier/presets/htmlpurifier.group_5.preset.php`:**

```php
<?php
/**
 * ============================================================================
 * HTML Purifier — пресет для суперадминов (MainGroupId = 5)
 * ============================================================================
 *
 * Файл:    plugins/htmlpurifier/presets/htmlpurifier.group_5.preset.php
 * Задача:  дать администратору свободу верстки (врезки, центрирование,
 *          классы, id, встроенные видео), но закрыть реально опасные
 *          конструкции: XSS, авторедиректы, подмену вёрстки и форм.
 *
 * ВАЖНО: пресет автоматически применяется ко всем полям, которые
 * импортируются в Cotonti с фильтром 'HTM' (см. вызовы cot_import
 * в модулях page, comments, forums и т.д.).
 *
 * @package    HTML Purifier
 * @subpackage Presets
 * @author     Cotonti Team
 * @copyright  (c) Cotonti Team
 * @license    https://github.com/Cotonti/Cotonti/blob/master/License.txt
 * @link       http://htmlpurifier.org/live/configdoc/plain.html
 * ============================================================================
 */

defined('COT_CODE') or die('Wrong URL');

require_once Cot::$cfg['plugins_dir']
    . '/htmlpurifier/lib/standalone/HTMLPurifier/Filter/YouTube.php';

$htmlpurifier_preset = [

    /* ========================================================================
     * БЛОК 1. ОРИГИНАЛЬНЫЕ НАСТРОЙКИ
     * ====================================================================== */

    'AutoFormat.AutoParagraph'                => false,
    'AutoFormat.DisplayLinkURI'               => false,
    'AutoFormat.Linkify'                      => false,
    'AutoFormat.RemoveEmpty.RemoveNbsp'       => false,
    'AutoFormat.RemoveEmpty'                  => true,
    'AutoFormat.RemoveSpansWithoutAttributes' => true,

    'Filter.Custom'                           => [],

    'HTML.Allowed'                            => null,

    'HTML.FlashAllowFullScreen'               => true,
    'HTML.Nofollow'                           => true,
    'HTML.SafeObject'                         => true,
    'HTML.SafeEmbed'                          => true,

    'HTML.Trusted'                            => true,

    'HTML.ForbiddenElements'                  => ['script'],

    'Output.FlashCompat'                      => true,
    'Filter.YouTube'                          => true,

    'URI.DisableExternal'                     => false,
    'URI.DisableExternalResources'            => false,

    'Attr.AllowedFrameTargets'                => ['_blank', '_self', '_parent', '_top'],
    'Attr.EnableID'                           => true,


    /* ========================================================================
     * БЛОК 2. ЧЁРНЫЙ СПИСОК ТЕГОВ — ПЕРЕЗАПИСЫВАЕТ ОРИГИНАЛ
     * ====================================================================== */
    'HTML.ForbiddenElements'                  => [
        'script',
        'style',
        'link',
        'meta',
        'base',
        'form',
        'input',
        'textarea',
        'select',
        'option',
        'button',
        'object',
        'embed',
        'applet',
        'canvas',
        'svg',
        'math',
    ],


    /* ========================================================================
     * БЛОК 3. ЧЁРНЫЙ СПИСОК АТРИБУТОВ
     * ====================================================================== */
    'HTML.ForbiddenAttributes'                => [
        '*@on*',
        '*@formaction',
        '*@xmlns',
        '*@srcdoc',
        'a@ping',
        'a@download',
    ],


    /* ========================================================================
     * БЛОК 4. WHITELIST СХЕМ ССЫЛОК
     * ====================================================================== */
    'URI.AllowedSchemes'                      => [
        'http'   => true,
        'https'  => true,
        'mailto' => true,
        'tel'    => true,
    ],


    /* ========================================================================
     * БЛОК 5. WHITELIST ДЛЯ IFRAME
     * ====================================================================== */
    'HTML.SafeIframe'                         => true,
    'URI.SafeIframeRegexp'                    => '%^(https?:)?//('
        . 'www\.youtube(?:-nocookie)?\.com/embed/|'
        . 'player\.vimeo\.com/video/|'
        . 'vk\.com/video_ext\.php'
        . ')%i',


    /* ========================================================================
     * БЛОК 6. FLASH / OBJECT / EMBED — ВЫКЛЮЧЕНО
     * ====================================================================== */
    'HTML.FlashAllowFullScreen'               => false,
    'HTML.SafeObject'                         => false,
    'HTML.SafeEmbed'                          => false,
    'Output.FlashCompat'                      => false,
    'HTML.Nofollow'                           => false,
    'Filter.YouTube'                          => false,


    /* ========================================================================
     * БЛОК 7. АТРИБУТЫ
     * ====================================================================== */
    'Attr.AllowedFrameTargets'                => ['_blank'],
    'Attr.AllowedRel'                         => ['nofollow', 'noreferrer', 'noopener'],
    'Attr.EnableID'                           => true,
    'Attr.AllowedClasses'                     => null,
];
```

### 4.3. Почему `HTML.Trusted = true` остался в пресете админа

Технически `Trusted = true` снимает почти все внутренние фильтры HTML
Purifier. Это компромисс: он нужен, чтобы админ мог верстать врезки и
использовать кастомные теги без переписывания белого списка под каждый
случай.

Защита при `Trusted = true` держится исключительно на:
- `HTML.ForbiddenElements` (Блок 2)
- `HTML.ForbiddenAttributes` (Блок 3)
- `URI.AllowedSchemes` (Блок 4)
- `URI.SafeIframeRegexp` (Блок 5)

Если хотя бы один из этих блоков убрать — админский пресет станет
уязвимым. **Не удаляйте эти блоки, не понимая последствий.**

---

## 5. Пошаговое руководство по применению правок

Ниже — точная последовательность действий. Выполняйте по порядку.
Не пропускайте шаги.

### Шаг 1. Резервная копия

**Что сделать:**

1. Скачайте с сервера и сохраните у себя три файла:
   - `plugins/htmlpurifier/htmlpurifier.php`
   - `plugins/htmlpurifier/presets/htmlpurifier.default.preset.php`
   - `plugins/htmlpurifier/presets/htmlpurifier.group_5.preset.php`

**Зачем:** если что-то пойдёт не так — вернёте как было.

### Шаг 2. Правка `htmlpurifier.php`

**Файл:** `plugins/htmlpurifier/htmlpurifier.php`

**Что сделать:**

1. Откройте файл в редакторе.
2. Найдите функцию `htmlpurifier_filter()`.
3. Найдите в её теле **самую первую строку-условие**:

   ```php
   if (Cot::$sys['parser'] == 'html') {
   ```

4. Замените её на:

   ```php
   if (in_array(Cot::$sys['parser'], ['html', 'parsedown'], true)) {
   ```

5. Сохраните файл.

**Проверка:** поиском по файлу найдите строку

```
in_array(Cot::$sys['parser'], ['html', 'parsedown'], true)
```

Если нашлась — правка применена.

**Что НЕ надо менять в этом файле:** всё остальное содержимое функции
`htmlpurifier_filter()`, подключение библиотеки, `$cot_import_filters['HTM'][]`.

### Шаг 3. Проверка `htmlpurifier.config.php`

**Файл:** `plugins/htmlpurifier/htmlpurifier.config.php`

**Что сделать:**

1. Проверьте, есть ли такой файл в папке плагина.
2. Если **есть** — откройте и поищите строки:

   ```php
   'HTML.Trusted'   => true,
   'HTML.Allowed'   => null,
   ```

3. Если эти строки есть, то **файл нужно удалить** (или переименовать в
   `htmlpurifier.config.php.bak`). Иначе он перезапишет оба пресета, и
   правки из Шагов 4–5 не сработают.
4. Если файла нет — переходите к Шагу 4.

**Зачем:** `htmlpurifier.config.php` подключается хуком
`htmlpurifier.config` **после** загрузки пресета и **перезаписывает**
любые его настройки. Пока он есть — он управляет защитой, а не ваш пресет.

### Шаг 4. Замена `htmlpurifier.default.preset.php`

**Файл:** `plugins/htmlpurifier/presets/htmlpurifier.default.preset.php`

**Что сделать:**

1. Откройте файл.
2. Удалите **всё** его содержимое.
3. Вставьте полный текст из раздела [4.1](#41-файл-htmlpurifierdefaultpresetphp)
   этого документа — от `<?php` до последней закрывающей `];`.
4. Сохраните файл.

**Проверка:** в файле должны быть блоки:

- `'HTML.Trusted' => false`
- `'HTML.Allowed' => 'p[align|style|class], ...'` (не `null`)
- `'HTML.ForbiddenElements' => [...]`
- `'HTML.ForbiddenAttributes' => [...]`
- `'URI.AllowedSchemes' => [...]`

### Шаг 5. Замена `htmlpurifier.group_5.preset.php`

**Файл:** `plugins/htmlpurifier/presets/htmlpurifier.group_5.preset.php`

**Что сделать:**

1. Откройте файл.
2. Удалите **всё** его содержимое.
3. Вставьте полный текст из раздела [4.2](#42-файл-htmlpurifiergroup_5presetphp)
   этого документа — от `<?php` до последней закрывающей `];`.
4. Сохраните файл.

**Проверка:** в файле должны быть блоки:

- `'HTML.Trusted' => true`
- `'HTML.ForbiddenElements' => ['script', 'style', ...]` (расширенный, не только `script`)
- `'HTML.ForbiddenAttributes' => ['*@on*', ...]`
- `'URI.AllowedSchemes' => [...]`
- `'URI.SafeIframeRegexp' => ...`

### Шаг 6. Установка/переустановка плагина HTML Purifier

**Что сделать:**

1. Откройте **Админ-панель** → **Расширения**.
2. Найдите **HTML Purifier**.
3. Нажмите **Удалить**.
4. Нажмите **Установить**.

**Зачем:** при удалении/установке Cotonti пересобирает реестр
`$cot_plugins` и подхватывает свежую правку в `htmlpurifier.php`.

**Внимание:** не перепутайте HTML Purifier с Parsedown Markdown. Это два
разных плагина, и каждый устанавливается отдельно.

### Шаг 7. Как HTML Purifier применяется к полям

Фильтрация включается через **параметр `'HTM'`** в вызовах `cot_import()`
внутри модулей Cotonti.

**Что сделать, что бы перепроверить:**

1. Откройте файл модуля, где сохраняется контент:
   - `modules/page/page.edit.php` — для страниц (здесь просто для примера)

2. Найдите строку импорта нужного поля. Например, для текста страницы:

   ```php
   $rpage['page_text'] = cot_import('rpage_text', 'P', 'HTM');
   ```

3. Убедитесь, что третий параметр — `'HTM'`. Если там `'TXT'` или
   что-то другое — HTML Purifier к этому полю **не применяется**, и
   правки в `htmlpurifier.php` и пресетах ничего не изменят.

**Если нужно, чтобы фильтровалось другое поле** — поменяйте фильтр в
соответствующем модуле:

```php
// Было:
$rdata['some_field'] = cot_import('some_field', 'P', 'TXT');

// Стало:
$rdata['some_field'] = cot_import('some_field', 'P', 'HTM');
```

**Проверка:** откройте файл модуля, поиском найдите название нужного
поля и убедитесь, что рядом стоит `'HTM'`.

### Шаг 8. Очистка кэша Cotonti

**Что сделать:**

1. Откройте **Админ-панель** → **Главная**.
2. Нажмите **Очистить кэш**.

**Зачем:** пресеты и настройки плагина кэшируются. Без очистки кэша
изменения могут не подхватиться.

### Шаг 9. Проверка парсера

**Что сделать:**

1. Откройте **Админ-панель** → **Конфигурация** → **Парсер**.
2. Убедитесь, что выбран **`parsedown`**.
3. Убедитесь, что **не выбраны** одновременно другие парсеры
   (`html`, `markitup`, `ckeditor` и т.п.).
4. Сохраните.

### Шаг 10. Включение Safe mode у Parsedown (для публичного сайта)

**Что сделать:**

1. Откройте **Админ-панель** → **Плагины** → **Parsedown Markdown**.
2. Включите опцию **Safe mode** (`safemode = 1`).
3. При необходимости включите **Escape markup** (`markupescaped = 1`).
4. Сохраните.

**Зачем:** Safe mode — это первый независимый слой защиты. HTML Purifier —
второй. Включать оба — правильно.

### Шаг 11. Тестирование

**Что сделать:**

1. Откройте сайт под **обычным пользователем** (не админом).
2. Создайте страницу или комментарий с содержимым:

   ```html
   <script>alert(1)</script>
   <div onclick="alert(2)">click</div>
   <iframe src="//evil.example"></iframe>
   <style>body{display:none}</style>
   <a href="javascript:alert(3)">link</a>
   <img src="x" onerror="alert(4)">
   ```

3. Сохраните.
4. Откройте сохранённую страницу в браузере.
5. Нажмите **Ctrl+U** (исходник HTML) и найдите опасные конструкции.
6. Откройте консоль браузера (**F12**).

**Ожидаемый результат:**

- Ни одного `alert()` не всплыло.
- В исходнике HTML нет `<script>`, `<iframe>`, `onclick`, `onerror`,
  `javascript:`, `<style>`.
- Легитимные теги (`<p>`, `<strong>`, `<ul>`, `<a href="https://...">`)
  остались на месте.

### Шаг 12. Если проверка не прошла

Смотрите раздел [8. Диагностика](#8-диагностика). Там описано,
в каком порядке проверять шаги 1–10 и на что обращать внимание.

### Шаг 13. Обновление старых записей (опционально)

Если в БД есть старые записи с внедрённым HTML (сделанные **до** установки
защиты), они уже не опасны при выводе — HTML Purifier их вычистит при
рендере. Но если хотите очистить и сами записи в БД, есть два пути:

**Путь A — вручную.** Открыть каждую подозрительную запись в редакторе и
пересохранить — тогда фильтрация произойдёт при сохранении (если у вас
настроен хук сохранения).

**Путь B — SQL.** Найти подозрительные записи:

```sql
SELECT page_id, page_title
FROM cot_pages
WHERE page_text LIKE '%<script%'
   OR page_text LIKE '%<iframe%'
   OR page_text LIKE '%onerror%'
   OR page_text LIKE '%onclick%';
```

Перезаписать каждую вручную через админку.

> Автоматическая очистка старых записей **не входит** в этот плагин.
> Она делается отдельным хуком `page.edit.update` или скриптом.

---

## 6. Правила эксплуатации

### 6.1. Обязательные

1. **Плагин HTML Purifier должен быть установлен и активен.**
   Без него Parsedown сам по себе не санитайзер, и любой XSS пройдёт.

2. **В `htmlpurifier.php` должна быть правка из раздела 3.1.**
   Проверьте строку:
   ```php
   if (in_array(Cot::$sys['parser'], ['html', 'parsedown'], true)) {
   ```
   Если там `if (Cot::$sys['parser'] == 'html')`, фильтр не сработает.

3. **Поля контента должны импортироваться с фильтром `'HTM'`.**
   Проверьте в модулях (`page.admin.php`, `comments.php`, `forums.post.php`),
   что вызовы `cot_import()` для нужных полей используют третий параметр
   `'HTM'`. Иначе HTML Purifier к этим полям не применяется.

4. **Пресеты (`default` и `group_5`) должны быть заполнены целиком.**
   Не удаляйте блоки 2–8 — они и есть защита.

5. **Выбран только один парсер контента.**
   Одновременное использование `parsedown` + `markitup` + `ckeditor`
   в активном парсере даёт непредсказуемое поведение.

### 6.2. Рекомендуемые

1. **Держите `safemode = 1` в настройках Parsedown** для любого
   пользовательского контента (комментарии, форум, гостевые страницы).
   Это первый независимый слой защиты.

2. **Ограничьте доступ к HTML-редактированию** — только доверенные
   группы. В настройках Cotonti это делается через права групп.

3. **Регулярно обновляйте Cotonti и оба плагина.**

4. **Настройте логирование ошибок фильтрации.** Если HTML Purifier
   что-то вырезал у легитимного контента — это сигнал пересмотреть
   whitelist, а не отключать защиту.

5. **Периодически проверяйте контент в БД.** Например, SQL-запрос:
   ```sql
   SELECT page_id, page_title
   FROM cot_pages
   WHERE page_text REGEXP '<script|<iframe|on[a-z]+=|javascript:';
   ```
   Если такие записи есть — они появятся из старых версий контента
   до установки защиты. Их нужно перезаписать вручную.

### 6.3. Запрещено

1. **Ставить `HTML.Trusted = true` в `default`-пресете.**
   Это откроет дыру для всех пользователей сразу.

2. **Ставить `HTML.Allowed = null` в `default`-пресете.**
   По умолчанию Purifier использует довольно широкую политику —
   лучше явный whitelist.

3. **Отключать `HTML.ForbiddenAttributes`.**
   Без `*@on*` в этом списке `onclick="fetch('/admin.php')"`
   пройдёт у любого, кто может писать контент.

4. **Разрешать `<script>` и `<style>` в `HTML.Allowed`.**
   Даже временно, даже «для теста».

5. **Разрешать `javascript:` и `data:text/html` в `URI.AllowedSchemes`.**
   Первое — XSS, второе — XSS через ссылку.

6. **Использовать `HTML.SafeObject` и `HTML.SafeEmbed` при
   `HTML.Trusted = true`.** Их ослабление бессмысленно, если рядом
   включён `Trusted`.

### 6.4. Про отключение защиты для админа

Если по какой-то причине потребуется дать конкретному пользователю
**полную** свободу HTML без фильтрации (например, техническая страница
с интерактивным JS) — не делайте этого через пресет. Вместо этого:

- Создайте отдельный шаблон, куда контент выводится через
  `{PAGE_TEXT}` **без** `cot_parse()` (сырой HTML из поля).
- Ограничьте доступ к этой странице только своей группой.
- Никогда не выводите так пользовательский контент.

---

## 7. Чек-лист после установки

Проверьте по пунктам. Если что-то не так — фильтр не работает.

- [ ] Плагин **HTML Purifier** установлен и активен.
- [ ] В `htmlpurifier.php` есть `in_array(Cot::$sys['parser'], ['html', 'parsedown'], true)`.
- [ ] Файл `htmlpurifier.config.php` отсутствует (или не перезаписывает `Trusted`).
- [ ] Файл `htmlpurifier.default.preset.php` содержит `'HTML.Trusted' => false` и явный `HTML.Allowed`.
- [ ] Файл `htmlpurifier.default.preset.php` содержит блоки 2–8.
- [ ] Файл `htmlpurifier.group_5.preset.php` содержит расширенный `HTML.ForbiddenElements`.
- [ ] Файл `htmlpurifier.group_5.preset.php` содержит `HTML.ForbiddenAttributes` со `*@on*`.
- [ ] Поля контента в модулях (`page.admin.php`, `comments.php`, `forums.post.php`) импортируются через `cot_import(..., 'HTM')`.
- [ ] В **Конфигурация → Парсер** выбран `parsedown`.
- [ ] В настройках Parsedown включён **Safe mode** (для публичного сайта).
- [ ] Кэш Cotonti очищен (Админка → Главная → Очистить кэш).
- [ ] Плагин HTML Purifier переустановлен после правок
      (uninstall → install).

### Тест-проверка

Создайте страницу с содержимым (под **обычным пользователем**, не админом):

```html
<script>alert(1)</script>
<div onclick="alert(2)">click</div>
<iframe src="//evil.example"></iframe>
<style>body{display:none}</style>
<a href="javascript:alert(3)">link</a>
<img src="x" onerror="alert(4)">
```

Сохраните. Откройте страницу в браузере. Откройте консоль.

**Ожидаемый результат:**
- никаких `alert()` не всплывает;
- в исходнике HTML нет `<script>`, `<iframe>`, `onclick`, `onerror`,
  `javascript:`, `<style>`;
- опасные теги заменены либо удалены.

**Если что-то прошло** — смотрите раздел 8.

---

## 8. Диагностика

### 8.1. Проблема: опасный код попадает в БД

**Это нормально при фильтрации на выводе.** HTML Purifier в Cotonti
срабатывает при импорте полей через `cot_import(..., 'HTM')`, а также
хук `parser.last` в момент рендера страницы. В `page_text` может
лежать сырой `<script>` — он будет вырезан при выводе.

Проверяйте не БД, а **исходник HTML готовой страницы**: Ctrl+U в
браузере.

**Если хотите фильтровать на сохранении** — нужен отдельный хук
`page.edit.update`, который прогонит `page_text` через Purifier до
записи в БД. Это отдельная задача, не входящая в текущий плагин.

### 8.2. Проблема: опасный код попадает в HTML страницы

Значит, фильтр не применяется к полю. Проверьте:

1. **Поле импортируется с фильтром `'HTM'`?**
   Откройте модуль, найдите `cot_import()` для нужного поля, убедитесь,
   что третий параметр — `'HTM'`. Если там `'TXT'` — фильтр не работает.

2. **Правка в `htmlpurifier.php` внесена?**
   Смотрите раздел 3.1.

3. **Кэш очищен?**
   Админка → Главная → Очистить кэш.

4. **Плагин переустановлен?**
   После правки `htmlpurifier.php` нужно переустановить плагин, чтобы
   изменения подхватились.

5. **Файл `htmlpurifier.config.php` перезаписывает настройки?**
   Смотрите Шаг 3 в разделе 5. Если файл есть и содержит
   `'HTML.Trusted' => true`, его нужно удалить.

### 8.3. Проблема: легитимный контент вырезается

Например, ссылка на картинку с внешнего домена не работает, или
врезка `<div class="alert">` исчезает.

Причина — слишком строгий whitelist. Решается **точечным** расширением
соответствующего блока, а не отключением защиты целиком.

| Симптом | Где править |
|---|---|
| Пропала врезка `<div class="...">` | `HTML.Allowed` — убедиться, что `div[class]` есть |
| Не работает картинка с внешнего CDN | `URI.DisableExternalResources => false` в пресете |
| Пропал `<kbd>` или `<mark>` | Добавить в `HTML.Allowed` |
| Не работает `target="_blank"` | `Attr.AllowedFrameTargets` |
| Не работают якорные ссылки у юзера | `Attr.EnableID => true` (осторожно, ломает вёрстку) |
| Не сохраняется YouTube-видео у админа | Проверить `URI.SafeIframeRegexp` и что домен в списке |

### 8.4. Проблема: ошибка `Call to undefined function htmlpurifier_filter()`

Плагин HTML Purifier не подключён. Админка → Расширения → установить.

### 8.5. Проблема: ошибка `Class 'HTMLPurifier' not found`

Библиотека HTML Purifier повреждена или отсутствует. Переустановите
плагин HTML Purifier.

### 8.6. Быстрая проверка: логирование

Временная правка `htmlpurifier.php` для отладки. Вставьте в начало
функции `htmlpurifier_filter()`:

```php
if (!empty($value)) {
    file_put_contents(
        Cot::$cfg['cache_dir'] . '/purifier.log',
        date('c') . " parser=" . Cot::$sys['parser']
            . " in=" . strlen($value)
            . " out=" . strlen(htmlspecialchars($value))
            . "\n",
        FILE_APPEND
    );
}
```

Откройте страницу с опасным контентом. Посмотрите
`datas/cache/purifier.log`:

- **Файл появился** → фильтр вызывается, проблема в самом пресете.
- **Файла нет** → фильтр не вызывается вообще. Проверьте правку в
  `htmlpurifier.php` и что поле импортируется с `'HTM'`.

**Не забудьте удалить правку после отладки.**

---

## 9. Что делать при инциденте

Если подозреваете, что XSS уже был использован:

1. **Сменить все пароли** — админские и пользовательские.
2. **Проверить БД на предмет внедрённого `<script>`**:
   ```sql
   SELECT page_id, page_title, page_text
   FROM cot_pages
   WHERE page_text LIKE '%<script%'
      OR page_text LIKE '%<iframe%'
      OR page_text LIKE '%onerror%'
      OR page_text LIKE '%onclick%';
   ```
3. **Перезаписать подозрительные записи**, прогнав их через актуальный
   HTML Purifier вручную.
4. **Проверить логи Cotonti** — Админка → Логи, а также `system/log/`.
5. **Проверить, что пресеты не правились.** Сравните с эталонными
   версиями из раздела 4 этого документа.
6. **Обновить Cotonti, плагины, PHP** до актуальных стабильных версий.
7. **Включить `safemode = 1`** в настройках Parsedown, если ещё не
   включён.

---

## 10. Контакты и обратная связь

Обнаружили уязвимость или обход защиты — сообщите через
[Issues](https://github.com/webitproff/parsedown-cotonti/issues) или
приватно в профиле автора.

При сообщении укажите:
- версию плагина;
- версию Cotonti;
- версию PHP;
- шаги воспроизведения;
- минимальный пример контента.

Пожалуйста, **не публикуйте уязвимость публично** до выхода патча.
