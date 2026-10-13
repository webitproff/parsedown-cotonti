<?php
/* ====================
[BEGIN_COT_EXT]
Code=parsedown
Name=Parsedown Markdown
Category=editor-parser
Description=Markdown parser (Parsedown 1.8.0) for Cotonti
Version=1.0.1
Date=2026-10-13
Author=webitproff
Copyright=(c) 2026 webitproff. Parsedown library (c) 2013-2025 Emanuil Rusev
Notes=Free plugin: https://github.com/webitproff/parsedown-cotonti. Parsedown library licensed under MIT.
Auth_guests=R
Lock_guests=12345A
Auth_members=RW
Lock_members=12345A
Recommends_plugins=htmlpurifier
[END_COT_EXT]

[BEGIN_COT_EXT_CONFIG]
editor=01:hidden::parsedown:
safemode=02:radio::0:Safe mode
breaks=03:radio::0:Line breaks
markupescaped=04:radio::0:Escape markup
urlslinked=05:radio::1:Auto-link URLs
anchorfix=06:radio::1:Anchor fix
[END_COT_EXT_CONFIG]
==================== */

/**
 * Parsedown Markdown parser for Cotonti — setup file.
 *
 * Метаданные плагина: код, имя, версия, конфигурационные опции.
 * Читается ядром Cotonti при установке/обновлении и при построении
 * страницы настроек в админке.
 *
 * @package    Parsedown
 * @subpackage Setup
 * @author     webitproff
 * @copyright  (c) 2026 webitproff
 * @license    MIT (для библиотеки Parsedown) / Cotonti License (для коннектора)
 * @link       https://github.com/webitproff/parsedown-cotonti
 * @version    1.0.1
 * @since      1.0.0
 */

defined('COT_CODE') or die('Wrong URL');
