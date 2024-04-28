<h3 align="center">PiperLang DOCUMENTATION</h3>

<hr>

## Quick Navigation

- [Installing](#Installing)
- [Quick start](#Quick Start)
- [Configuration](#Configuration)
- [Methods](#methods)
- [Formatting](#formatting)
- [Modifier](#modifier)
- [Hook](#hook)
- [Docker](#docker)
- <a href="https://github.com/JacobJoergensen/PiperLang/CHANGELOG.md">Changelog</a>


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
Once PiperLang is installed, you can start using it in your PHP projects by including the necessary files or by using Composer's autoloading feature to autoload PiperLang classes.

1) Include Autoloader: If you're using Composer autoloading, include Composer's autoloader in your PHP script:
```php
require_once __DIR__ . '/vendor/autoload.php';
```
If  you're not using Composer autoloading, include the PiperLang autoloader directly:
```php
require_once '/path/to/piperlang/autoload.php';
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

| Setting               | Method                                                                         | Description                                                           | Default       |
|-----------------------|--------------------------------------------------------------------------------|-----------------------------------------------------------------------|---------------|
| Debug Mode            | `$piperlang->debug = true`                                                     | Enables or disables debug mode.                                       | false         |
| Current Locale        | `$piperlang->current_locale = 'en'`                                            | Sets the current locale.                                              | null          |
| Default Locale        | `$piperlang->default_locale = 'es'`                                            | Sets the default locale.                                              | 'en'          |
| Supported Locales     | `$piperlang->supported_locales = ['en', 'es', 'fr']`                           | Adds locales that the application should support.                     | ['en']        |
| Locale Path           | `$piperlang->locale_path = '/path_to_your_locales/'`                           | Specifies the path to your localization files.                        | '/locales/'   |
| Locale File Extension | `$piperlang->locale_file_extension = 'json'`                                   | Specifies the extension of your localization files.                   | 'json'        |
| Loaded Locales        | `$piperlang->loaded_locales = ['en' => ['greeting' => 'Hello']];`              | Adds/edits existing loaded locales.                                   | []            |
| Variable Pattern      | `$piperlang->variable_pattern = '/<<(.*?)>>/'`                                 | Alters the variable pattern to something other than the default.      | '/{{(.*?)}}/' |
| Plural Rules          | `$piperlang->plural_rules = ['es' => '_plural', 'fr' => '_pluriel']`           | Defines the plural rules for your supported locales.                  | []            |
| Session Enabled       | `$piperlang->session_enabled = false`                                          | Enables or disables the session.                                      | true          |
| Session Key           | `$piperlang->session_key = 'user_lang'`                                        | Changes the session key for storing the user's locale preference.     | 'locale'      |
| Cookie Enabled        | `$piperlang->cookie_enabled = false`                                           | Enables or disables the cookie.                                       | false         |
| Cookie Key            | `$piperlang->cookie_key = 'user_lang'`                                         | Alters the cookie key for storing the user's locale preference.       | 'site_locale' |
| HTTP Accept Locale    | `$piperlang->http_accept_locale = 'en'`                                        | Sets the HTTP accept language header.                                 | null          |
| Hooks                 | `$piperlang->hooks = ['onLocaleChange' => [['priority' => 1, 'fn' => func]]];` | Adds/edits hooks for events. The array inside supports priority & fn. | []            |


## Methods
Key methods of `PiperLang` include:

* `getInfo()` - Returns an associative array containing PiperLang information.
* `addHook(string $hook_name, callable $fn, int $priority = 10)` - Adds a hook action with a specific callback function and a priority.
* `runHooks(string $hook_name, array $args = [])` - Runs all actions associated with a given hook.
* `detectBrowserLocale()` - Detects the preferred locale based on the user's browser.
* `detectUserLocale(string $source = 'session')` - Detects and returns the preferred locale based on the user's session or cookie.
* `getLocale()` - Returns the current locale.
* `setLocale(?string $preferred_lang = null)` - Sets the locale to the provided preference or the default locale if none is provided.
* `setLocalePath(string $path)` - Sets the path to the directory containing locale files.
* `switchLocale(string $new_lang)` - Switches the current locale to the new one provided.
* `replaceVariables(string $string, array $variables)` - Returns a string with replaced placeholders with provided variables.
* `translateWithPlural(string $key, int $count, array $variables = [])` - Returns a translated string with plural consideration for a given key and count. Also replaces any variables in the string.
* `loadFile(string $locale)` - Loads a locale file, validates its content, and processes the variables inside it.
* `unloadFile(string $locale)` - Unloads a loaded locale file from memory.
* `formatNumber(float $number)` - Returns a formatted number string according to the current locale setting.
* `formatCurrency(float $amount, string $currency, bool $show_symbol = false)` - Returns a formatted currency string according to the current locale setting.
* `formatDate(DateTime $date, string $format = 'long')` - Returns a formatted date string according to the current locale setting.
* `getFormattingRules()` - Returns the formatting rules specific to the current locale.


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


## Hook
The hook system allows you as a developer to integrate custom actions or modifications into specific points of execution within the PiperLang framework.

#### Adding a Hook Action
To add a hook action, use the addHook method provided by the PiperLang class.
```php
public function addHook(string $hook_name, callable $fn, int $priority = 10):
```

##### Parameters:
* ```$hook_name (string)```: The name of the hook.
* ```$fn (callable)```: The callable function or method to be executed when the hook is triggered.
* ```$priority (int)```: Execution priority of the hook action. Lower numbers indicate higher priority.

##### Example:
```php
$piper->addHook('before_save', function() {
    // Custom action before saving data
});
```

#### Running Hooks
To execute all hooks associated with a particular hook name, use the runHooks method provided by the PiperLang class.
```php
public function runHooks(string $hook_name, array $args = []):
```

##### Parameters:
* ```$hook_name (string)```: The name of the hook for which to execute associated actions.
* ```$args (mixed[])```: Parameters to be passed to the hook functions.

##### Example:
```php
$piper->runHooks('before_save', [$data]);
```

This will execute all hook actions associated with the ```"before_save"``` hook, passing $data as an argument to each hook function.


## Docker
The Docker setup for PiperLang provides a convenient environment for running the application with all dependencies installed.

#### Dockerfile
The Dockerfile defines the environment for running PiperLang. It uses PHP 8.3 CLI version as the base image and installs necessary dependencies.
```dockerfile
FROM php:8.3-cli

RUN apt-get update && apt-get upgrade -y && apt-get install -y libicu-dev
RUN docker-php-ext-install intl
WORKDIR /PiperLang

COPY . /PiperLang

RUN curl --silent --show-error https://getcomposer.org/installer | php \
&& mv composer.phar /usr/local/bin/composer

RUN composer install
```

##### Instructions:
* Base Image: The Dockerfile starts with the PHP 8.3 CLI version as the base image.
* Dependencies: It installs the libicu-dev package required for internationalization support.
* Composer: Downloads and installs Composer, a dependency manager for PHP.
* Application Setup: Copies the application code into the container and installs dependencies using Composer.

#### Docker Compose Configuration
The Docker Compose configuration simplifies container orchestration and defines the services required for running PiperLang.
```yml
version: '3'
services:
  app:
    build: .
    volumes:
      - .:/PiperLang
    working_dir: /PiperLang
```

##### Instructions:
* Service: Defines the app service for running PiperLang.
* Build: Specifies to build the Docker image using the current directory (.) containing the Dockerfile.
* Volumes: Mounts the current directory as a volume inside the container to enable live code reloading and development.
* Working Directory: Sets the working directory inside the container to /PiperLang.

#### Usage
To use Docker with PiperLang:

1) Create a Dockerfile with the specified content.
2) Create a docker-compose.yml file with the specified content.
3) Run docker-compose up --build to build and start the containerized environment.

With Docker, you can easily set up a consistent development environment for PiperLang across different platforms.
