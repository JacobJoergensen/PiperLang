<?php
    namespace PiperLang\PiperLang;

    use DateTime;
    use IntlDateFormatter;
    use InvalidArgumentException;
    use JsonException;
    use NumberFormatter;
    use RuntimeException;

    class PiperLang {
        /**
         * @var string|null
         */
        public ?string $current_language;

        /**
         * @var string
         */
        public string $default_language = 'en';

        /**
         * @var array<string>
         */
        public array $supported_languages = ['en'];

        /**
         * @var string
         */
        private string $locale_path = '/locales/';

        /**
         * @var string
         */
        public string $locale_file_extension = 'json';

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
        private bool $session_enabled = true;

        /**
         * @var string
         */
        public string $session_key = 'lang';

        /**
         * @var string
         */
        public string $cookie_key = 'site_language';

        /**
         * @var string
         */
        private string $http_accept_language;

        /**
         * PiperLang CONSTRUCTOR
         */
        public function __construct() {
            if ($this -> session_enabled && session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            if ($this -> session_enabled && session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
                $this -> current_language = isset($_SESSION[$this -> session_key]) && is_string($_SESSION[$this -> session_key]) ? $_SESSION[$this -> session_key] : null;
            } else {
                $this -> current_language = $this -> default_language;
            }

            $this -> http_accept_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '';
        }

        /**
         * DETECT USER'S PREFERRED LANGUAGE BASED ON USER'S BROWSER LANGUAGE.
         *
         * @return string - THE DETECTED LANGUAGE CODE.
         */
        public function detectBrowserLanguage(): string {
            foreach (explode(',', $this->http_accept_language) as $lang) {
                $lang_parts = explode(';', $lang, 2);
                $lang_code = strtolower(substr($lang_parts[0], 0, 2));

                if (in_array($lang_code, $this -> supported_languages, true)) {
                    return $lang_code;
                }
            }

            return $this -> default_language;
        }

        /**
         * DETECT USER'S PREFERRED LANGUAGE BASED ON USER'S SESSION OR COOKIE.
         *
         * @return string - THE DETECTED LANGUAGE CODE.
         */
        public function detectUserLanguage(): string {
            $language = $_SESSION[$this -> session_key] ?? $_COOKIE[$this -> cookie_key] ?? '';

            if (in_array($language, $this -> supported_languages, true)) {
                return $language;
            }

            return $this -> default_language;
        }

        /**
         * SET THE LANGUAGE BASED ON GIVEN PREFERENCE, OTHERWISE FALLBACK TO DEFAULT LANGUAGE.
         *
         * @param string|null $preferred_language - THE DESIRED LANGUAGE CODE.
         *
         * @return void - THIS METHOD DOES NOT RETURN A VALUE.
         */
        public function setLanguage(?string $preferred_language = null): void {
            if (!in_array($this -> default_language, $this -> supported_languages, true)) {
                $this -> supported_languages[] = $this -> default_language;
            }

            if ($preferred_language && in_array($preferred_language, $this -> supported_languages, true)) {
                $this -> current_language = $preferred_language;
                if ($this -> session_enabled) {
                    $_SESSION[$this -> session_key] = $preferred_language;
                }
            } else {
                $this -> current_language = $this -> default_language;
            }
        }

        /**
         * SET THE PATH TO THE DIRECTORY CONTAINING THE LANGUAGE FILES.
         *
         * @param string $path - THE DIRECTORY PATH TO THE LANGUAGE FILES.
         *
         * @return void - THIS METHOD DOES NOT RETURN A VALUE.
         *
         * @throws RuntimeException - IF THE DIRECTORY PATH IS NOT VALID.
         */
        public function setLocalePath(string $path): void {
            if (!is_dir($path)) {
                throw new RuntimeException('Locale path must be a valid directory path.');
            }

            $this -> locale_path = $path;
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
         * @param string $key - THE KEY IN THE LANGUAGE FILE.
         * @param int $count - THE COUNT TO DETERMINE IF PLURAL FORM SHOULD BE USED.
         * @param array<string, string|int> $variables - VARIABLES TO REPLACE IN THE TRANSLATION STRING.
         *
         * @return string - THE TRANSLATED TEXT WITH REQUIRED PLURALIZATION.
         *
         * @throws JsonException - THROWN IF THERE'S ANY PROBLEM IN LOADING AND PARSING THE LANGUAGE FILE.
         */
        public function translateWithPlural(string $key, int $count, array $variables = []): string {
            $lang = $this -> loadLanguage($this -> current_language ?? $this -> default_language);
            $default_lang = $this -> loadLanguage($this -> default_language);

            $plural_suffix = '_other';

            if(isset($this -> plural_rules[$this -> current_language]) && $count === 1){
                $plural_suffix = $this -> plural_rules[$this -> current_language];
            } elseif($count === 1){
                $plural_suffix = '_1';
            }

            $final_key = $key . (is_array($plural_suffix) ? '' : $plural_suffix);

            $missing_translation_message = 'Translation not found.';

            $translation = $lang[$final_key] ?? $default_lang[$final_key] ?? $missing_translation_message;

            array_walk($variables, function (&$value) {
                $value = (string) $value;
            });

            $variables['count'] = (string) $count;

            return $this -> replaceVariables($translation, $variables);
        }

        /**
         * LOAD THE LANGUAGE FILE FOR A GIVEN LANGUAGE CODE.
         *
         * @param string $language - THE TARGET LANGUAGE CODE.
         *
         * @return array<string, string>|null - THE LOADED LANGUAGE FILE CONTENT.
         *
         * @throws JsonException - THROWN IF THE LANGUAGE FILE CONTAINS INVALID JSON.
         */
        public function loadLanguage(string $language): ?array {
            try {
                $default_lang_file_path = $this -> locale_path . $this -> default_language . $this -> locale_file_extension;

                if (!file_exists($default_lang_file_path)) {
                    throw new RuntimeException("Default language file does not exist: $default_lang_file_path");
                }

                $default_lang = $this -> loadFile($this -> default_language);
            } catch (RuntimeException $exception) {
                throw new RuntimeException("Error loading default language file '$default_lang_file_path': {$exception -> getMessage()}");
            }

            if ($language === $this -> default_language) {
                return $default_lang;
            }

            try {
                $selected_lang = $this -> loadFile($language);
            } catch (RuntimeException $exception) {
                throw new RuntimeException("Error loading language file for '$language': {$exception -> getMessage()}");
            }

            return array_merge($default_lang, $selected_lang);
        }

        /**
         * LOADS A LANGUAGE FILE FROM THE DESIGNATED PATH, VALIDATES ITS CONTENT AND PROCESSES THE VARIABLES.
         *
         * @param string $language - THE TARGET LANGUAGE CODE.
         *
         * @return array<string, string> - THE PROCESSED LANGUAGE FILE CONTENT.
         *
         * @throws JsonException - THROWN IF THE LANGUAGE FILE CONTAINS INVALID JSON.
         * @throws RuntimeException - THROWN IF THERE ARE ISSUES IN READING THE FILE OR MISSING 'variables' DATA.
         */
        protected function loadFile(string $language): array {
            $language_file = $this -> locale_path . $language . $this -> locale_file_extension;

            if (!file_exists($language_file)) {
                throw new RuntimeException("Language file does not exist: $language_file");
            }

            $lang_file = file_get_contents($language_file);

            if ($lang_file === false) {
                throw new RuntimeException("Unable to read language file: $language_file");
            }

            if (empty($lang_file)) {
                throw new RuntimeException("Language file is empty: $language_file");
            }

            try {
                $lang = json_decode($lang_file, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $exception) {
                throw new JsonException("Invalid JSON in language file: $language_file", 0, $exception);
            }

            if (!is_array($lang) || !array_key_exists('variables', $lang)) {
                throw new RuntimeException("Missing 'variables' data in language file: $language_file");
            }

            $variables = $lang['variables'] ?? [];

            /**
             * @phpstan-ignore-next-line
             */
            if (is_null($variables)) {
                throw new RuntimeException("Language file $language_file does not contain required 'variables' data");
            }

            if (is_array($variables)) {
                unset($lang['variables']);

                foreach ($lang as $key => $value) {
                    if (is_string($value)) {
                        try {
                            $lang[$key] = $this -> replaceVariables($value, $variables);
                        } catch (RuntimeException $exception) {
                            throw new RuntimeException("Error replacing variable '$key' in language file: $language_file", 0, $exception);
                        }
                    }
                }
            }

            return $lang;
        }

        /**
         * FORMATS A NUMBER ACCORDING TO THE CURRENT LANGUAGE SETTING.
         *
         * @param float $number - THE NUMBER TO FORMAT.
         *
         * @return string - THE FORMATTED NUMBER ACCORDING TO THE CURRENT LANGUAGE.
         *
         * @throws InvalidArgumentException - THROWN IF THE INPUT IS NOT A VALID NUMBER FOR FORMATTING.
         * @throws RuntimeException - THROWN IF NUMBER FORMATTING FAILS.
         */
        public function numberFormat(float $number): string {
            if (!is_numeric($number)) {
                throw new InvalidArgumentException('Not a valid number for formatting.');
            }

            $formatter = new NumberFormatter($this -> current_language, NumberFormatter::DEFAULT_STYLE);

            $formatted_number = $formatter -> format($number);

            if ($formatted_number === false) {
                throw new RuntimeException('Number formatting failed.');
            }

            return $formatted_number;
        }

        /**
         * FORMATS A CURRENCY AMOUNT ACCORDING TO THE CURRENT LANGUAGE SETTING.
         *
         * @param float $amount - THE AMOUNT TO FORMAT.
         * @param string $currency - THE ISO 4217 CURRENCY CODE, SUCH AS 'usd' OR 'eur'.
         *
         * @return string - THE FORMATTED CURRENCY AMOUNT ACCORDING TO THE CURRENT LANGUAGE.
         *
         * @throws RuntimeException - THROWN IF CURRENCY FORMATTING FAILS.
         */
        public function currencyFormat(float $amount, string $currency): string {
            if (!is_numeric($amount)) {
                throw new InvalidArgumentException('Not a valid amount for currency formatting.');
            }

            if (!preg_match("/^[A-Z]{3}$/", $currency)) {
                throw new InvalidArgumentException('Not a valid ISO 4217 currency code.');
            }

            $formatter = new NumberFormatter($this -> current_language, NumberFormatter::CURRENCY);

            $formatted_currency = $formatter -> formatCurrency($amount, $currency);

            if ($formatted_currency === false) {
                throw new RuntimeException('Currency formatting failed.');
            }

            return $formatted_currency;
        }

        /**
         * FORMATS A DATE ACCORDING TO THE CURRENT LANGUAGE SETTING.
         *
         * @param DateTime $date - THE DATE TO FORMAT.
         * @param string $format - OPTIONAL. THE DATE FORMAT. CAN BE 'short', 'medium', 'long', OR 'full'. DEFAULTS TO 'long'.
         *
         * @return string - FORMATTED DATE ACCORDING TO THE CURRENT LANGUAGE.
         *
         * @throws RuntimeException - THROWN IF DATE FORMATTING FAILS.
         */
        public function dateFormat(DateTime $date, string $format = 'long'): string {
            if (!in_array($format, ['short', 'medium', 'long', 'full'])) {
                $format = 'long';
            }

            $formatter = new IntlDateFormatter($this -> current_language, IntlDateFormatter::{$format}, IntlDateFormatter::NONE);

            $formatted_date = $formatter -> format($date);

            if ($formatted_date === false) {
                throw new RuntimeException('Date formatting failed.');
            }

            return $formatted_date;
        }

        /**
         * RETURNS THE FORMATTING RULES SPECIFIC TO THE CURRENT LANGUAGE LOCALE.
         *
         * @return array<string, string|false|int|float> - ASSOCIATIVE ARRAY CONTAINING LOCALE SPECIFIC NUMERIC AND MONETARY FORMATTING INFORMATION.
         */
        public function getFormattingRules(): array {
            if (empty($this -> current_language)) {
                throw new InvalidArgumentException('Not a valid language code.');
            }

            setlocale(LC_ALL, $this -> current_language);

            return localeconv();
        }
    }
