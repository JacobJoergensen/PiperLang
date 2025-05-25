# PiperLang Changelog

## Version 2.0.0 (25-05-25)
PiperLang 2.0.0 includes significant improvements to architecture, performance, and security. However, these improvements come with breaking changes that may require updates to your existing code. This document outlines the major changes to help you migrate from version 1.x to 2.0.0.

* PiperLang now is running php 8.4

### Breaking Changes
* The deprecated hooks system has been completely removed as planned.
* The formatDate() method now requires DateTimeImmutable instead of DateTime:
    * Old way: $piperLang->formatDate(new DateTime());
    * New way: $piperLang->formatDate(new DateTimeImmutable());
* The formatNumber() method syntax have been improved:
    * Old way: $piperLang->formatNumber($number);
    * New way: $piperLang->formatNumber($number, $max_fraction_digits = 2);
* The formatCurrency() method behavior change, it will now show the currency symbol as default:
    * Old way: $piperLang->formatCurrency($amount, 'USD', false); // false was default
    * New way: $piperLang->formatCurrency($amount, 'USD', true); // true is default
* The translateWithPlural() method has been removed. If your code used this method for pluralization, you'll need to implement a custom solution.
* Locale files are now automatically loaded when setting a locale. If your code explicitly managed loading, this logic may need to be removed to prevent duplicate loading.

### ✨ Major Features
* Introduced a robust detectLocale() method that checks:
    * Session
    * Cookie
    * Browser (Accept-Language header)
    * Fallback to default locale 
    * The result is saved to session/cookie as appropriate
* Added a new setLocale() method that:
    * Accepts a locale string and an optional boolean to force loading the locale.
    * Automatically loads the locale if it hasn't been loaded yet.
    * Returns the current locale after setting it.
* Added a new getTranslation() method that:
    * Accepts a key and an optional locale.
    * Returns the translation for the given key in the specified locale.
    * Uses the current locale if none is provided.
* Improved replaceVariables() method:
    * Uses strtr() for default {{var}} patterns.
    * Falls back to preg_replace_callback() for custom regex patterns.

### 🛠 Improvements
* Improved error handling with more descriptive exceptions.

### 🧹 Removed
* Deprecated hook system (addHook, runHooks) was fully removed.
* translateWithPlural() method removed.
* loadFile() replaced with modern loadLocale() logic.
* getHttpAcceptLanguage() replaced by detectLocale() internally.
* No more internal support for plural_rules – the plural handling feature was dropped.

## Version 1.3.0 (22-11-24)
* PiperLang is now compatibility with PHP 8.4
* Added experimental support for php 8.5
* Upgraded to phpstan version 2.X.
* Improved github workflow
* Improved docs
* Updated dependencies
* The ```hooks``` property is deprecated and will be removed in version 2.0.0
* The ```addHook``` and ```runHooks``` methods are deprecated and will be removed in version 2.0.0

## Version 1.2.0 (29-09-24)
* Added fallback value for getInfo method
* Added fallback value for getLocale method 
* Added document root check for loadFile method 
* Improved loadFile method 
* Improved .editorconfig (for jetbrains phpstorm users)
* Reworked phpunit tests 
* Removed unneeded is_numeric checks

## Version 1.1.0 (01-06-24)
* Added session has been started check to ```detectUserLocale``` method
* Improved ```http_accept_language``` handling to give more freedom (**Breaking Change)**
* Updated tests to match the updated source code

## Version 1.0.0 (30-04-24)
* Added packagist support
* Added better protecting against XSS when it comes to the ```switchLocale``` method
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
