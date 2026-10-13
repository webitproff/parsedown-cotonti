<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=parser.last
[END_COT_EXT]
==================== */

/**
 * Постобработка якорных ссылок для Parsedown.
 *
 * Хук parser.last срабатывает в конце cot_parse() — уже после того,
 * как основная функция парсера вернула HTML. Здесь мы правим ссылки
 * вида href="#anchor" на абсолютный URL текущей страницы, чтобы они
 * корректно работали внутри модулей Cotonti (page, forums и т.п.).
 *
 * @package    Parsedown
 * @subpackage Anchor fix
 * @author     webitproff
 * @copyright  (c) 2026 webitproff
 * @license    Cotonti License
 * @link       https://github.com/webitproff/parsedown-cotonti
 * @version    1.0.1
 * @since      1.0.0
 */

defined('COT_CODE') or die('Wrong URL');

/*
 * Если markup отключён ($plain === true), Cotonti уже сделал
 * htmlspecialchars + nl2br, и в тексте нет реальных <a>. Пропускаем.
 */
if (!empty($plain)) {
    return;
}

/*
 * Хук parser.last общий для всех парсеров. Работаем только с нашим —
 * иначе будем вмешиваться в чужой HTML.
 */
if (!isset($parser) || $parser !== 'parsedown') {
    return;
}

/*
 * Уважаем настройку anchorfix из setup.php. Если админ её выключил —
 * оставляем якоря как есть.
 */
if (empty(Cot::$cfg['plugin']['parsedown']['anchorfix'])) {
    return;
}

// Нечего обрабатывать на пустом тексте.
if (empty($text)) {
    return;
}

/*
 * Ищем в готовом HTML атрибут href="#..." и подставляем к нему
 * текущий URI страницы. Cot::$sys['uri_curr'] — относительный URI
 * без ведущего слэша, ровно то, что нужно для внутренних якорей.
 */
$text = preg_replace(
    '`<a(\s.*?) href="(#.*?)"`i',
    '<a$1 href="' . Cot::$sys['uri_curr'] . '$2"',
    $text
);