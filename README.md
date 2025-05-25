<h1 align="center">PiperLang</h1>
<p align="center">PiperLang is a compact and efficient PHP library designed to provide localization capacities for your web application. With features supporting cookie and session-based locale detection, variable substitution in translations, number, currency and date formatting in accordance with the set locale.</p>
<hr>
<p align="center">
      <strong>Craft your web app with precision, localize with ease.</strong>
      <br>
      <a href="https://github.com/JacobJoergensen/PiperLang/blob/main/docs/index.md"><strong>Explore PiperLang docs »</strong></a>
      <br>
      <br>
      <a href="https://github.com/JacobJoergensen/PiperLang/issues/new?assignees=-&labels=type: bug&template=bug_report.yml">Report bug</a>
      ·
      <a href="https://github.com/JacobJoergensen/PiperLang/issues/new?assignees=&labels=type: feature&template=feature_request.yml">Request feature</a>
      ·
      <a href="https://github.com/JacobJoergensen/PiperLang/blob/main/LICENSE">License</a>
      ·
      <a href="https://github.com/JacobJoergensen/PiperLang/blob/main/CHANGELOG.md">Changelog</a>
</p>

<hr>
<br>

> :information_source: **PHP Version:** The development cycle of PiperLang operates on PHP 8.3. For optimal compatibility and performance, we recommend using this version when working with PiperLang.

<br>

## Features
* <strong>Localization Support:</strong> PiperLang provides robust support for localization, allowing developers to easily translate their web applications into different languages.
* <strong>Automatic Locale Detection:</strong> The library can automatically detect the user's preferred locale based on their browser settings or session/cookie preferences.
* <strong>Flexible Locale Management:</strong> Developers can set, switch, and manage locales dynamically, either based on user preferences or default settings.
* <strong>Variable Replacement in Translations:</strong> Developers can easily replace placeholders in translated strings with provided variables, enhancing flexibility in localization.
* <strong>Locale File Management:</strong> The library handles loading and processing of locale files, ensuring proper validation of content and support for JSON format.
* <strong>Number and Currency Formatting:</strong> PiperLang offers utilities for formatting numbers and currency amounts according to the current locale, improving user experience in diverse regions.
* <strong>Date Formatting:</strong> Developers can format dates according to the current locale, with options for different date formats such as short, medium, long, and full.
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

## Status
![workflow](https://github.com/JacobJoergensen/PiperLang/actions/workflows/workflow.yml/badge.svg)
[![codecov](https://codecov.io/gh/JacobJoergensen/PiperLang/graph/badge.svg?token=K6OZ3AVDPC)](https://codecov.io/gh/JacobJoergensen/PiperLang)

## Contributing

We welcome contributions to PiperLang! Whether you want to report a bug, request a feature, or submit a pull request with code changes, your input is highly valued. Please see the [CONTRIBUTING](CONTRIBUTING.md)

## License
Thank you for using PiperLang!

This project is licensed under the terms of the [MIT License](LICENSE), allowing you to use, modify, and distribute the software freely. For details, please see the [LICENSE](LICENSE) file.

<br>
