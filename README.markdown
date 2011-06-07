# Localisation Manager

A Symphony extension to create and export localisation files.

- Version: 1.6
- Author: Nils Hörrmann, post@nilshoerrmann.de
- Build Date: 7th June 2011
- Requirements: Symphony 2.2


## Extending and creating language files

If you like to to edit, extend, or create a language file, visit "System" > "Localisation" to download language files for the Symphony core or all extension installed. Using this extension it's possible to either download language files containing all existing strings, highlighting obsolete and missing strings, or download an file without translations. 

## Sharing language files

### Symphony core

Translations of the Symphony core are distributed via separate extensions. These extensions should be named `lang_{$name}` where `{$name}` is your language name, e. g. `lang_german` or `lang_dutch`. Each localisation extension should contain a `lang` folder where you store your translations. The needed files can be created using this extension. Please keep in mind that all language files need to follow a strict name scheme using the language code, e. g. `lang.pt-br.php` or `lang.ru.php`. A list of all language codes can be found here: <http://msdn.microsoft.com/en-us/library/ms533052(VS.85).aspx>.

Local variants of the same language should be bundled in the same extension, e. g. `English (United States)` and `English (United Kingdom)`.

The Localisation Manager will ignore all core localisation extensions and will not list them in the translation overview as there is no need to translate these extensions.

### Extension

Translations of extensions are bundled within the extensions itself. Please fork the extension you like to translate and send a pull request with the added language file to the developer via GitHub.


## Read more

Further information about Symphony localisation can be found on the [Symphony website](http://symphony-cms.com/learn/articles/view/localisation-in-symphony/).


## Change log

**Version 1.6**

- Fix apostrophe handling in strings (thanks Simone!)

**Version 1.5.1**

- Make sure that files on the first folder level are also parsed
- Fix link for new dictionaries

**Version 1.5**

- Symphony 2.2 compatibility

**Version 1.4**

- Exclude core strings for extensions
- Correctly populate about array for translations

**Version 1.3**

- Match new JavaScript dictionaries as well. Make sure you run Symphony 2.0.7 with this fix: <http://github.com/symphony/symphony-2/commit/960b28343f4f4a3148f7025d84a49337202108a0>.
- Added Localisation Manager to system navigation.
- Dictionary sorting is now an optional feature.
- Added German translations for the manager itself.

**Version 1.2.1**

- Correctly handle single quotes.

**Version 1.2**

- Complete rewrite of the string matching function
- Removed all features for backend localisation which are now part of the core
- Introduced a new interface which allows separate export of language files for the core and each extension available

**Version 1.1**

- Updated to confirm with Symphony 2.0.7beta changes

**Version 1.0.3**

- Added two more languages (Dutch and Portuguese)

**Version 1.0.2**

- Fixed issue with unescaped single quotes in missing string list.
- Fixed regex used to extract language code from language file names.

**Version 1.0.1** 

- Added dynamic JavaScript translation generation.
- Fixed issue with escaped double quotes.

**Version 1.0.0**

- Initial release.