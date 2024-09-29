<?php
    namespace PiperLang;

    use DateTime;
    use IntlDateFormatter;
    use InvalidArgumentException;
    use JsonException;
    use NumberFormatter;
    use RuntimeException;

    /**
     * PiperLang - IS A COMPACT AND EFFICIENT PHP FRAMEWORK DESIGNED TO
     * PROVIDE LOCALIZATION CAPABILITIES FOR YOUR WEB APPLICATION.
     *
     * @package    PiperLang\PiperLang
     * @author     Jacob Jørgensen
     * @license    MIT
     * @version    1.2.0
     */
    class PiperLang {
        /**
         * @var array<string, array<int, array<int, callable>>>
         */
        public array $hooks = [];

        /**
         * @var bool
         */
        public bool $debug = false;

        /**
         * @var string|null
         */
        public ?string $current_locale;

        /**
         * @var string
         */
        public string $default_locale = 'en';

        /**
         * @var array<string>
         */
        public array $supported_locales = ['en'];

        /**
         * @var string
         */
        public string $locale_path = '/locales/';

        /**
         * @var string
         */
        public string $locale_file_extension = 'json';

        /**
         * @var array<string, array<string, string>>
         */
        public array $loaded_locales = [];

        /**
         * @var string|null
         */
        public ?string $variable_pattern = '/{{(.*?)}}/';

        /**
         * @var array<string, string>
         */
        public array $plural_rules = [];

        /**
         * @var bool
         */
        public bool $session_enabled = true;

        /**
         * @var string
         */
        public string $session_key = 'locale';

        /**
         * @var bool
         */
        public bool $cookie_enabled = false;

        /**
         * @var string
         */
        public string $cookie_key = 'site_locale';

        /**
         * PiperLang CONSTRUCTOR
         */
        public function __construct() {}

        /**
         * ADD A HOOK ACTION.
         *
         * @param string $hook_name - THE NAME OF THE HOOK.
         * @param callable $fn - THE CALLABLE FUNCTION OR METHOD.
         * @param int $priority - EXECUTION PRIORITY, LOWER NUMBERS HAVE HIGHER PRIORITY.
         *
         * @return void - THIS METHOD DOES NOT RETURN A VALUE.
         */
        public function addHook(string $hook_name, callable $fn, int $priority = 10): void {
            $this -> hooks[$hook_name][$priority][] = $fn;
        }

        /**
         * RUN ALL HOOKS FOR THE PROVIDED hook_name .
         *
         * @param string $hook_name - THE NAME OF THE HOOK.
         * @param mixed[] $args - PARAMETERS THAT PASSED TO HOOKS FUNCTIONS.
         *
         * @return void - THIS METHOD DOES NOT RETURN A VALUE.
         */
        public function runHooks(string $hook_name, array $args = []): void {
            if (!isset($this -> hooks[$hook_name])) {
                return;
            }

            ksort($this -> hooks[$hook_name]);

            foreach($this -> hooks[$hook_name] as $hooks) {
                foreach($hooks as $hook) {
                    call_user_func_array($hook, $args);
                }
            }
        }

        /**
         * RETRIEVE THE http_accept_language VALUE.
         *
         * @return string - THE http_accept_language VALUE OR AN EMPTY STRING IF NOT SET.
         */
        public function getHttpAcceptLanguage(): string {
            return is_string($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
        }

        /**
         * GET THE PiperLang INFO BASED ON YOUR CURRENT SETUP.
         *
         * @return array<string, mixed> - AN ASSOCIATIVE ARRAY CONTAINING PiperLang Information.
         */
        public function getInfo(): array {
            return [
                'Debug Status' => $this -> debug ?? false,
                'Hooks List' => $this -> hooks ?? [],
                'Current Locale' => $this -> current_locale ?? '',
                'Default Locale' => $this -> default_locale ?? 'en',
                'Supported Locales' => $this -> supported_locales ?? ['en'],
                'Path to Locales' => $this -> locale_path ?? '/locales/',
                'Locale File Extension' => $this -> locale_file_extension ?? 'json',
                'Loaded Locales' => $this -> loaded_locales ?? [],
                'Variable Pattern' => $this -> variable_pattern ?? '/{{(.*?)}}/',
                'Plural Rules' => $this -> plural_rules ?? [],
                'HTTP Accept Locale' => $this -> getHttpAcceptLanguage() ?? '',
                'Session Enabled' => $this -> session_enabled ?? true,
                'Session Key' => $this -> session_key ?? 'locale',
                'Cookie Enabled' => $this -> cookie_enabled ?? false,
                'Cookie Key' => $this -> cookie_key ?? 'site_locale',
            ];
        }

        /**
         * DETECT USER'S PREFERRED LOCALE BASED ON USER'S BROWSER LOCALE.
         *
         * @return string - THE DETECTED LOCALE CODE.
         */
        public function detectBrowserLocale(): string {
            foreach (explode(',', $this -> getHttpAcceptLanguage()) as $locale) {
                $locale_parts = explode(';', $locale, 2);
                $locale_code = strtolower(substr($locale_parts[0], 0, 2));

                if (in_array($locale_code, $this -> supported_locales, true)) {
                    return $locale_code;
                }
            }

            return $this -> default_locale;
        }

        /**
         * DETECT USER'S PREFERRED LOCALE BASED ON USER'S SESSION OR COOKIE.
         *
         * @param string $source - SOURCE FROM WHERE TO DETECT THE LOCALE. CAN BE 'session' OR 'cookie'.
         *
         * @return string - THE DETECTED LOCALE CODE.
         *
         * @throws InvalidArgumentException - WHEN THE PROVIDED SOURCE IS INVALID OR DISABLED.
         */
        public function detectUserLocale(string $source = 'session'): string {
            $locale = $this -> default_locale;

            if ($source === 'session' && $this -> session_enabled) {
                if (session_status() !== PHP_SESSION_ACTIVE) {
                    session_start();
                }

                $locale = $_SESSION[$this -> session_key] ?? '';
            } elseif ($source === 'cookie' && $this -> cookie_enabled) {
                $locale = $_COOKIE[$this -> cookie_key] ?? '';
            } elseif ($this -> debug) {
                throw new InvalidArgumentException("Invalid or disabled source '$source' for detecting locale.");
            }

            return in_array($locale, $this -> supported_locales, true) ? $locale : $this -> default_locale;
        }

        /**
         * GET CURRENT LOCALE
         *
         * @return ?string - THE CURRENT LOCALE CODE.
         */
        public function getLocale(): ?string {
            return $this -> current_locale;
        }

        /**
         * SET THE LOCALE BASED ON GIVEN PREFERENCE, OTHERWISE FALLBACK TO DEFAULT LOCALE.
         *
         * @param string|null $preferred_lang - THE DESIRED LOCALE CODE.
         *
         * @return void - THIS METHOD DOES NOT RETURN A VALUE.
         */
        public function setLocale(?string $preferred_lang = null): void {
            if (!in_array($this -> default_locale, $this -> supported_locales, true)) {
                $this -> supported_locales[] = $this -> default_locale;
            }

            if ($preferred_lang && in_array($preferred_lang, $this -> supported_locales, true)) {
                $this -> current_locale = $preferred_lang;
            } else {
                $this -> current_locale = $this -> default_locale;
            }

            if ($this -> session_enabled) {
                if (session_status() !== PHP_SESSION_ACTIVE) {
                    session_start();
                }

                $_SESSION[$this -> session_key] = $this -> current_locale;

                if ($_SESSION[$this -> session_key] !== $this -> current_locale && $this -> debug) {
                    throw new RuntimeException('Failed to set locale in session');
                }
            }

            if ($this -> cookie_enabled) {
                if (headers_sent() && $this -> debug) {
                    throw new RuntimeException('Failed to set the cookie, headers were already sent');
                }

                if (!setcookie($this -> cookie_key, $this -> current_locale, time() + (86400 * 30), "/") && $this -> debug) {
                    throw new RuntimeException('Failed to set locale in cookie');
                }
            }
        }

        /**
         * SET THE PATH TO THE DIRECTORY CONTAINING THE LOCALE FILES.
         *
         * @param string $path - THE DIRECTORY PATH TO THE LOCALE FILES.
         *
         * @return void - THIS METHOD DOES NOT RETURN A VALUE.
         *
         * @throws RuntimeException - IF THE DIRECTORY PATH IS NOT VALID.
         */
        public function setLocalePath(string $path): void {
            if (!is_dir($path) && $this -> debug) {
                throw new RuntimeException('Locale path must be a valid directory path.');
            }

            $this -> locale_path = $path;
        }

        /**
         * SWITCH THE LOCALE.
         *
         * @param string $new_locale - THE NEW LOCALE TO BE SET.
         *
         * @return void - THIS METHOD DOES NOT RETURN A VALUE.
         */
        public function switchLocale(string $new_locale): void {
            $new_locale = htmlspecialchars($new_locale, ENT_QUOTES, 'UTF-8');

            $this -> setLocale($new_locale);
        }

        /**
         * REPLACE PLACEHOLDERS IN THE GIVEN STRING WITH PROVIDED VARIABLES.
         *
         * @param string $string - THE INPUT STRING.
         * @param array<string, string> $variables - THE VARIABLES TO REPLACE IN THE INPUT STRING.
         *
         * @return string - THE PROCESSED STRING.
         */
        public function replaceVariables(string $string, array $variables): string {
            if ($this -> variable_pattern === null) {
                return $string;
            }

            return preg_replace_callback($this -> variable_pattern, static function ($matches) use ($variables) {
                return $variables[$matches[1]] ?? $matches[0];
            }, $string) ?: '';
        }

        /**
         * LOAD A TRANSLATION WITH PLURAL CONSIDERATION.
         *
         * @param string $key - THE KEY IN THE LOCALE FILE.
         * @param int $count - THE COUNT TO DETERMINE IF PLURAL FORM SHOULD BE USED.
         * @param array<string, string|int> $variables - VARIABLES TO REPLACE IN THE TRANSLATION STRING.
         *
         * @return string - THE TRANSLATED TEXT WITH REQUIRED PLURALIZATION.
         *
         * @throws JsonException - THROWN IF THERE'S ANY PROBLEM IN LOADING AND PARSING THE LOCALE FILE.
         */
        public function translateWithPlural(string $key, int $count, array $variables = []): string {
            $locale = $this -> loadFile($this -> current_locale ?? $this -> default_locale);
            $default_lang = $this -> loadFile($this -> default_locale);

            $plural_suffix = '_other';

            if (isset($this -> plural_rules[$this -> current_locale]) && $count === 1) {
                $plural_suffix = $this -> plural_rules[$this -> current_locale];
            } elseif ($count === 1) {
                $plural_suffix = '_1';
            }

            $final_key = $key . $plural_suffix;

            $missing_translation_message = 'Translation not found.';

            $translation = $locale[$final_key] ?? $default_lang[$final_key] ?? $missing_translation_message;

            $variables['count'] = (string) $count;

            $variables = array_map(function ($value) {
                return (string) $value;
            }, $variables);

            return $this -> replaceVariables($translation, $variables);
        }

        /**
         * LOADS A LOCALE FILE FROM THE DESIGNATED PATH, VALIDATES ITS CONTENT AND PROCESSES THE VARIABLES.
         *
         * @param string $locale - THE TARGET LOCALE CODE.
         *
         * @return array<string, string> - THE PROCESSED locale FILE CONTENT.
         *
         * @throws JsonException - THROWN IF THE LOCALE FILE CONTAINS INVALID JSON.
         * @throws RuntimeException - THROWN IF THERE ARE ISSUES IN READING THE FILE OR MISSING 'variables' DATA.
         */
        public function loadFile(string $locale): array {
            if (isset($_SERVER['DOCUMENT_ROOT']) && is_string($_SERVER['DOCUMENT_ROOT'])) {
                $locale_file = $_SERVER['DOCUMENT_ROOT'] . $this -> locale_path . $locale . '.' . $this -> locale_file_extension;
            } else {
                throw new RuntimeException('DOCUMENT_ROOT is not set or is not a valid string.');
            }

            if (!file_exists($locale_file)) {
                if ($locale !== $this -> default_locale) {
                    $locale = $this -> default_locale;
                    $locale_file = $_SERVER['DOCUMENT_ROOT'] . $this -> locale_path . $locale . '.' . $this -> locale_file_extension;

                    if (!file_exists($locale_file)) {
                        throw new RuntimeException("Default locale file does not exist: $locale_file");
                    }
                } else  {
                    throw new RuntimeException("Locale file does not exist: $locale_file");
                }
            }

            $locale_file_extension = pathinfo($locale_file, PATHINFO_EXTENSION);

            if (strtolower($locale_file_extension) !== 'json') {
                throw new RuntimeException("Unsupported file format. Only JSON file format is supported for locale files.");
            }

            $locale_file_contents = file_get_contents($locale_file);

            if ($locale_file_contents === false) {
                throw new RuntimeException("Unable to read locale file: $locale_file");
            }

            if (empty($locale_file_contents)) {
                throw new RuntimeException("Locale file is empty: $locale_file");
            }

            try {
                $locale_nodes = json_decode($locale_file_contents, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw new JsonException("Invalid JSON in locale file: $locale_file", 0, $exception);
            }

            if (!is_array($locale_nodes) || !array_key_exists('variables', $locale_nodes)) {
                throw new RuntimeException("Missing 'variables' data in locale file: $locale_file");
            }

            $variables = $locale_nodes['variables'] ?? [];

            if (!is_array($variables)) {
                throw new RuntimeException("Locale file $locale_file does not contain required 'variables' data");
            }

            $variables = array_map(function ($value) {
                return is_scalar($value) ? (string) $value : '';
            }, $variables);

            foreach ($locale_nodes as $key => $value) {
                if (is_string($value)) {
                    try {
                        $locale_nodes[$key] = $this -> replaceVariables($value, $variables);
                    } catch (RuntimeException $exception) {
                        throw new RuntimeException("Error replacing variable '$key' in locale file: $locale_file", 0, $exception);
                    }
                }
            }

            $this -> loaded_locales[$locale] = $locale_nodes;

            return $locale_nodes;
        }

        /**
         * UNLOAD A LOADED LOCALE FILE FROM MEMORY.
         *
         * @param string $locale - THE LOCALE CODE TO UNLOAD.
         *
         * @return void - THIS METHOD DOES NOT RETURN A VALUE.
         *
         * @throws RuntimeException - IF THE LOCALE FILE IS ALREADY UNLOADED OR WAS NEVER LOADED.
         */
        public function unloadFile(string $locale): void {
            if (!isset($this -> loaded_locales[$locale]) && $this -> debug) {
                throw new RuntimeException("Locale file $locale is not currently loaded or has been already unloaded");
            }

            unset($this -> loaded_locales[$locale]);
        }

        /**
         * FORMATS A NUMBER ACCORDING TO THE CURRENT LOCALE SETTING.
         *
         * @param float $number - THE NUMBER TO FORMAT.
         *
         * @return string - THE FORMATTED NUMBER ACCORDING TO THE CURRENT LOCALE.
         *
         * @throws InvalidArgumentException - THROWN IF THE INPUT IS NOT A VALID NUMBER FOR FORMATTING.
         * @throws RuntimeException - THROWN IF NUMBER FORMATTING FAILS.
         */
        public function formatNumber(float $number): string {
            if ($this -> current_locale === null) {
                throw new InvalidArgumentException('Current locale not set.');
            }

            $formatter = new NumberFormatter($this -> current_locale, NumberFormatter::DEFAULT_STYLE);

            $formatter -> setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, 2);

            $formatted_number = $formatter -> format($number);

            if ($formatted_number === false) {
                throw new RuntimeException('Number formatting failed: ' . intl_get_error_message());
            }

            return $formatted_number;
        }

        /**
         * FORMATS A CURRENCY AMOUNT ACCORDING TO THE CURRENT LOCALE SETTING.
         *
         * @param float $amount - THE AMOUNT TO FORMAT.
         * @param string $currency - THE ISO 4217 CURRENCY CODE, SUCH AS 'usd' OR 'eur'.
         * @param bool $show_symbol - OPTIONAL, DETERMINES WHETHER TO DISPLAY A PARTICULAR SYMBOL.
         *
         * @return string - THE FORMATTED CURRENCY AMOUNT ACCORDING TO THE CURRENT LOCALE.
         *
         * @throws RuntimeException - THROWN IF CURRENCY FORMATTING FAILS.
         */
        public function formatCurrency(float $amount, string $currency, bool $show_symbol = false): string {
            if (!preg_match("/^[A-Z]{3}$/", $currency) && $this -> debug) {
                throw new InvalidArgumentException('Not a valid ISO 4217 currency code.');
            }

            if ($this -> current_locale === null) {
                throw new InvalidArgumentException('Current locale not set.');
            }

            $formatter = new NumberFormatter($this -> current_locale, NumberFormatter::CURRENCY);

            $formatted_currency = $formatter -> formatCurrency($amount, $currency);

            if ($formatted_currency === false) {
                throw new RuntimeException('Currency formatting failed: ' . intl_get_error_message());
            }

            if ($show_symbol) {
                $symbol = $formatter -> getSymbol(NumberFormatter::CURRENCY_SYMBOL);
                $formatted_currency = str_replace($currency, $symbol, $formatted_currency);
            }

            return $formatted_currency;
        }

        /**
         * FORMATS A DATE ACCORDING TO THE CURRENT LOCALE SETTING.
         *
         * @param DateTime $date - THE DATE TO FORMAT.
         * @param string $format - OPTIONAL. THE DATE FORMAT. CAN BE 'short', 'medium', 'long', OR 'full'. DEFAULTS TO 'long'.
         *
         * @return string - FORMATTED DATE ACCORDING TO THE CURRENT LOCALE.
         *
         * @throws RuntimeException - THROWN IF DATE FORMATTING FAILS.
         */
        public function formatDate(DateTime $date, string $format = 'long'): string {
            $format = strtolower($format);

            $format_style = match ($format) {
                'short' => IntlDateFormatter::SHORT,
                'medium' => IntlDateFormatter::MEDIUM,
                'full' => IntlDateFormatter::FULL,
                default => IntlDateFormatter::LONG,
            };

            $formatter = new IntlDateFormatter($this -> current_locale, $format_style, IntlDateFormatter::NONE);

            $formatted_date = $formatter -> format($date);

            if ($formatted_date === false) {
                throw new RuntimeException('Date formatting failed: ' . intl_get_error_message());
            }

            return $formatted_date;
        }

        /**
         * RETURNS THE FORMATTING RULES SPECIFIC TO THE CURRENT LOCALE LOCALE.
         *
         * @return array<string, string|false|int|float> - ASSOCIATIVE ARRAY CONTAINING LOCALE SPECIFIC NUMERIC AND MONETARY FORMATTING INFORMATION.
         */
        public function getFormattingRules(): array {
            if (empty($this -> current_locale) && $this -> debug) {
                throw new InvalidArgumentException('Not a valid locale code.');
            }

            setlocale(LC_ALL, $this -> current_locale);

            return localeconv();
        }
    }
