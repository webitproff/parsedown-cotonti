<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=global
[END_COT_EXT]
==================== */
/**
 * Parsedown — обход бага ядра modules/pm/inc/pm.functions.php:33.
 *
 * Cotonti хранит конфиг парсеров плоско: Cot::$cfg['plugin']['parsedown'][<ключ>],
 * без вложенного ключа ['editor']. Этот ключ есть только у редакторов
 * (elrte, epiceditor). Однако ядро в pm.functions.php читает его
 * без проверки isset на вложенном уровне, что даёт PHP-предупреждение
 * "Undefined array key 'editor'" при каждой загрузке модуля pm.
 *
 * Global-хук срабатывает раньше модуля pm и успевает создать ключ
 * до обращения ядра. Значение 'parsedown' используется только для
 * совместимости с проверками ядра ($editor != 'elrte' и т.п.).
 *
 * @package    Parsedown
 * @subpackage CoreWorkaround
 * @author     webitproff
 * @copyright  (c) 2026 webitproff
 * @license    Cotonti License
 * @link       https://github.com/webitproff/parsedown-cotonti
 * @version    1.0.1
 * @since      1.0.1
 */
defined('COT_CODE') or die('Wrong URL');

/*
 * Обход бага ядра modules/pm/inc/pm.functions.php:33.
 * Ядро читает Cot::$cfg['plugin'][<parser>]['editor'] без isset на вложенном
 * ключе. У плагинов-парсеров такого ключа нет — он есть только у редакторов.
 * Global-хук срабатывает раньше модуля pm, поэтому успеваем создать ключ.
 */
if (!isset(Cot::$cfg['plugin']['parsedown']['editor'])) {
    Cot::$cfg['plugin']['parsedown']['editor'] = 'parsedown';
}