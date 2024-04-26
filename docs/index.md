## Key Classes and Methods
Key methods of `PiperLang` class include:

* `detectBrowserLocale()` - Detects user's browser locale.
* `detectUserLocale(string $source = 'session')` - Detects the user's preferred locale based on their session or cookie.
* `getLocale()` - Gets the current locale.
* `setLocale(string $preferred_lang = null)` - Sets the locale based on the preference, otherwise fallback to default locale.
* `switchLocale(string $new_lang)` - Changes the currently set locale to a new one.
* `setLocalePath(string $path)` - Sets the path to the directory containing locale files.
* `replaceVariables(string $string, array $variables)` - Replaces variables in the given string with provided ones.
* `translateWithPlural(string $key, int $count, array $variables = [])` : Translates the provided key considering the count for plural forms. Replaces variables in the translation string.
* `loadFile(string $locale)` - Loads a locale file, validates its content and processes the variables.
* `unloadFile(string $locale)` - Unloads a loaded locale file from memory.
* `numberFormat(float $number)` - Formats a number according to the current locale setting.
* `currencyFormat(float $amount, string $currency, bool $show_symbol = false)` - Formats a currency amount according to the current locale setting.
* `dateFormat(DateTime $date, string $format = 'long')` - Formats a date according to the current locale setting.
* `getFormattingRules()` - Returns the formatting rules specific to the current locale.

## Initializing
```$piperlang = new \PiperLang\PiperLang();```

## Configuration
You can change various settings in the `PiperLang` framework. Here's an example of how you can modify settings after the initializing:

| Setting               | Method                                                               | Description                                                     | Default       |
|-----------------------|----------------------------------------------------------------------|-----------------------------------------------------------------|---------------|
| Default Locale        | `$piperlang->default_locale = 'es'`                                  | Set the default locale.                                         | 'en'          |
| Supported Locales     | `$piperlang->supported_locales = ['en', 'es', 'fr']`                 | Add locales that the application should support.                | ['en']        |
| Locale Path           | `$piperlang->locale_path = '/path_to_your_locales/'`                 | Specify the path to your localization files.                    | '/locales/'   |
| Locale File Extension | `$piperlang->locale_file_extension = 'json'`                         | Specify the extension of your localization files.               | 'json'        |
| Variable Pattern      | `$piperlang->variable_pattern = '/<<(.*?)>>/'`                       | Alter the variable pattern to something other than the default. | '/{{(.*?)}}/' |
| Plural Rules          | `$piperlang->plural_rules = ['es' => '_plural', 'fr' => '_pluriel']` | Define the plural rules for your supported locales.             | []            |
| Session Enabled       | `$piperlang->session_enabled = false`                                | Enable or disable session.                                      | true          |
| Session Key           | `$piperlang->session_key = 'user_lang'`                              | Change the session key for storing user locale preference.      | 'locale'      |
| Cookie Enabled        | `$piperlang->cookie_enabled = false`                                 | Enable or disable cookie.                                       | false         |
| Cookie Key            | `$piperlang->cookie_key = 'user_lang'`                               | Alter the cookie key for storing user locale preference.        | 'site_locale' |

### Setting a Locale
```$piperlang->setLocale("fr");```

It's important to note that the locale chosen should be one amongst the supported locales. The default supported locale is English ("en").

### Translating Text with Replacement and Plural Forms
```$translation = $piperlang->translateWithPlural("item_count", 5, ["items"=>"books"]);```

In the provided locale files (example `en.json`):

    {"item_count_1": "{{count}} item", "item_count_other": "{{count}} items"}

The corresponding translation considering the plural forms will be used, here `count` in `item_count_other` will be replaced with the provided count.

### Formatting Date
```$date = new DateTime("2010-07-05T06:30:00");``` <br>
```$formattedDate = $piperlang->dateFormat($date, 'long');```

Remember to set the locale before making a call to `dateFormat()`. The provided code will format the date into a 'long' style for the set locale.

### Number Formatting
```$formattedNumber = $piperlang->numberFormat(1234567.89);```

### Currency Formatting
```$formattedCurrency = $piperlang->currencyFormat(1234567.89, "USD", true);```

It's advisable to set the locale before calling `numberFormat()` or `currencyFormat()` for the desired locale-dependent format.

### Getting Formatting Rules
```$formattingRules = $piperlang->getFormattingRules();```

This fetches currency, number formatting data, as well as other locale-specific information.

## Getting Started (EXAMPLE)
```use PiperLang\PiperLang;``` <br>
```$piperLang = new PiperLang();``` <br>
```$piperLang->setLocale('fr'); // set locale to French``` <br>
```$greetingText = $piperLang->translateWithPlural('hello', 1); // hello in French``` <br>
```echo $greetingText;``` <br>
```$formattedDate = $piperLang->dateFormat(new DateTime()); // today's date in French format``` <br>
```echo $formattedDate;```