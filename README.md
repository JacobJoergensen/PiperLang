# PiperLang (BETA STAGE - WORK IN PROGRESS)
PiperLang is a compact and efficient PHP framework designed to provide localization capacities for your web application. With features supporting cookie and session-based language detection, dynamic pluralization, variable substitution in translations, and number, currency and date formatting in accordance with the set language.

<br>

> :warning: **Beta Stage Warning:** PiperLang is currently in a Beta stage. It is a work in progress and should not be used in production applications at the moment. Unexpected bugs and errors may happen, and features may change without prior announcements. We encourage you to participate in our testing and development phases by reporting any encountered issues.
>
> :information_source: **PHP Version:** The development cycle of PiperLang operates on PHP 8.3. For optimal compatibility and performance, we recommend using this version when working with PiperLang.

<br>

## Key Classes and Methods
Key methods of `PiperLang` class include:

* `detectBrowserLanguage()` - Detects user's browser language.
* `detectUserLanguage()` - Detects the user's preferred language based on their session or cookie.
* `setLanguage(string $preferred_language = null)` - Sets the language based on the preference, otherwise fallback to default language.
* `translateWithPlural(string $key, int $count, array $variables = [])` : Translates the provided key considering the count for plural forms. Replaces variables in the translation string.
* `numberFormat(float $number)` - Formats a number according to the current language setting.
* `currencyFormat(float $amount, string $currency)` - Formats a currency amount according to the current language setting.
* `dateFormat(DateTime $date, string $format = 'long')` - Formats a date according to the current language setting.
* `getFormattingRules()` - Returns the formatting rules specific to the current language locale.

## Initializing
```$piperlang = new \PiperLang\PiperLang();```

## Configuration
You can change various settings in the `PiperLang` framework. Here's an example of how you can modify settings after the initializing:

| Setting | Method  | Description | Default |
| --- | --- | --- | --- |
| Default Language | `$piperlang->default_language = 'es'` | Set the default language to Spanish. | 'en' |
| Supported Languages | `$piperlang->supported_languages = ['en', 'es', 'fr']` | Add languages that the application should support. | ['en'] |
| Locale Path | `$piperlang->locale_path = '/path_to_your_locales/'` | Specify the path to your localization files. | '/locales/' |
| Locale File Extension | `$piperlang->locale_file_extension = 'txt'` | Specify the extension of your localization files. | 'json' |
| Variable Pattern | `$piperlang->variable_pattern = '/<<(.*?)>>/'` | Alter the variable pattern to something other than the default. | '/{{(.*?)}}/' |
| Plural Rules | `$piperlang->plural_rules = ['es' => '_plural', 'fr' => '_pluriel']` | Define the plural rules for your supported languages. | [] |
| Session Key | `$piperlang->session_key = 'user_lang'` | Change the session key for storing user language preference. | 'lang' |
| Cookie Key | `$piperlang->cookie_key = 'user_lang'` | Alter the cookie key for storing user language preference. | 'site_language' |
| Session Management | `$piperlang->session_enabled = false` | Enable or disable session management. | true |

### Setting a Language Preference
```$piperlang->setLanguage("fr");```

It's important to note that the language chosen should be one amongst the supported languages. The default supported language is English ("en").

### Translating Text with Replacement and Plural Forms
```$translation = $piperlang->translateWithPlural("item_count", 5, ["items"=>"books"]);```

In the provided language files (example `en.json`):

    {"item_count_1": "{{count}} item", "item_count_other": "{{count}} items"}

The corresponding translation considering the plural forms will be used, here `count` in `item_count_other` will be replaced with the provided count.

### Formatting Date
```$date = new DateTime("2010-07-05T06:30:00"); $formattedDate = $piperlang->dateFormat($date, 'long');```

Remember to set the language before making a call to `dateFormat()`. The provided code will format the date into a 'long' style for the set language.

### Number Formatting 
```$formattedNumber = $piperlang->numberFormat(1234567.89);```

### Currency Formatting
```$formattedCurrency = $piperlang->currencyFormat(1234567.89, "USD");```

It's advisable to set the language before calling `numberFormat()` or `currencyFormat()` for the desired locale-dependent format.

### Getting Formatting Rules
```$formattingRules = $piperlang->getFormattingRules();```

This fetches currency, number formatting data, as well as other locale-specific information. 

With these examples and key points in mind, using the PiperLang framework should be straightforward. For deeper insights or trouble related to any point, feel free to raise a query.

<br>

## Issues and Pull Requests

For bug reports and feature requests, please use the Issues tab.

* **Report bug**: [Click here](https://github.com/JacobJoergensen/PiperLang/issues/)
* **Request feature**: [Click here](https://github.com/JacobJoergensen/PiperLang/issues/)

For direct code contributions, please open a Pull Request.

* **Open a new Pull Request**: [Click here](https://github.com/JacobJoergensen/PiperLang/compare)

Remember, your input plays a big role in making the framework better for everyone. We greatly appreciate your help and suggestions!

<br>
