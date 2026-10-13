<?php
/**
 * Русский языковой файл плагина Parsedown.
 *
 * Имена ключей $L['cfg_<name>'] должны совпадать с именами опций
 * в BEGIN_COT_EXT_CONFIG файла parsedown.setup.php — иначе Cotonti
 * не покажет подписи настроек в админке.
 *
 * @package    Parsedown
 * @subpackage Localization
 * @author     webitproff
 * @copyright  (c) 2026 webitproff
 * @license    Cotonti License
 * @link       https://github.com/webitproff/parsedown-cotonti
 * @version    1.0.1
 * @since      1.0.0
 */

defined('COT_CODE') or die('Wrong URL');

$L['parsedown_description'] = 'Парсер Markdown на основе Parsedown 1.8.0. Включайте только один парсер контента, иначе возможны конфликты.';

// safemode
$L['cfg_safemode']          = 'Безопасный режим';
$L['cfg_safemode_hint']     = 'Экранировать HTML и отфильтровывать небезопасные URL. Настоятельно рекомендуется включить при работе с пользовательским контентом.';

// breaks
$L['cfg_breaks']            = 'Перенос строк';
$L['cfg_breaks_hint']       = 'Одиночный перевод строки считать как &lt;br&gt; (стиль GitHub).';

// markupescaped
$L['cfg_markupescaped']     = 'Запретить HTML';
$L['cfg_markupescaped_hint']= 'Полностью отключить обработку встроенной HTML-разметки в Markdown.';

// urlslinked
$L['cfg_urlslinked']        = 'Автоссылки';
$L['cfg_urlslinked_hint']   = 'Автоматически превращать голые URL в кликабельные ссылки.';

// anchorfix
$L['cfg_anchorfix']         = 'Якорные ссылки';
$L['cfg_anchorfix_hint']    = 'Преобразовывать ссылки вида #anchor в полный URI текущей страницы.';