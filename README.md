# Kirby TranslatedLayout field plugin

A layout field that forces translations to have an identical structure to the one of the default language.

This is an experimental draft for trying to bring some translation logic to blocks and layouts.

Note: If you'd like to manually edit a `translatedlayout` field in any content file, it's not recommended to use this plugin.
Note: Blocks are not yet available while testing the layouts. They should be easy to port.

### Implementation
 - *Seconday language* translations of this field are always syncronized (on parse aka `$field->fill($value)`) with layouts and blocks from the default language using their unique `id`.
    - If a block has no translation, it's replaced with the default language.
    - If a block translation is not available in the default language, it's removed.
    - Some simple GUI limitations prevent panel users from changing the layout and/or adding blocks.
    - The syncronized translation is saved as a regular layout in the content file.  
      This duplicates the structure in every language, but it facilitates this plugin's implementation and ensures full compatibility with core blocks and layouts.
 - The **primary language** inherits the default LayoutBlock behaviour and remains (almost) identical.

![Screenshot of Kirby 3 plugins TranslatedLayout](TranslatedLayout.gif)

## Installation

### Requirements
- Kirby 3.6 or above.
- **Warning!** If you already have a layout with translated content, switching to this field will erase all translations unless you manually give the same `id` to blocks/rows/columns in the translations.  
  Please also note that during the beta phase, **there remains a risk of data loss**. Do not use without backups.


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

Example setup :
````yml
sections:
  content:
    type: fields
    fields:
      layout:
        label: TranslatedLayout Demo
        type: translatedlayout
        translate: true # <--- enables syncing of translations (layout field)
        layouts:
          - "1/1"
          - "1/2, 1/2"
          - "1/3, 1/3, 1/3"
        fieldsets:
          translateable:
            label: Fully Translateable Blocks
            type: group
            fieldsets:
              heading:
                extends: blocks/heading
                translate: true # same as default value
              - list
              - text
          partiallytranslateable:
            label: Blocks with some translateable fields
            type: group
            fieldsets:
              image: # over-rule the translated option of existing fields
                label: Image (non translateable src)
                type: image
                translate: false
                fields:
                  link:
                    translate: false
              url: # custom block example
                name: Url (non-translateable source)
                icon: cog
                fields:
                  link:
                    type: url
                    translate: false
                    required: true
                  text:
                    type: text
                    translate: true
                  
          nontranslateable:
            label: Non-translated blocks
            type: group
            fieldsets:
              line:
                extends: blocks/line
                translate: false # Completely disable whole block translations
````

To use predefined translation settings for the default kirby blocks, you may use :  
Hint: Useful for quickly setting up this plugin in a test environment.

````yml
fields:
  content:
    type: translatedlayout
    extends: fields/translatedlayoutwithfieldsets
````

To setup your own fieldsets, prefer copy/pasting from [translatedlayoutwithfieldsets.yml](https://github.com/Daandelange/kirby3-TranslatedLayout/blob/master/src/blueprints/fields/translatedlayoutwithfieldsets.yml) and adapt it to your needs.


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
