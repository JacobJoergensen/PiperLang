<h3 align="center">PiperLang DOCUMENTATION (v2.0.0)</h3>

<hr>

## Quick Navigation

- [Installing](#Installing)
- [Quick start](#Quick-Start)
- [Configuration](#Configuration)
- [Methods](#methods)
- [Formatting](#formatting)
- [Modifier](#modifier)
- <a href="https://github.com/JacobJoergensen/PiperLang/blob/main/CHANGELOG.md">Changelog</a>


## Installing
PiperLang can be installed either by downloading the framework directly from its GitHub repository or using Composer, a PHP dependency manager.

#### Downloading from GitHub
You can download PiperLang directly from its GitHub repository. This method is suitable if you prefer to manually manage dependencies or if you want to explore the source code.

1) GitHub Repository: Visit the PiperLang GitHub repository.
2) Download ZIP: Click on the "Code" button and select "Download ZIP" to download the latest version of PiperLang as a zip archive.
3) Extract: Extract the downloaded zip file to your desired location.
4) Integration: Integrate PiperLang into your project by including the necessary files as per your project structure.

#### Installation via Composer
Composer is a popular PHP dependency manager that simplifies the process of managing PHP packages, including PiperLang.

1) Composer Command: Run the following Composer command in your project directory:
```
composer require piperlang/piperlang
```

This command will automatically download and install the latest version of PiperLang and its dependencies into your project.

#### Initializing
Once PiperLang is installed, you can start using it in your PHP projects by including the necessary files or by using Composer's autoload feature to autoload PiperLang classes.

1) Include Autoloader: If you're using Composer autoload, include Composer's autoloader in your PHP script:
```php
require_once __DIR__ . '/vendor/autoload.php';
```

2) Instantiate PiperLang: Create an instance of the PiperLang class to start using PiperLang in your project:
```php
use PiperLang\PiperLang;

$piper = new PiperLang();
```

You can now use the ```$piper``` object to access PiperLang's methods and functionalities within your PHP application.
With PiperLang installed and initialized, you're ready to leverage its features and capabilities in your PHP projects.


## Quick Start
- Now we can start playing around with PiperLang.
- Create a locales/en.json file:

```json
{
  "variables": {
    "site_name": "MySite"
  },
  "welcome": "Welcome to {{site_name}}"
}
```

- Now in PHP:

```php
use PiperLang\PiperLang;

$_SERVER['DOCUMENT_ROOT'] = __DIR__;

$piper = new PiperLang(
    supported_locales: ['en', 'fr'],
    debug: true
);

// Load and display translation.
$piper->loadLocale('en');
echo $piper->getTranslation('welcome'); // Welcome to MySite.
```
- <a href="https://github.com/JacobJoergensen/PiperLang/tree/main/examples"> See more examples by clicking here!</a>


## Configuration
You can change various settings in the `PiperLang` framework. Here's an example of how you can modify settings after the initializing:

| Setting                | Method                                                                     | Description                                                         | Default       |
|------------------------|----------------------------------------------------------------------------|---------------------------------------------------------------------|---------------|
| Debug Mode             | `$piperlang->debug = true`                                                 | Enables or disables debug mode.                                     | false         |
| Current Locale         | `$piperlang->current_locale = 'en'`                                        | Sets the current locale.                                            | null          |
| Default Locale         | `$piperlang->default_locale = 'es'`                                        | Sets the default locale.                                            | 'en'          |
| Supported Locales      | `$piperlang->supported_locales = ['en', 'es', 'fr']`                       | Adds locales that the application should support.                   | ['en']        |
| Locale Path            | `$piperlang->locale_path = '/path_to_your_locales/'`                       | Specifies the path to your localization files.                      | '/locales/'   |
| Locale File Extension  | `$piperlang->locale_file_extension = 'json'`                               | Specifies the extension of your localization files.                 | 'json'        |
| Loaded Locales         | `$piperlang->loaded_locales = ['en' => ['greeting' => 'Hello']];`          | Adds/edits existing loaded locales.                                 | []            |
| Allowed Tags           | `$piperlang->allowed_tags = '<a><br>'`                                     | Specifies which HTML tags are allowed in translations.              | '<a><br>'     |
| Variable Pattern       | `$piperlang->variable_pattern = '/<<(.*?)>>/'`                             | Alters the variable pattern to something other than the default.    | '/{{(.*?)}}/' |
| Session Enabled        | `$piperlang->session_enabled = false`                                      | Enables or disables the session.                                    | true          |
| Session Key            | `$piperlang->session_key = 'user_lang'`                                    | Changes the session key for storing the user's locale preference.   | 'locale'      |
| Cookie Enabled         | `$piperlang->cookie_enabled = false`                                       | Enables or disables the cookie.                                     | false         |
| Cookie Key             | `$piperlang->cookie_key = 'user_lang'`                                     | Alters the cookie key for storing the user's locale preference.     | 'site_locale' |

## Methods
Key methods of `PiperLang` include:

* `getInfo()` - Returns an associative array containing PiperLang configuration details.
* `detectLocale()` - Auto-detects the best matching locale (session > cookie > header).
* `getLocale()` - Retrieves the current locale.
* `setLocale(?string $preferred_lang = null)` - Sets the locale to the preferred language or defaults if none is provided.
* `setLocalePath(string $path)` - Sets the path for locale files.
* `switchLocale(string $new_lang)` - Switches the locale to a new language.
* `replaceVariables(string $string, array $variables)` - Replaces placeholders in a string with specified variables.
* `loadLocale(string $locale)` - Loads, validates, and processes a locale file.
* `unloadFile(string $locale)` - Unloads a specified locale file from memory.
* `formatNumber(float $value, int $precision = 2)` - Formats a number according to the current locale.
* `formatCurrency(float $amount, string $currency, bool $show_symbol = true)` - Formats a currency amount per locale.
* `formatDate(DateTimeImmutable $date, string $style = 'long')` - Formats a date based on locale and specified format.
* `getFormattingRules()` - Retrieves locale-specific formatting rules.


## Formatting

The PiperLang class provides several methods for formatting number, date, and currency data according to specific locale rules using PHP's built-in NumberFormatter and IntlDateFormatter classes.

#### Number Formatting
The ```formatNumber(float $number)``` method takes a float value as an argument and formats it based on the currently set locale. It sets the maximum fraction digits to 2, and returns the formatted number. If the provided number is not numeric, or there is any other issue, an exception is thrown.

#### Currency Formatting
For currency formatting, the ```formatCurrency(float $amount, string $currency, bool $show_symbol = false)``` method is used. It takes the amount to be formatted, the ISO 4217 currency code (like 'USD' or 'EUR'), and an optional parameter to decide whether to display a currency symbol or not. The formatted currency string will then be returned. Invalid amount or currency inputs prompt exceptions.

#### Date Formatting
The ```formatDate(DateTime $date, string $format = 'long')``` method formats a date according to the current locale setting. This method accepts a DateTime object and an optional format string (which can be 'short', 'medium', 'long' or 'full', 'long' is default). It returns the formatted date string.

#### Formatting Rules
The ```getFormattingRules()``` method shows all the current locale-specific numeric and monetary formatting information. It returns an associative array built from values obtained using PHP's ```localeconv()``` method.


## Modifier
The Modifier class extends the PiperLang class and can be used to modify or extend the functionality of PiperLang without changing the core library.

#### The Modifier class can be found in Modifier.php inside the src folder
```php
namespace PiperLang;

class Modifier extends PiperLang {
    /**
     * MODIFY CONSTRUCTOR.
     *
     * CALLS THE PARENT PiperLang CLASS CONSTRUCTOR
     * FIRST, AND THEN EXECUTE THE CUSTOM CODE.
     *
     * @see PiperLang::__construct() - FOR THE PARENT CONSTRUCTOR.
     */
    public function __construct() {
        parent::__construct();
        // ADD YOUR CUSTOM CODE HERE.
    }
}
```
