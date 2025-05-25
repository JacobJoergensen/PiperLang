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

#### Packagist Repository
PiperLang is also available on Packagist, the main Composer repository for PHP packages. You can install PiperLang via Composer as shown above, and it will fetch the package from Packagist.

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
- Now we can start playing around with PiperLang, below is a simple example
```php
// Modifying supported locales to also support Danish locale
$piper->supported_locales = ['en', 'da'];

// Modifying the path for the locale files to match our own path
$piper->locale_path = '/app/assets/locales/';

// Set the debug mode to true, means it will send you more exceptions that can help you debug
$piper->debug = true;

// Set locale based on the browser locale
$locale = $piper->setLocale($piper->detectBrowserLocale());

// Get the current locale
echo "Current Locale: " . $piper->getLocale() . "\n";

// Load the locale file based on the browser locale
$localeNodes = $piper->loadFile($locale);

// One way to output the translations
if (isset($localeNodes['welcome'])) {
    echo "Welcome Message: " . $localeNodes['welcome'] . "\n";
} else {
    echo "No translation found for 'welcome' key in " . $piper->current_locale . " locale.\n";
}
```
- <a href="https://github.com/JacobJoergensen/PiperLang/tree/main/examples"> See more examples by clicking here!</a>


## Configuration
You can change various settings in the `PiperLang` framework. Here's an example of how you can modify settings after the initializing:

| Setting               | Method                                                                         | Description                                                       | Default       |
|-----------------------|--------------------------------------------------------------------------------|-------------------------------------------------------------------|---------------|
| Debug Mode            | `$piperlang->debug = true`                                                     | Enables or disables debug mode.                                   | false         |
| Current Locale        | `$piperlang->current_locale = 'en'`                                            | Sets the current locale.                                          | null          |
| Default Locale        | `$piperlang->default_locale = 'es'`                                            | Sets the default locale.                                          | 'en'          |
| Supported Locales     | `$piperlang->supported_locales = ['en', 'es', 'fr']`                           | Adds locales that the application should support.                 | ['en']        |
| Locale Path           | `$piperlang->locale_path = '/path_to_your_locales/'`                           | Specifies the path to your localization files.                    | '/locales/'   |
| Locale File Extension | `$piperlang->locale_file_extension = 'json'`                                   | Specifies the extension of your localization files.               | 'json'        |
| Loaded Locales        | `$piperlang->loaded_locales = ['en' => ['greeting' => 'Hello']];`              | Adds/edits existing loaded locales.                               | []            |
| Variable Pattern      | `$piperlang->variable_pattern = '/<<(.*?)>>/'`                                 | Alters the variable pattern to something other than the default.  | '/{{(.*?)}}/' |
| Session Enabled       | `$piperlang->session_enabled = false`                                          | Enables or disables the session.                                  | true          |
| Session Key           | `$piperlang->session_key = 'user_lang'`                                        | Changes the session key for storing the user's locale preference. | 'locale'      |
| Cookie Enabled        | `$piperlang->cookie_enabled = false`                                           | Enables or disables the cookie.                                   | false         |
| Cookie Key            | `$piperlang->cookie_key = 'user_lang'`                                         | Alters the cookie key for storing the user's locale preference.   | 'site_locale' |

## Methods
Key methods of `PiperLang` include:

* `getInfo()` - Returns an associative array containing PiperLang configuration details.
* `detectBrowserLocale()` - Detects the preferred locale based on the user's browser settings.
* `detectUserLocale(string $source = 'session')` - Determines the user’s preferred locale based on session or cookie.
* `getLocale()` - Retrieves the current locale.
* `setLocale(?string $preferred_lang = null)` - Sets the locale to the preferred language or defaults if none is provided.
* `setLocalePath(string $path)` - Sets the path for locale files.
* `switchLocale(string $new_lang)` - Switches the locale to a new language.
* `replaceVariables(string $string, array $variables)` - Replaces placeholders in a string with specified variables.
* `loadLocale(string $locale)` - Loads, validates, and processes a locale file.
* `unloadFile(string $locale)` - Unloads a specified locale file from memory.
* `formatNumber(float $number)` - Formats a number according to the current locale.
* `formatCurrency(float $amount, string $currency, bool $show_symbol = false)` - Formats a currency amount per locale.
* `formatDate(DateTime $date, string $format = 'long')` - Formats a date based on locale and specified format.
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
The ```getFormattingRules()``` method shows all the current locale specific numeric and monetary formatting information. It returns an associative array built from values obtained using PHP's ```localeconv()``` method.


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

#### Let's create a code that take advantage of the Modifier.
To ensure the Modifier class functions as expected, follow these steps:

1) Add your own modifications to the Modifier class.
```php
namespace PiperLang;

use MinehubsStudios\Backend\Session;

class Modifier extends PiperLang {    
    /**
    * @var string[]|null
    */
    public ?array $language = [];

    /**
     * MODIFY CONSTRUCTOR.
     *
     * CALLS THE PARENT PiperLang CLASS CONSTRUCTOR
     * FIRST, AND THEN EXECUTE THE CUSTOM CODE.
     *
     * @throws JsonException
     *
     * @see PiperLang::__construct() - FOR THE PARENT CONSTRUCTOR.
     */
    public function __construct() {
        parent::__construct();

        $this->supported_locales = ['en', 'da'];
        $this->locale_path = '/app/assets/locales/';
        $this->debug = true;

        $this->setLocale($this->detectBrowserLocale());
        $this->language = $this->loadFile($this->default_locale);
    }
}
```

2) Installation: Make sure the PiperLang library is installed and available in your project.
3) Include Modifier Class: Include the Modifier class in your project files.
```php
use PiperLang\Modifier;
```
4) Instantiate Modifier: Create an instance of the Modifier class, providing any necessary parameters.
```php
$modifier = new Modifier();
```
