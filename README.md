# Parsedown Markdown for Cotonti

[![Version](https://img.shields.io/badge/version-1.0.1-blue.svg)](https://github.com/webitproff/parsedown-cotonti/releases)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](./LICENSE.txt)
[![Cotonti](https://img.shields.io/badge/Cotonti-1.x-green.svg)](https://www.cotonti.com/)
[![PHP](https://img.shields.io/badge/PHP-7.3%2B%20%7C%208.5-777bb4.svg)](https://www.php.net/)
[![Parsedown](https://img.shields.io/badge/Parsedown-1.8.0-orange.svg)](https://github.com/erusev/parsedown/releases/tag/1.8.0)
[![Composer](https://img.shields.io/badge/composer-not%20required-lightgrey.svg)](#-installation)

A free **Markdown** parser plugin for the [Cotonti](https://www.cotonti.com/) CMS, built on top of the **[Parsedown 1.8.0](https://github.com/erusev/parsedown/releases/tag/1.8.0)** library.

The plugin registers itself in Cotonti as an `editor-parser`, installs easily through the admin panel, **does not require Composer**, and is compatible with modern PHP versions up to 8.5.

- **Repository:** https://github.com/webitproff/parsedown-cotonti
- **Connector author:** [webitproff](https://github.com/webitproff)
- **License:** MIT
- **Price:** free

---

## Table of Contents

- [Features](#-features)
- [Requirements](#-requirements)
- [Screenshots](#-screenshots)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [How It Works](#-how-it-works)
- [File Structure](#-file-structure)
- [Security](#-security)
- [PHP 8.5 Compatibility](#-php-85-compatibility)
- [Cotonti Hooks](#-cotonti-hooks)
- [Usage in Templates](#-usage-in-templates)
- [Extending the Plugin](#-extending-the-plugin)
- [FAQ](#-faq)
- [Troubleshooting](#-troubleshooting)
- [Version History](#-version-history)
- [License](#-license)
- [Credits](#-credits)

---

## ✨ Features

- 📝 **Full Markdown support** through Parsedown 1.8.0 — headings, lists, tables, blockquotes, code blocks, links, images, footnotes, autolinks.
- 🔌 **Easy installation** — copy the folder and install via the Cotonti admin panel.
- 🚫 **No Composer** — the Parsedown library ships inside the plugin and is loaded directly.
- 🛡️ **Safe mode** — HTML escaping and unsafe URL scheme filtering for XSS protection.
- ⚙️ **5 flexible settings** in the admin panel: `safemode`, `breaks`, `markupescaped`, `urlslinked`, `anchorfix`.
- ⚓ **Anchor fix** — automatically turns `#anchor` links into full page URIs (important for internal navigation in Cotonti).
- 🚀 **Performance** — static cache of the parser instance within a single request; on a page with hundreds of entries, `Parsedown` is instantiated only once.
- 🌍 **Localization** — Russian and English language files included out of the box.
- 🐘 **PHP 8.5-ready** — modern PHP requirements accounted for: nullable types, up-to-date constants, no deprecated constructs.
- 🆓 **MIT license** — free to use, modify, and distribute.

---

## 📦 Requirements

| Component | Minimum version | Notes |
|---|---|---|
| **Cotonti** | 1.x (current 0.9.x / 1.0.x branch) | Uses the `Cot` class, the `parser` hook, and the `$cot_plugins['parser']` registry |
| **PHP** | 7.3 | Tested on 8.5 |
| **PHP extension** | `mbstring` | Required by Cotonti itself |
| **`htmlpurifier` plugin** | — | Strongly recommended as an additional protection layer |
| **Composer** | **not required** | The library is bundled |

---

## 🖼 Screenshots

> _This section will be filled after the first public release._
>
> Planned:
> - screenshot of the plugins list with Parsedown installed;
> - screenshot of the settings page with the 5 options;
> - example of Markdown rendering in the "Pages" module.

---

## 🚀 Installation

### Step 1. Download

Get the latest version:

```bash
git clone https://github.com/webitproff/parsedown-cotonti.git
```

Or download the ZIP from the [Releases](https://github.com/webitproff/parsedown-cotonti/releases) page and unpack it.

### Step 2. Copy the files

Copy the `parsedown/` folder into the Cotonti plugins directory:

```
<site-root>/plugins/parsedown/
```

Resulting structure:

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

### Step 3. Verify the library

Make sure the file `plugins/parsedown/lib/Parsedown.php` exists and contains the `Parsedown` class version 1.8.0. If the `lib/` folder is empty, download the library separately:

```bash
# From the plugin root
curl -L -o lib/Parsedown.php \
  https://raw.githubusercontent.com/erusev/parsedown/1.8.0/Parsedown.php
```

### Step 4. Install via the admin panel

1. Open the **Admin Panel** → **Extensions**.
2. Find **"Parsedown Markdown"** in the list (category `editor-parser`).
3. Click **"Install"**.

### Step 5. Activate as parser

1. Go to **Admin Panel** → **Configuration** → **Parser**.
2. Select **`parsedown`** from the dropdown.
3. Save.

> ⚠️ **Important!** Make sure **only one parser is enabled**. Having `parsedown`, `markitup`, `ckeditor`, etc. all active as parsers will lead to undefined behavior.

### Step 6. Configure options (optional)

Go to **Admin Panel** → **Plugins** → **Parsedown Markdown** → adjust the options to your needs.

---

## ⚙️ Configuration

All options are available under **Admin Panel → Plugins → Parsedown Markdown**.

| Option | Values | Default | Description |
|---|---|---|---|
| **Safe mode** (`safemode`) | 0 / 1 | **0** | Escapes HTML and filters unsafe URL schemes. Enable if the site accepts user-generated content. |
| **Line breaks** (`breaks`) | 0 / 1 | **0** | Single newline → `<br>`. Matches GitHub Flavored Markdown style. |
| **Escape markup** (`markupescaped`) | 0 / 1 | **0** | Completely disable processing of inline HTML inside Markdown. |
| **Auto-link URLs** (`urlslinked`) | 0 / 1 | **1** | Bare URLs (`https://...`) become clickable links automatically. |
| **Anchor fix** (`anchorfix`) | 0 / 1 | **1** | `#anchor` links are expanded to the full URI of the current page. Useful for Cotonti pages and forums. |

### Recommended combinations

**For a public site with user-generated content (blog, forum):**

```
safemode      = 1
breaks        = 1
markupescaped = 1
urlslinked    = 1
anchorfix     = 1
+ htmlpurifier plugin active
```

**For an internal site / single-author blog:**

```
safemode      = 0
breaks        = 0
markupescaped = 0
urlslinked    = 1
anchorfix     = 1
```

---

## 🔧 How It Works

### Call sequence

```
User opens a page
        │
        ▼
Cotonti module (e.g., page) calls cot_parse($text)
        │
        ▼
cot_parse() builds the function name: $func = 'cot_parse_parsedown'
        │
        ▼
Core checks: function_exists('cot_parse_parsedown')?
        │
        ├─── NO  → searches $cot_plugins['parser']
        │          includes parsedown.parser.php
        │          the file declares cot_parse_parsedown()
        │          the core calls it
        │
        └─── YES → calls the function directly
        │
        ▼
cot_parse_parsedown($text):
   • returns '' for null / empty strings
   • on first call in a request:
       – includes lib/Parsedown.php
       – instantiates new Parsedown()
       – applies settings from Cot::$cfg['plugin']['parsedown']
   • parses the text via $pd->text($text)
        │
        ▼
parser.last hook includes parsedown.anchor.php
        │
        ▼
If anchorfix is enabled — rewrites href="#..." → full URI
        │
        ▼
Returns the resulting HTML to the template
```

### Key point

The `parsedown.parser.php` file **only declares** the `cot_parse_parsedown()` function and **does not parse anything at the top level of the file**. This is a Cotonti core requirement: after `include`, the core calls that function itself. If the file tries to parse immediately upon inclusion, the core fails with:

```
Call to undefined function cot_parse_parsedown()
```

The same rule applies to all Cotonti parsers (markitup, ckeditor in parser mode, etc.).

### Performance

Inside `cot_parse_parsedown()`, a `static $pd` variable is used as a cache:

```php
static $pd = null;
if ($pd === null) {
    $pd = new Parsedown();
    // ... settings
}
return $pd->text($text);
```

A `static` variable lives across calls within a single HTTP request. On a typical page with a list of articles, the function may be called 20–50 times; without caching that would create 20–50 `Parsedown` objects, with caching — **one**.

---

## 📁 File Structure

```
plugins/parsedown/
│
├── parsedown.setup.php          # plugin metadata and config
│   ├── [BEGIN_COT_EXT]          #   — code, name, version, author
│   └── [BEGIN_COT_EXT_CONFIG]   #   — 5 radio options
│
├── parsedown.parser.php         # parser hook: declares cot_parse_parsedown()
│   └── function cot_parse_parsedown(?string): string
│
├── parsedown.anchor.php         # parser.last hook: anchor link fix
│
├── lang/
│   ├── parsedown.ru.lang.php    # Russian option labels
│   └── parsedown.en.lang.php    # English option labels
│
├── lib/
│   └── Parsedown.php            # original 1.8.0 library
│                                # (c) Emanuil Rusev, MIT
│
├── LICENSE.txt                  # plugin and library license
└── README.md                    # this file
```

---

## 🛡 Security

### What Safe mode protects against

When `safemode = 1`, `Parsedown::setSafeMode(true)`:

1. Escapes **all inline HTML** (`<script>`, `<iframe>`, `<object>`, etc.).
2. Filters **link schemes** — only a safe whitelist is allowed:
   `http`, `https`, `ftp`, `ftps`, `mailto`, `tel`, `data:image/*`, `irc`, `ircs`, `git`, `ssh`, `news`, `steam`.
3. Removes **all `on*` attributes** (e.g., `onclick`, `onerror`) from inline HTML.
4. Removes unsafe **tag names / attributes** that do not pass the whitelist.

### What Safe mode does NOT protect against

- **Unwanted markup** in Markdown itself (spam links, ad images) — that is a moderation concern, not a parser concern.
- **Injection through CSS attributes** in allowed tags — partially filtered.

### Recommendations

1. **Keep `safemode = 1`** for any content written by users (comments, forum, guest pages).
2. Additionally install and activate **[htmlpurifier](https://github.com/Cotonti/Cotonti/tree/master/plugins/htmlpurifier)** — it cleans HTML **after** the parser, giving a second independent defense layer.
3. Keep Cotonti and the plugin up to date.
4. Do not enable two or more content parsers simultaneously — it can cause conflicts and bypasses.

### What happens when the library is missing

If `lib/Parsedown.php` is not found (e.g., broken installation), `cot_parse_parsedown()` returns:

```php
htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
```

The text is output as-is with full HTML escaping — this is a **safe degraded mode**, not an error.

---

## 🐘 PHP 8.5 Compatibility

The plugin is designed with modern PHP requirements in mind and is fully compatible with PHP 7.3, 7.4, 8.0, 8.1, 8.2, 8.3, 8.4, and **8.5**.

What is accounted for:

- **Nullable types** — the function parameter is typed as `?string` to accept `null` from DB fields without a `TypeError`.
- **Strict return types** — `: string` guarantees the correct type on output.
- **Constants `ENT_QUOTES | ENT_SUBSTITUTE`** — current and recommended in 8.x.
- **No dynamic properties** — the code does not create properties on objects on the fly.
- **No negative string index access** — removed in PHP 7.1, causes an error in 8.x.
- **No implicitly nullable types** — `function foo(string $x = null)` is incorrect and is not used.
- **No deprecated functions** — no `create_function`, `each`, `money_format`, `get_magic_quotes_gpc`, etc.
- **No `utf8_encode` / `utf8_decode`** — deprecated in 8.2, not used.

### Testing

The plugin has been tested on:

| PHP version | Result |
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

## 🪝 Cotonti Hooks

The plugin subscribes to two hooks:

| Hook | File | Purpose |
|---|---|---|
| `parser` | `parsedown.parser.php` | Declares `cot_parse_parsedown()`, called by the core during content parsing |
| `parser.last` | `parsedown.anchor.php` | Post-processes anchor links `#anchor` → full URI |

### Execution order

```
cot_parse()
    │
    ├── main parser (our cot_parse_parsedown)
    │
    └── parser.last hook
            └── parsedown.anchor.php
```

### Debugging

To see whether the parser was actually invoked, enable Cotonti debug mode:

```php
// config.php
$cfg['debug_mode'] = true;
```

The list of fired hooks (`$cotHooksFired`) will include both `parser` and `parser.last`.

---

## 📝 Usage in Templates

### Automatic parsing

Cotonti automatically applies the active parser to content in `page`, `forums`, `comments`, and other modules — just select `parsedown` in the config.

### Manual call from templates

In XTemplate:

```html
{PAGE_TEXT}
```

If the page uses a field to which the parser is applied automatically, no additional action is needed.

### Call from a plugin / module

```php
$html = cot_parse($markdownSource, true, 'parsedown');
```

`cot_parse()` parameters:

| Parameter | Value |
|---|---|
| 1 | Source text |
| 2 | `true` — enable markup, `false` — plain text |
| 3 | Parser name; you can specify `'parsedown'` explicitly |

### Example code

```php
<?php
// Inside your plugin:
$source = "## Heading\n\nText with **bold** and *italic*.\n\n- Item 1\n- Item 2";

$html = cot_parse($source, true, 'parsedown');

// Output:
// <h2>Heading</h2>
// <p>Text with <strong>bold</strong> and <em>italic</em>.</p>
// <ul>
// <li>Item 1</li>
// <li>Item 2</li>
// </ul>
```

---

## 🧩 Extending the Plugin

### Adding your own settings

1. Open `parsedown.setup.php`.
2. Add an option to the `[BEGIN_COT_EXT_CONFIG]` block:

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

3. Add labels in the language files:

```php
// lang/parsedown.en.lang.php
$L['cfg_your_option']      = 'Your option';
$L['cfg_your_option_hint'] = 'Description of your option';
```

4. Apply the value in `cot_parse_parsedown()`:

```php
if (!empty($cfg['your_option'])) {
    // ...
}
```

5. Reinstall the plugin in the admin panel (uninstall → install).

### Overriding Parsedown

If you need custom logic (e.g., adding custom inline patterns), you can extend `Parsedown`:

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

However, this requires modifying `parsedown.parser.php`. Support for user-defined classes via config is planned for future versions.

---

## ❓ FAQ

### Why do I need this plugin if Cotonti already has markitup?

`markitup` is an **editor** (visual toolbar with buttons), not a parser. `parsedown` is a **parser** (it turns Markdown into HTML). They can be used together: write in markitup, save as Markdown, parse with Parsedown.

### Why not use Composer?

Cotonti traditionally distributes plugins as **self-contained folders**. Requiring Composer would complicate installation for most CMS users who are not familiar with it. The Parsedown library is a single file, so it is easier to bundle it inside the plugin.

### Will it work with other Parsedown versions?

Technically yes — the API has been stable since 1.6. But the plugin is shipped and tested specifically with **1.8.0**, the last stable release of the 1.x branch.

### What should I do if the parser doesn't work?

See [Troubleshooting](#-troubleshooting).

### Is Extra (extended) Markdown supported?

Parsedown 1.8.0 implements **CommonMark** + GitHub Flavored Markdown (tables, strikethrough, autolinks). Extensions like footnotes (`[^1]`) or definitions (`term\n: definition`) are **not supported**. They require the `ParsedownExtra` fork, which is not currently bundled.

### How do I update the plugin?

1. Download the new version.
2. Replace all files **except** `lib/Parsedown.php` (if the library version is unchanged).
3. In the admin panel: **Extensions** → **Parsedown Markdown** → **Update** (uninstall → install).
4. Settings are preserved because they live in `$cfg['plugin']['parsedown']`.

---

## 🔧 Troubleshooting

### Error `Call to undefined function cot_parse_parsedown()`

**Cause:** `parsedown.parser.php` does not declare the function, or an old plugin version is installed that parses text right on inclusion.

**Solution:**

1. Make sure `parsedown.parser.php` contains the block:
   ```php
   if (!function_exists('cot_parse_parsedown')) {
       function cot_parse_parsedown(?string $text): string { ... }
   }
   ```
2. Reinstall the plugin via the admin panel.

### Error `Class 'Parsedown' not found`

**Cause:** `lib/Parsedown.php` is missing or corrupted.

**Solution:**

```bash
cd plugins/parsedown
curl -L -o lib/Parsedown.php \
  https://raw.githubusercontent.com/erusev/parsedown/1.8.0/Parsedown.php
```

Verify that the file starts with `<?php` and contains `class Parsedown`.

### Markdown is not parsed; text is displayed as-is

**Possible causes:**

1. `parsedown` is not selected in **Configuration → Parser**.
2. A different parser is selected (e.g., `none`).
3. The plugin is not installed (check status in the extensions list).
4. The text is output via `htmlspecialchars()` without calling `cot_parse()` — check your template.

### Anchor links `#anchor` don't work

**Cause:** the `anchorfix` option is disabled.

**Solution:** enable `anchorfix` in the plugin settings.

### HTML tags inside Markdown are displayed as text

**Cause:** `markupescaped` or `safemode` is enabled.

**Solution:** for trusted content, disable both options. **Not recommended** for user-generated content.

### Error `TypeError: cot_parse_parsedown(): Argument #1 ($text) must be of type string, null given`

**Cause:** an older plugin version with the signature `string $text` instead of `?string $text` is installed.

**Solution:** update `parsedown.parser.php` to the current version (1.0.1+).

---

## 📅 Version History

The full history is in [`CHANGELOG.md`](./CHANGELOG.md) (to be added in future releases).

### 1.0.1 — 2026-10-13

- Added detailed comments to all files.
- Added PHPDoc metadata (`@package`, `@author`, `@copyright`, `@license`, `@link`, `@version`, `@since`).
- Explicit PHP 8.5 compatibility: `?string` in the signature, current constants `ENT_QUOTES | ENT_SUBSTITUTE`.
- Improved handling of empty input — early exit before parser instantiation.
- Added a static cache of the `Parsedown` object for performance.
- Added `LICENSE.txt`.
- Expanded `README.md`.

### 1.0.0 — 2026-10-13

- First public release.
- Bundled Parsedown 1.8.0.
- Hooks `parser` and `parser.last`.
- 5 options in the admin panel.
- Language files: Russian, English.

---

## 📄 License

The project is distributed under the **MIT** license. Full text in [`LICENSE.txt`](./LICENSE.txt).

### Summary

- ✅ Free use, including commercial.
- ✅ Modification and distribution.
- ✅ Sublicensing.
- ⚠️ The copyright notice must be preserved.
- ❌ The author is not liable for consequences of use.

### Parsedown library

The `lib/Parsedown.php` library is the property of **Emanuil Rusev** and is distributed under MIT. Full text: https://github.com/erusev/parsedown/blob/1.8.0/LICENSE.txt

---

## 🙏 Credits

- **[Emanuil Rusev](https://github.com/erusev)** — for the Parsedown library, lightweight, fast, and reliable.
- **[Cotonti Team](https://github.com/Cotonti)** — for the CMS and its extensible architecture with hooks and parsers.
- **Cotonti community** — for feedback, testing, and suggestions.

---

## 🔗 Links

- **Plugin repository:** https://github.com/webitproff/parsedown-cotonti
- **Issues:** https://github.com/webitproff/parsedown-cotonti/issues
- **Pull Requests:** https://github.com/webitproff/parsedown-cotonti/pulls
- **Cotonti CMS:** https://www.cotonti.com/
- **Cotonti on GitHub:** https://github.com/Cotonti/Cotonti
- **Parsedown:** https://github.com/erusev/parsedown

---

## 🤝 Contributing

Contributions are welcome:

- 🐞 Bug reports — [Issues](https://github.com/webitproff/parsedown-cotonti/issues)
- 💡 Ideas and suggestions — [Discussions](https://github.com/webitproff/parsedown-cotonti/discussions)
- 🔧 Pull Requests — with a description of what and why
- 🌍 New translations — send `parsedown.<lang>.lang.php` files

### Before submitting a PR

1. Fork the repository.
2. Create a branch: `git checkout -b feature/my-feature`.
3. Commit: `git commit -m "Add: description"`.
4. Push: `git push origin feature/my-feature`.
5. Open a Pull Request.

---

<p align="center">
  <strong>Parsedown Markdown for Cotonti</strong><br>
  Made with ♥ for the Cotonti community<br>
  <a href="https://github.com/webitproff/parsedown-cotonti">github.com/webitproff/parsedown-cotonti</a>
</p>
