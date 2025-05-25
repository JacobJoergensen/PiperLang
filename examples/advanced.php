<?php
    /**
     * How the locale file should be structured for this example:
     * {
     * "variables": {
     * "site_name": "MonSite"
     * },
     * "welcome": "Bienvenue à {{site_name}}",
     * "about": "À propos de {{company}}"
     * }
    */

    use PiperLang\PiperLang;

    $_SERVER['DOCUMENT_ROOT'] = __DIR__;
    session_start();

    $piper = new PiperLang(
        allowed_tags: '<strong><a>',
        debug: true,
        default_locale: 'en',
        supported_locales: ['en', 'fr', 'es']
    );

    // Manually switch to French.
    $piper->setLocale('fr');

    // Get a dynamic welcome message.
    echo $piper->getTranslation('welcome'); // → "Bienvenue à {{site_name}}".

    // Replacing custom variables manually.
    $translated = $piper->replaceVariables(
        $piper->getTranslation('about'),
        ['company' => 'ACME Corp']
    );

    echo $translated; // → "À propos de ACME Corp".

    // Format currency.
    echo $piper->formatCurrency(1234.56, 'EUR'); // → "1 234,56 €"

    // Format number.
    echo $piper->formatNumber(1234.5678); // → "1 234,57" (depends on locale)

    // Format date.
    echo $piper->formatDate(new DateTimeImmutable('2025-05-24')); // → "24 mai 2025" (in French)

