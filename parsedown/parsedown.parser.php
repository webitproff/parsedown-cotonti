<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=parser
[END_COT_EXT]
==================== */

/**
 * Parsedown 1.8.0 — коннектор парсера Markdown для Cotonti.
 *
 * Cotonti при выборе парсера `parsedown` сам подключает этот файл
 * (см. cot_parse() в system/functions.php) и затем вызывает функцию
 * cot_parse_parsedown($text). Файл НЕ должен ничего парсить на верхнем
 * уровне — только объявлять функцию, иначе ядро упадёт с ошибкой
 * "Call to undefined function cot_parse_parsedown()".
 *
 * @package    Parsedown
 * @subpackage Parser
 * @author     webitproff
 * @copyright  (c) 2026 webitproff
 * @license    Cotonti License (коннектор), MIT (библиотека Parsedown)
 * @link       https://github.com/webitproff/parsedown-cotonti
 * @version    1.0.1
 * @since      1.0.0
 */

defined('COT_CODE') or die('Wrong URL');

/*
 * Объявляем функцию только если её ещё нет.
 * Cotonti гарантирует однократный include файла за запрос,
 * но проверка защищает от двойного объявления, если файл
 * случайно подключён где-то ещё.
 */
if (!function_exists('cot_parse_parsedown')) {

    /**
     * Парсит Markdown-текст в HTML через Parsedown 1.8.0.
     *
     * Тип ?string: Cotonti может передать NULL из полей БД —
     * без `?` PHP 8.x бросит TypeError до входа в тело функции.
     *
     * @param  ?string $text Исходный текст в формате Markdown
     * @return string        Безопасный HTML
     */
    function cot_parse_parsedown(?string $text): string
    {
        // 1. Пустой вход — выходим сразу, не создавая объект парсера.
        //    Это экономит работу, если за запрос парсер вызовется
        //    с пустыми полями (что бывает часто в списках).
        if ($text === null || $text === '') {
            return '';
        }

        /*
         * 2. Статический кэш объекта Parsedown.
         *
         *    static-переменная живёт между вызовами функции в пределах
         *    одного HTTP-запроса. На типичной странице (список статей,
         *    комментарии) функция может быть вызвана десятки раз —
         *    без static каждый вызов делал бы new Parsedown() и заново
         *    применял настройки. Со static объект создаётся один раз.
         *
         *    Имя $pd выбрано намеренно, чтобы не путать со служебной
         *    переменной $parser, которую Cotonti прокидывает в хуки.
         */
        static $pd = null;

        if ($pd === null) {

            // 3. Подключаем библиотеку Parsedown.
            //    Путь строится от актуальной папки плагинов Cotonti —
            //    так плагин продолжит работать, если админ перенёс
            //    установку или сменил plugins_dir.
            $lib = Cot::$cfg['plugins_dir'] . '/parsedown/lib/Parsedown.php';

            // 4. Аварийный выход, если библиотека отсутствует.
            //    Отдаём текст с экранированием HTML — это безопаснее,
            //    чем вернуть сырой Markdown, который потом кто-то
            //    отрендерит как HTML.
            if (!is_file($lib)) {
                return htmlspecialchars(
                    $text,
                    ENT_QUOTES | ENT_SUBSTITUTE,
                    'UTF-8'
                );
            }

            /*
             * require_once защищает от повторного подключения файла
             * внутри одного запроса (например, если другой плагин
             * уже подключил Parsedown раньше).
             */
            require_once $lib;

            // 5. Создаём экземпляр парсера.
            $pd = new Parsedown();

            /*
             * 6. Читаем настройки плагина из админки.
             *
             *    Ключи совпадают с BEGIN_COT_EXT_CONFIG в
             *    parsedown.setup.php. Используем ?? [] — так код
             *    не упадёт, если плагин только что установлен, но
             *    настройки ещё не записаны в конфиг.
             */
            $cfg = Cot::$cfg['plugin']['parsedown'] ?? [];

            /*
             * 7. Применяем настройки к парсеру.
             *
             *    setSafeMode      — экранировать HTML и фильтровать
             *                       небезопасные URL. Самая важная
             *                       опция, защищающая от XSS.
             *    setBreaksEnabled — одиночный перенос строки трактуется
             *                       как <br> (GitHub-стиль).
             *    setMarkupEscaped — полностью отключить обработку
             *                       встроенного HTML в Markdown.
             *    setUrlsLinked    — авто-превращение голых URL в ссылки.
             *                       Дефолт включён: если ключ вообще не
             *                       задан — считаем опцию включённой.
             */
            $pd->setSafeMode(!empty($cfg['safemode']));
            $pd->setBreaksEnabled(!empty($cfg['breaks']));
            $pd->setMarkupEscaped(!empty($cfg['markupescaped']));
            $pd->setUrlsLinked(
                !isset($cfg['urlslinked']) || !empty($cfg['urlslinked'])
            );
        }

        // 8. Непосредственно парсинг. Parsedown::text() возвращает
        //    строку HTML с уже сбалансированными тегами.
        return $pd->text($text);
    }
}