# Localisation Manager

A Symphony extension to create and export localisation files.

- Version: 1.2
- Author: Nils Hörrmann, post@nilshoerrmann.de
- Build Date: 5th February 2010
- Requirements: Symphony CMS 2.0.7**RC2**


## Extending and creating language files

If you like to to edit, extend, or create a language file, visit `/symphony/extension/localisationmanager/` in your browser to download language files for the Symphony core or all extension installed. Using this extension it's possible to either download language files containing all existing strings, highlighting obsolete and missing strings, or download an file without translations. 


### Using language files

Since Symphony 2.0.7**RC2** it's possible to set language via the Symphony interface: You can set a default system language in the preferences or customize your own language setting in your author profile. 


## Sharing language files

### Symphony core

Translations of the Symphony core are distributed via separate extensions. These extensions should be named `lang_{$name}` where `{$name}` is your language name, e. g. `lang_german` or `lang_dutch`. Each localisation extension should contain a `lang` folder where you store your translations. The needed files can be created using this extension. Please keep in mind that all language files need to follow a strict name scheme using the language code, e. g. `lang.pt-br.php` or `lang.ru.php`. [A list of all language codes can be found here](http://msdn.microsoft.com/en-us/library/ms533052(VS.85).aspx).

Local variants of the same language should be bundled in the same extension, e. g. `English (United States)` and `English (United Kingdom)`.

The Localisation Manager will ignore all core localisation extensions and will not list them in the translation overview as there is no need to translate these extensions.

### Extension

Translations of extensions are bundled within the extensions itself. Please fork the extension you like to translate and send a pull request with the added language file to the developer via GitHub.


## Read more

Further information about Symphony localisation can be found on the [Symphony website](http://symphony-cms.com/learn/articles/view/localisation-in-symphony/).


## Change log

**Version 1.2, 5th February 2010:**

- Complete rewrite of the string matching function
- Removed all features for backend localisation which are now part of the core
- Introduced a new interface which allows separate export of language files for the core and each extension available

**Version 1.1, 8th January 2010:**

- Updated to confirm with Symphony 2.0.7beta changes

**Version 1.0.3, 10th November 2009:**

- Added two more languages (Dutch and Portuguese)

**Version 1.0.2, 19th September 2009:**

- Fixed issue with unescaped single quotes in missing string list.
- Fixed regex used to extract language code from language file names.

**Version 1.0.1, 19th September 2009:** 

- Added dynamic JavaScript translation generation.
- Fixed issue with escaped double quotes.

**Version 1.0.0, 17th September 2009:**

- Initial release.