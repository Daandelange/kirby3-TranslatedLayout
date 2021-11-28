# Kirby translatedlayout plugin

A layout field that forces translations to have anidentical structure to the one of the default language.

This is an experimental draft for trying to bring some translation logic to blocks and layouts.

Note: If you manually edit content files, it's not recommended to use this plugin.

### Implementation
 - Seconday languages are always syncronized (on parse) with layouts and blocks from the default language using their unique `id`. If a block has no translation, it's replaced with the default language. If a block translation is not available in the default.
 - In secondary languages, some simple GUI limitations prevent panel users from changing the layout and/or adding blocks.
 - The syncronized translation is saved as a regular layout in the content file. This duplicates the structure in every language, but it facilitates the implementation and ensures full compatibility with core blocks and layouts.

![Screenshot of Kirby 3 plugins TranslatedLayout](TranslatedLayout.gif)

## Installation

### Requirements
- Kirby 3.6 or above.
- **Warning!** If you already have a layout with translated content, switching to this field will erase all translations unless you manually give the same `id` to blocks/rows/columns in the translations.  
  Please also note that during the beta phase, there remains a risk of data loss. Do not use without backups.


### Download
Download and copy this repository to `/site/plugins/translatedlayout`.

### Git submodule
```
git submodule add https://github.com/daandelange/kirby3-translatedlayout.git site/plugins/translatedlayout
```

<!-- Unavailable !!
### Composer

```
composer require daandelange/translatedlayout
```
-->

## Setup
In your page blueprints, you can simply replace a `type: layout` field by `type: translatedlayout`.

## Options
There are no options available yet. Would you like to contribute some ?

## Development
- `npm install` : Install the required dependencies.
- `npm run dev` : Develop mode (listen/compile).
- `npm run build` : Compile for publishing.

## License

MIT - Free to use, free to improve !

However, for usage in commercial projects, please seriously consider to improve the plugin a little and contribute back the changes with a PR, or hire someone to do so.

## Credits

- [Daan de Lange](https://daandelange.com/)
