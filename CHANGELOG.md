# PiperLang Changelog

## WIP - Next Version
* Nothing to see yet!

## Version 1.0.0 (??-??-??)
* Added packagist support
* Improved composer.json for better composer support
* Improved .md files to be up to date

## Version 1.0.0 Beta 5 (28-04-24)
* Added Docker support for setting up local development environment
* Added better documentation of PiperLang
* Added phpunit coverage that also support codecov
* Added build artifacts, so you can download a build through github actions
* Added even more phpunit tests
* Improved readme.md

## Version 1.0.0 Beta 4 (26-04-24)
* Added a way to modify the core code without having to change the core code, look at Modifier.php
* Added hook system to allow for better custom functionality working with PiperLang without changing the core code
* Added getInfo method to get PiperLang info based on your current setup, to help you debug and see information about your setup
* Added debug mode (default: set to false) , when true it will show a lot more exceptions that can help debugging.
* Added missing tests
* Added some examples
* Added a english locale file for test purpose
* Made loaded_locales public
* Renamed format methods to have format as the first word instead of the last
* Improved readme.md
* Fixed locale file path
* Fixed locale file type missing .
* Fixed version in security.md

## Version 1.0.0 Beta 3 (23-04-24)
* Added class comment
* Added get current locale method
* Added a way to get all loaded locale files
* Added unload file method
* Added unit tests (still more tests need to be added later on)
* Changed use call name from PiperLang\PiperLang\PiperLang to PiperLang\PiperLang
* Made session enabled and locale path public
* Improved variable naming (now all is named locale)
* Improved setLocale methods
* Updated docs
* Fixed format style in dateFormat method
* Fixed attribute in numberFormat method
* Removed loadLocale method (use loadFile instead)

## Version 1.0.0 Beta 2 (08-04-24)
* Added cookie enabled setting (closes #14)
* Added show symbol option to currencyFormat method
* Added setLanguageSession method
* Added setLanguageCookie method
* No longer set the language inside the constructor
* Improved detectUserLanguage method
* Improved format handling inside the dateFormat method
* Improved format error messages
* Updated docs in README.md
* Fixed some style and typo things

## Version 1.0.0 Beta 1 (04-04-24)
* Initial release
