<?php
    /**
     * How the locale file should be structured for this example:
     * {
     * "variables": {
     * "site_name": "MySite"
     * },
     * "welcome": "Welcome to {{site_name}}"
     * }
    */

    use PiperLang\PiperLang;

    // Simulate a document root (required by PiperLang).
    $_SERVER['DOCUMENT_ROOT'] = __DIR__;

    // Create a PiperLang instance (defaults to 'en').
    $piper = new PiperLang(
        debug: true,
        default_locale: 'en',
        supported_locales: ['en', 'fr']
    );

    // Load an English locale file if not already loaded.
    $piper->loadLocale('en');

    // Fetch a translation string.
    // Should output: e.g. "Welcome to {{site_name}}".
    echo $piper->getTranslation('welcome');
