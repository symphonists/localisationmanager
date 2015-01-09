# Localisation Manager

A Symphony extension to create and export localisation files.

## Extending and creating language files

If you like to to edit, extend, or create a language file, visit "System" > "Localisation" to download language files for the Symphony core or all extension installed. Using this extension it's possible to either download language files containing all existing strings, highlighting obsolete and missing strings, or download an file without translations. 

### Compatibility

As of version 2.0 this extension is compatible with Symphony 2.3+ only and comes with support for namespaced translations and updated transliterations.

## Sharing language files

### Symphony core

Translations of the Symphony core are distributed via separate extensions. These extensions should be named `lang_{$name}` where `{$name}` is your language name, e. g. `lang_german` or `lang_dutch`. Each localisation extension should contain a `lang` folder where you store your translations. The needed files can be created using this extension. Please keep in mind that all language files need to follow a strict name scheme using the language code, e. g. `lang.pt-br.php` or `lang.ru.php`. A list of all language codes can be found here: <http://msdn.microsoft.com/en-us/library/ms533052(VS.85).aspx>.

Local variants of the same language should be bundled in the same extension, e. g. `English (United States)` and `English (United Kingdom)`.

The Localisation Manager will ignore all core localisation extensions and will not list them in the translation overview as there is no need to translate these extensions.

### Extension

Translations of extensions are bundled within the extensions itself. Please fork the extension you like to translate and send a pull request with the added language file to the developer via GitHub.


## Read more

Further information about Symphony localisation can be found on the [Symphony website](http://symphony-cms.com/learn/articles/view/localisation-in-symphony/).