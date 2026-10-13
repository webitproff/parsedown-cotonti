<?php
/**
 * English language file for the Parsedown plugin.
 *
 * Key names $L['cfg_<name>'] must match option names in
 * BEGIN_COT_EXT_CONFIG of parsedown.setup.php — otherwise Cotonti
 * will not show labels in the admin panel.
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

$L['parsedown_description'] = 'Markdown parser based on Parsedown 1.8.0. Enable only one content parser to avoid conflicts.';

$L['cfg_safemode']          = 'Safe mode';
$L['cfg_safemode_hint']     = 'Escape HTML and filter unsafe URLs. Strongly recommended for user-generated content.';

$L['cfg_breaks']            = 'Line breaks';
$L['cfg_breaks_hint']       = 'Treat a single newline as &lt;br&gt; (GitHub style).';

$L['cfg_markupescaped']     = 'Escape markup';
$L['cfg_markupescaped_hint']= 'Completely disable processing of inline HTML markup.';

$L['cfg_urlslinked']        = 'Auto-link URLs';
$L['cfg_urlslinked_hint']   = 'Automatically convert bare URLs into clickable links.';

$L['cfg_anchorfix']         = 'Anchor fix';
$L['cfg_anchorfix_hint']    = 'Convert #anchor links to the full URI of the current page.';