
# Parsedown Markdown для Cotonti

[![Version](https://img.shields.io/badge/version-1.0.1-blue.svg)](https://github.com/webitproff/parsedown-cotonti/releases)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](./LICENSE.txt)
[![Cotonti](https://img.shields.io/badge/Cotonti-1.x-green.svg)](https://www.cotonti.com/)
[![PHP](https://img.shields.io/badge/PHP-7.3%2B%20%7C%208.5-777bb4.svg)](https://www.php.net/)
[![Parsedown](https://img.shields.io/badge/Parsedown-1.8.0-orange.svg)](https://github.com/erusev/parsedown/releases/tag/1.8.0)
[![Composer](https://img.shields.io/badge/composer-not%20required-lightgrey.svg)](#-установка)

Бесплатный плагин-парсер **Markdown** для CMS [Cotonti](https://www.cotonti.com/), построенный на библиотеке **[Parsedown 1.8.0](https://github.com/erusev/parsedown/releases/tag/1.8.0)**.

Плагин регистрируется в Cotonti как `editor-parser`, легко устанавливается через админку, **не требует Composer** и совместим с современным PHP вплоть до 8.5.

- **Репозиторий:** https://github.com/webitproff/parsedown-cotonti
- **Автор коннектора:** [webitproff](https://github.com/webitproff)
- **Лицензия:** MIT
- **Стоимость:** бесплатно

---

## Содержание

- [Возможности](#-возможности)
- [Требования](#-требования)
- [Скриншоты](#-скриншоты)
- [Установка](#-установка)
- [Настройка](#-настройка)
- [Как это работает](#-как-это-работает)
- [Структура файлов](#-структура-файлов)
- [Безопасность](#-безопасность)
- [Совместимость с PHP 8.5](#-совместимость-с-php-85)
- [Хуки Cotonti](#-хуки-cotonti)
- [Использование в шаблонах](#-использование-в-шаблонах)
- [Расширение плагина](#-расширение-плагина)
- [FAQ](#-faq)
- [Устранение неполадок](#-устранение-неполадок)
- [История версий](#-история-версий)
- [Лицензия](#-лицензия)
- [Благодарности](#-благодарности)

---

## ✨ Возможности

- 📝 **Полная поддержка Markdown** через Parsedown 1.8.0 — заголовки, списки, таблицы, цитаты, блоки кода, ссылки, изображения, сноски, автоссылки.
- 🔌 **Простая установка** — копирование папки + штатная установка через админку Cotonti.
- 🚫 **Без Composer** — библиотека Parsedown лежит внутри плагина и подключается напрямую.
- 🛡️ **Safe mode** — экранирование HTML и фильтрация небезопасных URL-схем для защиты от XSS.
- ⚙️ **5 гибких настроек** в админке: `safemode`, `breaks`, `markupescaped`, `urlslinked`, `anchorfix`.
- ⚓ **Anchor fix** — автоматическое превращение ссылок `#anchor` в полный URI страницы (важно для внутренних переходов в Cotonti).
- 🚀 **Производительность** — статический кэш объекта парсера в пределах одного запроса; на странице со списком из сотен записей `Parsedown` создаётся один раз.
- 🌍 **Локализация** — русский и английский языковые файлы «из коробки».
- 🐘 **PHP 8.5-ready** — учтены все требования современного PHP: nullable-типы, актуальные константы, отсутствие устаревших конструкций.
- 🆓 **MIT-лицензия** — можно свободно использовать, изменять и распространять.

---

## 📦 Требования

| Компонент | Минимальная версия | Примечание |
|---|---|---|
| **Cotonti** | 1.x (актуальная ветка 0.9.x / 1.0.x) | Плагин использует класс `Cot`, хук `parser`, реестр `$cot_plugins['parser']` |
| **PHP** | 7.3 | Проверено на 8.5 |
| **Расширение PHP** | `mbstring` | Требуется самим Cotonti |
| **Плагин `htmlpurifier`** | — | Настоятельно рекомендуется как дополнительный слой защиты |
| **Composer** | **не требуется** | Библиотека включена в поставку |

---

## 🖼 Скриншоты

> _Раздел будет заполнен после первого публичного релиза._
> 
> Планируется добавить:
> - скриншот страницы списка плагинов с установленным Parsedown;
> - скриншот страницы настроек с 5 опциями;
> - пример рендера Markdown в модуле «Страницы».

---

## 🚀 Установка

### Шаг 1. Скачивание

Скачайте последнюю версию:

```bash
git clone https://github.com/webitproff/parsedown-cotonti.git
```

Или скачайте ZIP-архив со страницы [Releases](https://github.com/webitproff/parsedown-cotonti/releases) и распакуйте.

### Шаг 2. Копирование файлов

Скопируйте папку `parsedown/` в директорию плагинов Cotonti:

```
<корень-сайта>/plugins/parsedown/
```

Итоговая структура:

```
plugins/parsedown/
├── parsedown.setup.php
├── parsedown.parser.php
├── parsedown.anchor.php
├── LICENSE.txt
├── README.md
├── lang/
│   ├── parsedown.ru.lang.php
│   └── parsedown.en.lang.php
└── lib/
    └── Parsedown.php
```

### Шаг 3. Проверка библиотеки

Убедитесь, что файл `plugins/parsedown/lib/Parsedown.php` существует и содержит класс `Parsedown` версии 1.8.0. Если папка `lib/` пуста — скачайте библиотеку отдельно:

```bash
# Из корня плагина
curl -L -o lib/Parsedown.php \
  https://raw.githubusercontent.com/erusev/parsedown/1.8.0/Parsedown.php
```

### Шаг 4. Установка через админку

1. Зайдите в **Админ-панель** → **Расширения**.
2. Найдите в списке **«Parsedown Markdown»** (категория `editor-parser`).
3. Нажмите **«Установить»**.

### Шаг 5. Активация как парсера

1. Перейдите в **Админ-панель** → **Конфигурация** → **Парсер**.
2. В выпадающем списке выберите **`parsedown`**.
3. Сохраните.

> ⚠️ **Важно!** Убедитесь, что **выбран только один парсер**. Наличие одновременно `parsedown`, `markitup`, `ckeditor` и т.п. в активных парсерах приведёт к неопределённому поведению.

### Шаг 6. Настройка опций (опционально)

Перейдите в **Админ-панель** → **Плагины** → **Parsedown Markdown** → настройте опции под свои задачи.

---

## ⚙️ Настройка

Все опции доступны в **Админ-панель → Плагины → Parsedown Markdown**.

| Опция | Значения | По умолчанию | Описание |
|---|---|---|---|
| **Safe mode** (`safemode`) | 0 / 1 | **0** | Экранирует HTML и фильтрует небезопасные URL-схемы. Включайте, если сайт допускает пользовательский контент. |
| **Line breaks** (`breaks`) | 0 / 1 | **0** | Одиночный перевод строки → `<br>`. Соответствует стилю GitHub Flavored Markdown. |
| **Escape markup** (`markupescaped`) | 0 / 1 | **0** | Полностью отключить обработку встроенного HTML в Markdown. |
| **Auto-link URLs** (`urlslinked`) | 0 / 1 | **1** | Голые URL (`https://...`) автоматически становятся ссылками. |
| **Anchor fix** (`anchorfix`) | 0 / 1 | **1** | Ссылки `#anchor` превращаются в полный URI текущей страницы. Полезно для Cotonti-страниц и форумов. |

### Рекомендуемые сочетания

**Для публичного сайта с пользовательским контентом (блог, форум):**

```
safemode      = 1
breaks        = 1
markupescaped = 1
urlslinked    = 1
anchorfix     = 1
+ активный плагин htmlpurifier
```

**Для внутреннего сайта / авторского блога одного человека:**

```
safemode      = 0
breaks        = 0
markupescaped = 0
urlslinked    = 1
anchorfix     = 1
```

---

## 🔧 Как это работает

### Последовательность вызовов

```
Пользователь открывает страницу
        │
        ▼
Модуль Cotonti (например, page) вызывает cot_parse($text)
        │
        ▼
cot_parse() формирует имя функции: $func = 'cot_parse_parsedown'
        │
        ▼
Ядро проверяет: function_exists('cot_parse_parsedown')?
        │
        ├─── НЕТ → ищет парсер в $cot_plugins['parser']
        │          подключает parsedown.parser.php
        │          файл объявляет функцию cot_parse_parsedown()
        │          ядро вызывает её
        │
        └─── ДА  → вызывает функцию напрямую
        │
        ▼
cot_parse_parsedown($text):
   • возвращает '' для null / пустых строк
   • при первом вызове за запрос:
       – подключает lib/Parsedown.php
       – создаёт new Parsedown()
       – применяет настройки из Cot::$cfg['plugin']['parsedown']
   • парсит текст через $pd->text($text)
        │
        ▼
Хук parser.last подключает parsedown.anchor.php
        │
        ▼
Если anchorfix включён — переписывает href="#..." → полный URI
        │
        ▼
Возврат готового HTML в шаблон
```

### Ключевой момент

Файл `parsedown.parser.php` **только объявляет функцию** `cot_parse_parsedown()` и **ничего не парсит на верхнем уровне файла**. Это требование ядра Cotonti: после `include` ядро само вызовет эту функцию. Если файл начнёт парсить сразу при подключении, ядро упадёт с ошибкой:

```
Call to undefined function cot_parse_parsedown()
```

Это же правило действует для всех парсеров Cotonti (markitup, ckeditor в режиме парсера и т.п.).

### Производительность

Внутри `cot_parse_parsedown()` используется `static $pd` — статический кэш объекта:

```php
static $pd = null;
if ($pd === null) {
    $pd = new Parsedown();
    // ... настройки
}
return $pd->text($text);
```

`static`-переменная живёт между вызовами функции в течение **одного HTTP-запроса**. На типичной странице со списком статей функция может вызваться 20–50 раз; без кэша это было бы 20–50 объектов `Parsedown`, с кэшем — **один**.

---

## 📁 Структура файлов

```
plugins/parsedown/
│
├── parsedown.setup.php          # метаданные и конфиг плагина
│   ├── [BEGIN_COT_EXT]          #   — код, имя, версия, автор
│   └── [BEGIN_COT_EXT_CONFIG]   #   — 5 radio-опций
│
├── parsedown.parser.php         # хук parser: объявляет cot_parse_parsedown()
│   └── function cot_parse_parsedown(?string): string
│
├── parsedown.anchor.php         # хук parser.last: правка якорных ссылок
│
├── lang/
│   ├── parsedown.ru.lang.php    # русские подписи опций
│   └── parsedown.en.lang.php    # английские подписи опций
│
├── lib/
│   └── Parsedown.php            # оригинальная библиотека 1.8.0
│                                # (c) Emanuil Rusev, MIT
│
├── LICENSE.txt                  # лицензия плагина и библиотеки
└── README.md                    # этот файл
```

---

## 🛡 Безопасность

### Что защищает Safe mode

Когда `safemode = 1`, `Parsedown::setSafeMode(true)`:

1. Экранирует **весь встроенный HTML** (`<script>`, `<iframe>`, `<object>` и т.д.).
2. Фильтрует **схемы ссылок** — разрешает только безопасный whitelist:
   `http`, `https`, `ftp`, `ftps`, `mailto`, `tel`, `data:image/*`, `irc`, `ircs`, `git`, `ssh`, `news`, `steam`.
3. Удаляет **все атрибуты** `on*` (например, `onclick`, `onerror`) у встроенного HTML.
4. Удаляет небезопасные **имя тега / атрибуты**, не проходящие whitelist.

### Что Safe mode НЕ защищает

- От **нежелательной разметки** в самом Markdown (спамные ссылки, рекламные изображения) — это задача модерации, а не парсера.
- От **инъекций через CSS-атрибуты** в разрешённых тегах — фильтруется частично.

### Рекомендации

1. **Держите `safemode = 1`** для любого контента, который пишут пользователи (комментарии, форум, гостевые страницы).
2. Дополнительно установите и активируйте **[htmlpurifier](https://github.com/Cotonti/Cotonti/tree/master/plugins/htmlpurifier)** — он очистит HTML **после** парсера, давая второй независимый слой защиты.
3. Регулярно обновляйте Cotonti и плагин.
4. Не включайте одновременно два и более парсера контента — это может привести к конфликтам и обходам защиты.

### Что происходит при отсутствии библиотеки

Если файл `lib/Parsedown.php` не найден (например, повреждена установка), функция `cot_parse_parsedown()` возвращает:

```php
htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
```

То есть текст будет выведен как есть, но с полным экранированием HTML — это **безопасный деградированный режим**, а не ошибка.

---

## 🐘 Совместимость с PHP 8.5

Плагин разработан с учётом современных требований PHP и полностью совместим с PHP 7.3, 7.4, 8.0, 8.1, 8.2, 8.3, 8.4 и **8.5**.

Что учтено:

- **Nullable-типы** — параметр функции типизирован как `?string`, чтобы принять `null` из полей БД без `TypeError`.
- **Строгие возвращаемые типы** — `: string` гарантирует корректный тип на выходе.
- **Константы `ENT_QUOTES | ENT_SUBSTITUTE`** — актуальны и рекомендованы в 8.x.
- **Отсутствие dynamic properties** — код не создаёт свойств у объектов «на лету».
- **Отсутствие обращений к строке по отрицательному индексу** — удалено в PHP 7.1, в 8.x вызывает ошибку.
- **Отсутствие неявно nullable типов** — `function foo(string $x = null)` некорректно, не используется.
- **Отсутствие устаревших функций** — нет `create_function`, `each`, `money_format`, `get_magic_quotes_gpc` и т.п.
- **Отсутствие `utf8_encode` / `utf8_decode`** — помечены deprecated в 8.2, не используются.

### Тестирование

Плагин протестирован на:

| Версия PHP | Результат |
|---|---|
| 7.3 | ✅ OK |
| 7.4 | ✅ OK |
| 8.0 | ✅ OK |
| 8.1 | ✅ OK |
| 8.2 | ✅ OK |
| 8.3 | ✅ OK |
| 8.4 | ✅ OK |
| **8.5** | ✅ OK |

---

## 🪝 Хуки Cotonti

Плагин подписан на два хука:

| Хук | Файл | Назначение |
|---|---|---|
| `parser` | `parsedown.parser.php` | Объявление функции `cot_parse_parsedown()`, вызываемой ядром при парсинге контента |
| `parser.last` | `parsedown.anchor.php` | Постобработка якорных ссылок `#anchor` → полный URI |

### Порядок срабатывания

```
cot_parse()
    │
    ├── основной парсер (наш cot_parse_parsedown)
    │
    └── хук parser.last
            └── parsedown.anchor.php
```

### Отладка

Если нужно понять, был ли парсер реально вызван, включите режим отладки Cotonti:

```php
// config.php
$cfg['debug_mode'] = true;
```

В список выполненных хуков (`$cotHooksFired`) попадёт и `parser`, и `parser.last`.

---

## 📝 Использование в шаблонах

### Автоматический парсинг

Cotonti автоматически применяет активный парсер к контенту в модулях `page`, `forums`, `comments` и других — достаточно выбрать `parsedown` в конфиге.

### Ручной вызов в шаблонах

В XTemplate:

```html
{PAGE_TEXT}
```

Если страница использует поле, к которому парсер применяется автоматически, дополнительных действий не нужно.

### Вызов из плагина / модуля

```php
$html = cot_parse($markdownSource, true, 'parsedown');
```

Параметры `cot_parse()`:

| Параметр | Значение |
|---|---|
| 1 | Исходный текст |
| 2 | `true` — включить разметку, `false` — plain text |
| 3 | Имя парсера; можно указать `'parsedown'` явно |

### Пример кода

```php
<?php
// Внутри своего плагина:
$source = "## Заголовок\n\nТекст с **жирным** и *курсивом*.\n\n- Пункт 1\n- Пункт 2";

$html = cot_parse($source, true, 'parsedown');

// На выходе:
// <h2>Заголовок</h2>
// <p>Текст с <strong>жирным</strong> и <em>курсивом</em>.</p>
// <ul>
// <li>Пункт 1</li>
// <li>Пункт 2</li>
// </ul>
```

---

## 🧩 Расширение плагина

### Добавление своих настроек

1. Откройте `parsedown.setup.php`.
2. Добавьте опцию в блок `[BEGIN_COT_EXT_CONFIG]`:

```ini
[BEGIN_COT_EXT_CONFIG]
safemode=01:radio::0:Safe mode
breaks=02:radio::0:Line breaks
markupescaped=03:radio::0:Escape markup
urlslinked=04:radio::1:Auto-link URLs
anchorfix=05:radio::1:Anchor fix
your_option=06:radio::1:Your label
[END_COT_EXT_CONFIG]
```

3. Добавьте подписи в языковые файлы:

```php
// lang/parsedown.ru.lang.php
$L['cfg_your_option']      = 'Ваша опция';
$L['cfg_your_option_hint'] = 'Описание вашей опции';
```

4. Примените значение в `cot_parse_parsedown()`:

```php
if (!empty($cfg['your_option'])) {
    // ...
}
```

5. Переустановите плагин в админке (uninstall → install).

### Переопределение Parsedown

Если нужна своя логика (например, добавление кастомных inline-паттернов), можно унаследоваться от `Parsedown`:

```php
class MyParsedown extends Parsedown
{
    protected function inlineSpecial($Excerpt)
    {
        // ...
    }
}

$pd = new MyParsedown();
```

Но это требует правки `parsedown.parser.php`. В будущих версиях планируется поддержка пользовательских классов через конфиг.

---

## ❓ FAQ

### Зачем нужен этот плагин, если в Cotonti уже есть markitup?

`markitup` — это **редактор** (визуальная панель кнопок), а не парсер. `parsedown` — именно **парсер** (превращает Markdown в HTML). Их можно использовать вместе: писать в markitup, сохранять как Markdown, а парсить через Parsedown.

### Почему не использовать Composer?

Cotonti традиционно распространяет плагины как **самодостаточные папки**. Требование Composer усложнило бы установку для большинства пользователей CMS, которые не знакомы с ним. Библиотека Parsedown — один файл, и её проще положить внутрь плагина.

### Будет ли работать с другими версиями Parsedown?

Технически — да, API стабильно с версии 1.6. Но плагин поставляется и тестируется именно с **1.8.0** — последним стабильным релизом из ветки 1.x.

### Что делать, если парсер не работает?

См. [Устранение неполадок](#-устранение-неполадок).

### Поддерживается ли Extra (расширенный) Markdown?

Parsedown 1.8.0 реализует **CommonMark** + GitHub Flavored Markdown (таблицы, зачёркивание, автоссылки). Расширения вроде сносок (`[^1]`) или определений (`term\n: definition`) **не поддерживаются**. Для них нужен форк `ParsedownExtra` — на текущий момент не входит в поставку.

### Как обновить плагин?

1. Скачайте новую версию.
2. Замените файлы **кроме** `lib/Parsedown.php` (если версия библиотеки та же).
3. В админке: **Расширения** → **Parsedown Markdown** → **Обновить** (uninstall → install).
4. Настройки сохранятся, так как они лежат в `$cfg['plugin']['parsedown']`.

---

## 🔧 Устранение неполадок

### Ошибка `Call to undefined function cot_parse_parsedown()`

**Причина:** файл `parsedown.parser.php` не объявляет функцию, либо был установлен старый вариант плагина, который парсит текст прямо при подключении.

**Решение:**

1. Убедитесь, что в `parsedown.parser.php` есть блок:
   ```php
   if (!function_exists('cot_parse_parsedown')) {
       function cot_parse_parsedown(?string $text): string { ... }
   }
   ```
2. Переустановите плагин через админку.

### Ошибка `Class 'Parsedown' not found`

**Причина:** файл `lib/Parsedown.php` отсутствует или повреждён.

**Решение:**

```bash
cd plugins/parsedown
curl -L -o lib/Parsedown.php \
  https://raw.githubusercontent.com/erusev/parsedown/1.8.0/Parsedown.php
```

Проверьте, что файл начинается с `<?php` и содержит `class Parsedown`.

### Markdown не парсится, текст выводится как есть

**Возможные причины:**

1. В **Конфигурация → Парсер** не выбран `parsedown`.
2. Выбран другой парсер (например, `none`).
3. Плагин не установлен (статус в списке расширений).
4. Текст выводится через `htmlspecialchars()` без вызова `cot_parse()` — проверьте шаблон.

### Якорные ссылки `#anchor` не работают

**Причина:** опция `anchorfix` выключена.

**Решение:** включите `anchorfix` в настройках плагина.

### HTML-теги внутри Markdown отображаются как текст

**Причина:** включён `markupescaped` или `safemode`.

**Решение:** для доверенного контента выключите обе опции. **Не рекомендуется** для пользовательского контента.

### Ошибка `TypeError: cot_parse_parsedown(): Argument #1 ($text) must be of type string, null given`

**Причина:** установлена старая версия плагина с сигнатурой `string $text` вместо `?string $text`.

**Решение:** обновите `parsedown.parser.php` до актуальной версии (1.0.1+).

---

## 📅 История версий

Полная история — в файле [`CHANGELOG.md`](./CHANGELOG.md) (появится в следующих релизах).

### 1.0.1 — 2026-10-13

- Добавлены подробные комментарии ко всем файлам.
- Добавлены метаданные PHPDoc (`@package`, `@author`, `@copyright`, `@license`, `@link`, `@version`, `@since`).
- Явная совместимость с PHP 8.5: `?string` в сигнатуре, актуальные константы `ENT_QUOTES | ENT_SUBSTITUTE`.
- Улучшена обработка пустого ввода — ранний выход до создания парсера.
- Добавлен статический кэш объекта `Parsedown` для производительности.
- Добавлен `LICENSE.txt`.
- Расширен `README.md`.

### 1.0.0 — 2026-10-13

- Первый публичный релиз.
- Библиотека Parsedown 1.8.0.
- Хуки `parser` и `parser.last`.
- 5 настроек в админке.
- Языковые файлы: русский, английский.

---

## 📄 Лицензия

Проект распространяется под лицензией **MIT**. Полный текст — в файле [`LICENSE.txt`](./LICENSE.txt).

### Кратко

- ✅ Свободное использование, включая коммерческое.
- ✅ Модификация и распространение.
- ✅ Сублицензирование.
- ⚠️ Требуется сохранение copyright notice.
- ❌ Автор не несёт ответственности за последствия использования.

### Библиотека Parsedown

Библиотека `lib/Parsedown.php` — собственность **Emanuil Rusev**, распространяется под MIT. Полный текст: https://github.com/erusev/parsedown/blob/1.8.0/LICENSE.txt

---

## 🙏 Благодарности

- **[Emanuil Rusev](https://github.com/erusev)** — за библиотеку Parsedown, лёгкую, быструю и надёжную.
- **[Cotonti Team](https://github.com/Cotonti)** — за CMS и её расширяемую архитектуру с хуками и парсерами.
- **Сообщество Cotonti** — за обратную связь, тестирование и предложения.

---

## 🔗 Ссылки

- **Репозиторий плагина:** https://github.com/webitproff/parsedown-cotonti
- **Issues:** https://github.com/webitproff/parsedown-cotonti/issues
- **Pull Requests:** https://github.com/webitproff/parsedown-cotonti/pulls
- **Cotonti CMS:** https://www.cotonti.com/
- **Cotonti на GitHub:** https://github.com/Cotonti/Cotonti
- **Parsedown:** https://github.com/erusev/parsedown

---

## 🤝 Участие в разработке

Приветствуются:

- 🐞 Сообщения об ошибках — [Issues](https://github.com/webitproff/parsedown-cotonti/issues)
- 💡 Идеи и пожелания — [Discussions](https://github.com/webitproff/parsedown-cotonti/discussions)
- 🔧 Pull Requests — с описанием, зачем и почему
- 🌍 Новые переводы — присылайте файлы `parsedown.<lang>.lang.php`

### Перед PR

1. Форкните репозиторий.
2. Создайте ветку: `git checkout -b feature/my-feature`.
3. Коммит: `git commit -m "Add: описание"`.
4. Пуш: `git push origin feature/my-feature`.
5. Откройте Pull Request.

---

<p align="center">
  <strong>Parsedown Markdown для Cotonti</strong><br>
  Сделано с ♥ для сообщества Cotonti<br>
  <a href="https://github.com/webitproff/parsedown-cotonti">github.com/webitproff/parsedown-cotonti</a>
</p>


