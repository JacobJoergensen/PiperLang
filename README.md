<h1 align="center">PiperLang (BETA STAGE - WORK IN PROGRESS)</h1>
<p align="center">PiperLang is a compact and efficient PHP framework designed to provide localization capacities for your web application. With features supporting cookie and session-based locale detection, dynamic pluralization, variable substitution in translations, and number, currency and date formatting in accordance with the set locale.</p>
<hr>
<p align="center">
      <strong>Craft your web app with precision, localize with ease.</strong>
      <br>
      <a href=""><strong>Explore PiperLang docs »</strong></a>
      <br>
      <br>
      <a href="https://github.com/JacobJoergensen/PiperLang/issues/new?assignees=-&labels=type: bug&template=bug_report.yml">Report bug</a>
      ·
      <a href="https://github.com/JacobJoergensen/PiperLang/issues/new?assignees=&labels=type: feature&template=feature_request.yml">Request feature</a>
      ·
      <a href="https://github.com/JacobJoergensen/PiperLang/LICENSE">License</a>
      ·
      <a href="https://github.com/JacobJoergensen/PiperLang/CHANGELOG.md">Changelog</a>
</p>

<hr>
<br>

> :warning: **Beta Stage Warning:** PiperLang is currently in a Beta stage. It is a work in progress and should not be used in production applications at the moment. Unexpected bugs and errors may happen, and features may change without prior announcements. We encourage you to participate in our testing and development phases by reporting any encountered issues.
>
> :information_source: **PHP Version:** The development cycle of PiperLang operates on PHP 8.3. For optimal compatibility and performance, we recommend using this version when working with PiperLang.

<br>

## Features
* <strong>Localization Support:</strong> PiperLang provides robust support for localization, allowing developers to easily translate their web applications into different languages.
* <strong>Automatic Locale Detection:</strong> The framework can automatically detect the user's preferred locale based on their browser settings or session/cookie preferences.
* <strong>Flexible Locale Management:</strong> Developers can set, switch, and manage locales dynamically, either based on user preferences or default settings.
* <strong>Translation with Pluralization:</strong> PiperLang facilitates translation with pluralization, ensuring accurate representation of phrases based on count variations.
* <strong>Variable Replacement in Translations:</strong> Developers can easily replace placeholders in translated strings with provided variables, enhancing flexibility in localization.
* <strong>Locale File Management:</strong> The framework handles loading and processing of locale files, ensuring proper validation of content and support for JSON format.
* <strong>Number and Currency Formatting:</strong> PiperLang offers utilities for formatting numbers and currency amounts according to the current locale, improving user experience in diverse regions.
* <strong>Date Formatting:</strong> Developers can format dates according to the current locale, with options for different date formats such as short, medium, long, and full.
* <strong>Customizable Hooks:</strong> The framework allows developers to define and execute custom hook actions at various stages, providing extensibility for integrating additional functionalities.
* <strong>Debugging Support:</strong> PiperLang includes debugging features to help developers identify and resolve issues related to locale management, file loading, and formatting.

## Quick Start
#### 1. Download PiperLang
- You can download the PiperLang framework from its GitHub repository or via Composer. If you're using Composer, you can simply run:
  ```composer require piperlang/piperlang```

#### 2. Initialize PiperLang
- Once downloaded, include the framework in your PHP file where you intend to use localization:
  ```php
  require_once 'path/to/vendor/autoload.php'; // If you've installed via Composer
  // or
  require_once 'path/to/PiperLang.php'; // If you've downloaded the framework directly
  ```

- Then call the core class by doing:
  ```php
  use PiperLang\PiperLang;
  ```
- After that we can create a new instance of PiperLang like so:
  ```php
  $piper = new PiperLang();
  ```

#### 3. Simple Example
- Now we can start playing around with PiperLang, this is a super simple example of it.

```php
$piper->supported_locales = ['en', 'da']; // Modifying supported locales
$piper->locale_path = '/app/assets/locales/'; // Modifying the path for the locale files
$piper->debug = true; // Set the debug mode to true

$piper->setLocale($piper->detectBrowserLocale()); // Set locale based on the browser locale

echo "Current Locale: " . $piper->getLocale() . "\n"; // To see the current locale

$localeNodes = $piper->loadFile($piper->detectBrowserLocale()); // Load the locale file based on the browser locale

if (isset($localeNodes['welcome'])) {
    echo "Welcome Message: " . $localeNodes['welcome'] . "\n";
} else {
    echo "No translation found for 'welcome' key in " . $piper->current_locale . " locale.\n";
}
```

## Status
![CI](https://github.com/JacobJoergensen/PiperLang/actions/workflows/ci.yml/badge.svg)

## Contributing

We welcome contributions to PiperLang! Whether you want to report a bug, request a feature, or submit a pull request with code changes, your input is highly valued. Please see the [CONTRIBUTING](CONTRIBUTING.md)

## License
Thank you for using PiperLang!

This project is licensed under the terms of the [MIT License](LICENSE), allowing you to use, modify, and distribute the software freely. For details, please see the [LICENSE](LICENSE) file.

<br>
