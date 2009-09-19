# Localisation Manager

A Symphony CMS Extension to manage back-end localisations.

- Version: 1.0.1
- Author: Nils Hörrmann, post@nilshoerrmann.de
- Build Date: 19th September 2009
- Requirements: Symphony CMS 2.0.x

## Available languages

Localisation Manager currently supports the following languages:

- **German,** containing:
  - Symphony Core
  - Debug DevKit
  - Export Ensemble
  - JIT Image Manipulation
  - Maintenance Mode
  - Profile DevKit
  - Field: Select Box Link

If you like to contribute new languages or if you like to extend existing ones, please read the instructions below.

## Enabling localisation

After installing Localisation Manager successfully, you'll find a new setting in your preferences allowing you to switch the system language. Authors can override this global preference with a custom setting in their profiles.

## Extending and creating languages

If you like to to edit, extend, or create a language file, visit `/symphony/extension/localisationmanager/` in your browser. Enter your language details and download an up-to-date language file. Leaving all fields blank will create a clean, untranslated language file. Providing details of an already existing language will create a new file containing all existing strings, leaving out obsolete string but highlighting all missing ones. Localisation Manager will automatically grab language strings for all extensions currently present in your `extensions` folder no matter if they are activated or not. 

*Please note: If you are downloading an existing language file, all language strings that are not needed in your current setup will be deleted. If you like to keep all strings, you'll have to upload all extensions mentioned in the original language file.*

### Using language files

Newly created or edited language files can be uploaded either to `/symphony/lib/lang/` or `/extensions/localisationmanager/lang/` and will be instantly available for use.

## Sharing language files

If you like to contribute new or updated language files, please fork this repository and commit your changes.

## Acknowledgement

This extension makes use of the Translation Manager class written by Marcin Konicki, http://ahwayakchih.neoni.net, available at <http://github.com/ahwayakchih/translationmanager>.

## Change log

### Version 1.0.1: 

- Added dynamic JavaScript translation generation.
- Fixed issue with escaped double quotes.

### Version 1.0.0: 

- Initial release.