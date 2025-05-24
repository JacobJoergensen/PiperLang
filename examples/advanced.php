<?php
    use PiperLang\PiperLang;

    // Include the framework
    require_once '../src/PiperLang.php';

    // Initialize PiperLang
    $piperLang = new PiperLang();

    // Enable session storage
    $piperLang->session_enabled = true;

    // Enable cookie storage
    $piperLang->cookie_enabled = true;

    // Set locale from browser
    $piperLang->setLocale($piperLang->detectBrowserLocale());

    // Load file for the locale
    $piperLang->loadFile($piperLang->getLocale());

    // Format a number
    $originalNumber = 1234567.89;
    $formattedNumber = $piperLang->formatNumber($originalNumber);

    echo "Original number: $originalNumber, formatted number: $formattedNumber";
