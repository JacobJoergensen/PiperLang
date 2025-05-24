<?php
    use PiperLang\PiperLang;

    // Include the framework
    require_once '../src/PiperLang.php';

    // Initialize PiperLang
    $piperLang = new PiperLang();

    // Set current locale
    $piperLang->setLocale('en');

    // Translate a text
    echo $piperLang->translateWithPlural('hello', 21);
