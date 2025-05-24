<?php
	namespace PiperLang;

	use DateTime;
	use IntlDateFormatter;
	use InvalidArgumentException;
	use JsonException;
	use NumberFormatter;
	use RuntimeException;

	/**
	 * PiperLang - Is a compact and efficient PHP library designed to
	 * provide localization capabilities for your web application.
	 *
	 * @package    PiperLang\PiperLang
	 * @author     Jacob Jørgensen
	 * @license    MIT
	 * @version    2.0.0
	 */
	class PiperLang {
		/**
		 * Current locale code (e.g., "en", "fr").
		 *
		 * @var string|null
		 */
		public ?string $current_locale;

		/**
		 * Loaded locales.
		 *
		 * @var array<string, array<string, string>>
		 */
		public array $loaded_locales = [];

		/**
		 * Pattern for variable replacement in translation strings.
		 *
		 * @var string|null
		 */
		public ?string $variable_pattern = '/{{(.*?)}}/';

		/**
		 * PiperLang constructor.
		 */
		public function __construct(
			public string $allowed_tags = '<a><br>',
			public bool $cookie_enabled = false,
			public string $cookie_key = 'site_locale',
			public bool $debug = false,
			public string $default_locale = 'en',
			public string $locale_path = '/locales/',
			public string $locale_file_extension = 'json',
			public bool $session_enabled = true,
			public string $session_key = 'locale',
            /** @var string[] */
			public array $supported_locales = ['en']
		) {
			$detected_locale = $this->detectLocale();

			$this->current_locale = $detected_locale;
			$this->setLocale($detected_locale);
		}

		/**
		 * Get PiperLang info based on your current setup.
		 *
		 * @return array<string, mixed> - An associative array containing PiperLang Information.
		 */
		public function getInfo(): array {
			return [
				'Debug Status'          => $this->debug,
				'Current Locale'        => $this->current_locale ?? '',
				'Default Locale'        => $this->default_locale,
				'Supported Locales'     => $this->supported_locales,
				'Path to Locales'       => $this->locale_path,
				'Locale File Extension' => $this->locale_file_extension,
				'Loaded Locales'        => $this->loaded_locales ?? [],
				'Allowed HTML Tags'     => $this->allowed_tags,
				'Variable Pattern'      => $this->variable_pattern,
				'Session Enabled'       => $this->session_enabled,
				'Session Key'           => $this->session_key,
				'Cookie Enabled'        => $this->cookie_enabled,
				'Cookie Key'            => $this->cookie_key,
			];
		}

		/**
		 * Detects the user's preferred locale based on the following priority:
		 * 1. Session (if enabled)
		 * 2. Cookie (if enabled)
		 * 3. HTTP Accept-Language header
		 * 4. Default locale (fallback)
		 *
		 * If a valid locale is detected, it is stored in the session and/or cookie
		 * depending on the configuration.
		 *
		 * @return string - The detected and validated locale code
		 */
		public function detectLocale(): string {
			$locale = null;

			// 1. Session check.
			if ($this->session_enabled) {
				if (session_status() !== PHP_SESSION_ACTIVE) {
					session_start();
				}

				$locale = $_SESSION[$this->session_key] ?? null;
			}

			// 2. Cookie check.
			if (!$locale && $this->cookie_enabled) {
				$locale = $_COOKIE[$this->cookie_key] ?? null;
			}

			// 3. Browser language header.
			if (!$locale && isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && is_string($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
				foreach (explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $lang) {
					$lang_code = strtolower(substr(trim(explode(';', $lang)[0]), 0, 2));

					if (in_array($lang_code, $this->supported_locales, true)) {
						$locale = $lang_code;
						break;
					}
				}
			}

			// 4. Fallback to default locale.
			if (!in_array($locale, $this->supported_locales, true)) {
				$locale = $this->default_locale;
			}

			// Save the detected locale back to session/cookie if enabled.
			if ($this->session_enabled) {
				$_SESSION[$this->session_key] = $locale;
			}

			if ($this->cookie_enabled) {
				setcookie($this->cookie_key, $locale, time() + 3600 * 24 * 30, "/");
			}

			return $locale;
		}

		/**
		 * Get current locale code.
		 *
		 * @return ?string - The current locale code, or the default locale if not set.
		 */
		public function getLocale(): ?string {
			return $this -> current_locale ?? $this -> default_locale;
		}

		/**
		 * Get the translation for a given key.
		 *
		 * @param string $key  - The key for the translation
		 * @param bool $escape - Whether to escape HTML characters (default: true)
		 *
		 * @return string      - The translated string
		 */
		public function getTranslation(string $key, bool $escape = true): string {
			$translations = $this->loaded_locales[$this->current_locale] ?? [];
			$translation = array_key_exists($key, $translations)
				? (string) $translations[$key]
				: "Translation missing: $key";

			if ($escape) {
				$translation = html_entity_decode($translation, ENT_QUOTES | ENT_HTML5, 'UTF-8');
				$translation = strip_tags($translation, $this->allowed_tags);
			}

			return $translation;
		}

		/**
		 * Sets the active locale for the current request/session.
		 *
		 * @param string|null $locale - The preferred locale code (optional)
		 * @param bool $force         - Force set the locale even if not supported
		 *
		 * @return bool               - True if locale was set successfully, otherwise false
		 *
		 * @throws JsonException      - If the locale file is not valid and debug mode is enabled
		 */
		public function setLocale(?string $locale = null, bool $force = false): bool {
			// Ensure default locale is supported.
			if (!in_array($this->default_locale, $this->supported_locales, true)) {
				$this->supported_locales[] = $this->default_locale;
			}

			if ($locale === null || (!$force && !in_array($locale, $this->supported_locales, true))) {
				$locale = $_GET['locale'] ?? null;

				if (!$locale && $this->session_enabled && isset($_SESSION[$this->session_key])) {
					$locale = $_SESSION[$this->session_key];
				}

				if (!$locale && $this->cookie_enabled && isset($_COOKIE[$this->cookie_key])) {
					$locale = $_COOKIE[$this->cookie_key];
				}

				if (!$locale) {
					$locale = $this->detectLocale();
				}
			}

			if (!$force && !in_array($locale, $this->supported_locales, true)) {
				$locale = $this->default_locale;
			}

            if (is_string($locale)) {
                $this->current_locale = $locale;
            } else {
                throw new InvalidArgumentException('Locale must be a string.');
            }

			if ($this->session_enabled) {
				if (session_status() !== PHP_SESSION_ACTIVE) {
					session_start();
				}

				$_SESSION[$this->session_key] = $this->current_locale;

				if ($_SESSION[$this->session_key] !== $this->current_locale && $this->debug) {
					throw new RuntimeException('Failed to set locale in session');
				}
			}

			if ($this->cookie_enabled) {
				if (headers_sent() && $this->debug) {
					throw new RuntimeException('Failed to set cookie: headers already sent');
				}

				if (!setcookie($this->cookie_key, $this->current_locale, time() + 86400 * 30, "/") && $this->debug) {
					throw new RuntimeException('Failed to set locale in cookie');
				}
			}

			// Autoload locale if not already loaded.
			if (!isset($this->loaded_locales[$this->current_locale])) {
				$this->loadLocale($this->current_locale);
			}

			return true;
		}

		/**
		 * Replaces placeholders in a string with variable values.
		 *
		 * @param string $string                   - The input string containing placeholders.
		 * @param array<string, string> $variables - The variables to replace in the string.
		 *
		 * @return string                          - The processed string with variables replaced.
		 */
		public function replaceVariables(string $string, array $variables): string {
            if (is_string($this->variable_pattern) && $this->variable_pattern !== '/{{(.*?)}}/') {
                // Fallback to regex if a custom pattern is set.
                return preg_replace_callback(
                    $this->variable_pattern,
                    static function ($matches) use ($variables) {
                        return $variables[$matches[1]] ?? $matches[0];
                    },
                    $string
                ) ?: '';
            }

            // Optimized replacement using strtr for the default pattern.
			$placeholders = array_map(fn($key) => '{{' . $key . '}}', array_keys($variables));
			$replacements = array_combine($placeholders, $variables);

			return strtr($string, $replacements);
		}

		/**
		 * Loads and parses a locale JSON file and stores its translations.
		 *
		 * @param string $locale                  - Locale code (e.g. 'en', 'fr')
		 *
		 * @return void
		 *
		 * @throws RuntimeException|JsonException - On file or JSON issues when debug mode is enabled
		 */
		public function loadLocale(string $locale): void {
			$document_root = $_SERVER['DOCUMENT_ROOT'] ?? null;

			if (!is_string($document_root) || empty($document_root)) {
				if ($this->debug) {
					throw new RuntimeException('DOCUMENT_ROOT is not set or is invalid.');
				}

				return;
			}

			$path = $document_root . $this->locale_path . $locale . '.' . $this->locale_file_extension;

			if (!file_exists($path)) {
				// Fallback to the default locale if not already tried.
				if ($locale !== $this->default_locale) {
					$this->loadLocale($this->default_locale);
				} elseif ($this->debug) {
					throw new RuntimeException("Locale file not found: $path");
				}

				return;
			}

			$content = file_get_contents($path);

			if ($content === false || trim($content) === '') {
				if ($this->debug) {
					throw new RuntimeException("Failed to read or empty locale file: $path");
				}

				return;
			}

			try {
				$translations = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
			} catch (JsonException $exception) {
				if ($this->debug) {
					throw new JsonException("Invalid JSON in locale file: $path", 0, $exception);
				}

				return;
			}

			if (!is_array($translations) || !isset($translations['variables']) || !is_array($translations['variables'])) {
				if ($this->debug) {
					throw new RuntimeException("Missing or invalid 'variables' key in locale file: $path");
				}

				return;
			}

			// Sanitize and prepare variables.
			$variables = [];

			foreach ($translations['variables'] as $var_key => $var_value) {
				if (is_string($var_key)) {
					$variables[$var_key] = is_scalar($var_value) ? (string)$var_value : '';
				}
			}

			// Replace variables in all string translations.
			foreach ($translations as $key => $value) {
				if (is_string($value)) {
					try {
						$translations[$key] = $this->replaceVariables($value, $variables);
					} catch (RuntimeException $exception) {
						if ($this->debug) {
							throw new RuntimeException("Error replacing variable '$key' in locale file: $path", 0, $exception);
						}
						continue;
					}
				}
			}

			// Store only string-to-string pairs.
			$this->loaded_locales[$locale] = array_filter(
				$translations,
				fn($var_value, $var_key) => is_string($var_key) && is_string($var_value),
				ARRAY_FILTER_USE_BOTH
			);
		}

		/**
		 * Unload a loaded locale file from memory.
		 *
		 * @param string $locale    - The locale code to unload (e.g. 'en', 'fr').
		 *
		 * @return void
		 *
		 * @throws RuntimeException - If the locale file is not currently loaded or has been already unloaded.
		 */
		public function unloadFile(string $locale): void {
			if (!isset($this->loaded_locales[$locale]) && $this->debug) {
				throw new RuntimeException("Locale file $locale is not currently loaded or has been already unloaded");
			}

			unset($this->loaded_locales[$locale]);
		}

		/**
		 * Formats a number according to the current locale.
		 *
		 * @param float $number             - The number to format
		 * @param int $max_fraction_digits  - the maximum number of fraction digits (default: 2)
		 *
		 * @return string                   - The formatted number
		 *
		 * @throws InvalidArgumentException - Thrown if the current locale is not set
		 * @throws RuntimeException         - Thrown if number formatting fails
		 */
		public function formatNumber(float $number, int $max_fraction_digits = 2): string {
			if (!$this->current_locale) {
				throw new InvalidArgumentException('Current locale not set.');
			}

			$formatter = new NumberFormatter($this->current_locale, NumberFormatter::DECIMAL);
			$formatter->setAttribute(NumberFormatter::MAX_FRACTION_DIGITS, $max_fraction_digits);

			$formatted = $formatter->format($number);

			if ($formatted === false) {
				throw new RuntimeException('Number formatting failed: ' . intl_get_error_message());
			}

			return $formatted;
		}

		/**
		 * Formats a currency amount according to the current locale.
		 *
		 * @param float  $amount            - The amount to format
		 * @param string $currency          - The iso 4217 currency code (e.g. "usd", "eur")
		 * @param bool   $show_symbol       - Whether to show the currency symbol (default: true)
		 *
		 * @return string                   - The formatted currency amount
		 *
		 * @throws InvalidArgumentException - Thrown if the locale is not set or the currency code is invalid
		 * @throws RuntimeException         - Thrown if currency formatting fails
		 */
		public function formatCurrency(float $amount, string $currency, bool $show_symbol = true): string {
			if (!$this->current_locale) {
				throw new InvalidArgumentException('Current locale not set.');
			}

			if (!preg_match('/^[A-Z]{3}$/', $currency)) {
				throw new InvalidArgumentException('Invalid ISO 4217 currency code.');
			}

			$formatter = new NumberFormatter($this->current_locale, NumberFormatter::CURRENCY);
			$formatted = $formatter->formatCurrency($amount, strtoupper($currency));

			if ($formatted === false) {
				throw new RuntimeException('Currency formatting failed: ' . intl_get_error_message());
			}

			if (!$show_symbol) {
				$symbol = $formatter->getSymbol(NumberFormatter::CURRENCY_SYMBOL);
				$formatted = str_replace($symbol, $currency, $formatted);
			}

			return $formatted;
		}

		/**
		 * Formats a date according to the current locale.
		 *
		 * @param DateTime $date            - The date to format
		 * @param string   $format          - One of 'short', 'medium', 'long', or 'full' (default: 'long')
		 *
		 * @return string                   - The formatted date
		 *
		 * @throws InvalidArgumentException - Thrown if the current locale is not set
		 * @throws RuntimeException         - Thrown if date formatting fails
		 */
		public function formatDate(DateTime $date, string $format = 'long'): string {
			if (!$this->current_locale) {
				throw new InvalidArgumentException('Current locale not set.');
			}

			$style = match (strtolower($format)) {
				'short' => IntlDateFormatter::SHORT,
				'medium' => IntlDateFormatter::MEDIUM,
				'full' => IntlDateFormatter::FULL,
				default => IntlDateFormatter::LONG,
			};

			$formatter = new IntlDateFormatter($this->current_locale, $style, IntlDateFormatter::NONE);
			$formatted = $formatter->format($date);

			if ($formatted === false) {
				throw new RuntimeException('Date formatting failed: ' . intl_get_error_message());
			}

			return $formatted;
		}

		/**
		 * Returns locale-specific numeric and monetary formatting rules.
		 *
		 * @return array<string|false|int|float> - Associative array of formatting rules from localeconv()
		 *
		 * @throws InvalidArgumentException      - Thrown if the current locale is not set
		 * @throws RuntimeException              - Thrown if the locale cannot be applied (in debug mode)
		 */
		public function getFormattingRules(): array {
			if (!$this->current_locale) {
				throw new InvalidArgumentException('Locale not set.');
			}

			if (!setlocale(LC_ALL, $this->current_locale)) {
				if ($this->debug) {
					throw new RuntimeException("Unable to apply locale: {$this->current_locale}");
				}

				return [];
			}

			return array_filter(localeconv(), fn($value) => is_string($value) || is_int($value) || is_float($value) || $value === false);
		}
	}
