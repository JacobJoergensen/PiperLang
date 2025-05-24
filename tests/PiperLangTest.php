<?php
    namespace Tests;

    use PHPUnit\Framework\TestCase;

    use PiperLang\PiperLang;

    use DateTime;
    use InvalidArgumentException;
    use RuntimeException;

    class PiperLangTest extends TestCase {
        private PiperLang $piper_lang;

        protected function setUp(): void {
            $this -> piper_lang = new PiperLang();

            $this -> piper_lang -> current_locale = null;
            $this -> piper_lang -> default_locale = 'en';
            $this -> piper_lang -> supported_locales = ['en', 'es', 'de'];
            $this -> piper_lang -> locale_path = 'locales/';
            $this -> piper_lang -> variable_pattern = '/{{(.*?)}}/';
            $this -> piper_lang -> session_enabled = true;
            $this -> piper_lang -> session_key = 'current_locale';
        }

        public function testGetHttpAcceptLanguage(): void {
            // TEST: SIMULATE AND TEST THE HTTP_ACCEPT_LANGUAGE DETECTION.
            $original = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9';

            if ($original !== null) {
                $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $original;
            } else {
                unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            }
        }

        public function testGetInfo(): void {
            $info = $this -> piper_lang -> getInfo();

            $this -> assertIsArray($info);

            $expected_keys = [
                'Debug Status', 'Current Locale', 'Default Locale',
                'Supported Locales', 'Path to Locales', 'Locale File Extension',
                'Loaded Locales', 'Variable Pattern',
                'Session Enabled', 'Session Key',
                'Cookie Enabled', 'Cookie Key'
            ];

            // TEST: ENSURE THAT ALL EXPECTED KEYS ARE PRESENT.
            foreach ($expected_keys as $key) {
                $this -> assertArrayHasKey($key, $info);
            }

            // TEST: ENSURE THAT ALL VALUES ARE OF THE EXPECTED TYPE.
            $this -> assertIsBool($info['Debug Status']);
            $this -> assertTrue(is_string($info['Current Locale']) || is_null($info['Current Locale']));
            $this -> assertIsString($info['Default Locale']);
            $this -> assertIsArray($info['Supported Locales']);
            $this -> assertIsString($info['Path to Locales']);
            $this -> assertIsString($info['Locale File Extension']);
            $this -> assertIsArray($info['Loaded Locales']);
            $this -> assertIsString($info['Variable Pattern']);
            $this -> assertIsBool($info['Session Enabled']);
            $this -> assertIsString($info['Session Key']);
            $this -> assertIsBool($info['Cookie Enabled']);
            $this -> assertIsString($info['Cookie Key']);
        }

        public function testDetectBrowserLocale(): void {
            // TEST: SIMULATE DETECTION OF BROWSER LOCALE.
            $original = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;

            $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9,es-ES;q=0.8,fr-FR;q=0.7';
            $this -> assertEquals('en', $this -> piper_lang -> detectBrowserLocale());

            if ($original !== null) {
                $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $original;
            } else {
                unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            }
        }

        public function testDetectUserLocale(): void {
            // TEST: ENSURE detectUserLocale RETURNS DEFAULT LOCALE WHEN NO SESSION OR COOKIE IS SET.
            $this -> piper_lang -> default_locale = 'en';
            $this -> piper_lang -> supported_locales = ['en', 'fr', 'de'];
            $result = $this -> piper_lang -> detectUserLocale();
            $this -> assertEquals('en', $result, "Expected default locale when no session or cookie is set");

            // TEST: ENSURE detectUserLocale RETURNS SESSION LOCALE WHEN SESSION IS SET.
            $this -> piper_lang -> session_enabled = true;
            $_SESSION[$this -> piper_lang -> session_key] = 'fr';
            $result = $this -> piper_lang -> detectUserLocale();
            $this -> assertEquals('fr', $result, "Expected locale from session when session is set");

            // TEST: ENSURE detectUserLocale RETURNS COOKIE LOCALE WHEN COOKIE IS SET.
            $this -> piper_lang -> cookie_enabled = true;
            unset($_SESSION[$this -> piper_lang -> session_key]);
            $_COOKIE[$this -> piper_lang -> cookie_key] = 'de';
            $result = $this -> piper_lang -> detectUserLocale('cookie');
            $this -> assertEquals('de', $result, "Expected locale from cookie when cookie is set");

            // TEST: ENSURE FALLBACK TO DEFAULT LOCALE WHEN UNSUPPORTED LOCALE IS SET IN COOKIE.
            $_COOKIE[$this -> piper_lang -> cookie_key] = 'es';
            $result = $this -> piper_lang -> detectUserLocale('cookie');
            $this -> assertEquals('en', $result, "Expected default locale when unsupported locale is set in cookie");

            // TEST: THROW EXCEPTION FOR INVALID SOURCE IN detectUserLocale.
            $this -> piper_lang -> debug = true;
            $this -> expectException(InvalidArgumentException::class);
            $this -> expectExceptionMessage("Invalid or disabled source 'invalidSource' for detecting locale.");
            $this -> piper_lang -> detectUserLocale('invalidSource');
        }

        public function testGetLocale(): void {
            // TEST: ENSURE getLocale RETURNS CURRENT LOCALE.
            $this -> piper_lang -> current_locale = 'es';
            $this -> assertEquals('es', $this -> piper_lang -> getLocale());

            // TEST: ENSURE FALLBACK TO DEFAULT LOCALE WHEN CURRENT LOCALE IS NULL.
            $this -> piper_lang -> current_locale = null;
            $this -> assertEquals('en', $this -> piper_lang -> getLocale(), 'Expected default locale "en" when current_locale is null');
        }

        public function testSetLocale(): void {
            // TEST: SET A PREFERRED LOCALE THAT IS IN THE SUPPORTED LOCALES.
            $this -> piper_lang -> setLocale('es');
            $this -> assertEquals('es', $this -> piper_lang -> getLocale(), 'Expected locale to be set to "es"');

            // TEST: SET A PREFERRED LOCALE THAT IS NOT A PART OF THE SUPPORTED LOCALES, SHOULD USE DEFAULT LOCALE.
            $this -> piper_lang -> setLocale('fr');
            $this -> assertEquals('en', $this -> piper_lang -> getLocale(), 'Expected locale to fall back to default "en"');

            // TEST: ENSURE THAT DEFAULT LOCALE IS ADDED TO SUPPORTED LOCALES IF IT'S MISSING.
            $this -> piper_lang -> supported_locales = ['es'];
            $this -> piper_lang -> setLocale('es');
            $this -> assertContains('en', $this -> piper_lang -> supported_locales, 'Expected default locale "en" to be added to supported locales');

            // TEST: SESSION BEHAVIOR WHEN SESSION IS ENABLED.
            $this -> piper_lang -> session_enabled = true;
            $_SESSION = [];

            $this -> piper_lang -> setLocale('es');
            $this -> assertEquals('es', $_SESSION[$this -> piper_lang -> session_key], 'Expected session locale to be set to "es"');

            // TEST: BEHAVIOR WHEN SESSION IS DISABLED.
            $this -> piper_lang -> session_enabled = false;
            $_SESSION = [];

            $this -> piper_lang -> setLocale('es');
            $this -> assertArrayNotHasKey($this -> piper_lang -> session_key, $_SESSION, 'Expected no session locale to be set when session is disabled');

            // TEST: COOKIE BEHAVIOR WHEN COOKIES ARE ENABLED.
            $this -> piper_lang -> cookie_enabled = true;

            ob_start();
            $this -> piper_lang -> setLocale('es');
            ob_end_clean();

            $this -> assertTrue(isset($_COOKIE[$this -> piper_lang -> cookie_key]), 'Expected cookie to be set');
            $this -> assertEquals('es', $_COOKIE[$this -> piper_lang -> cookie_key], 'Expected cookie value to be set to "es"');
        }

        public function testSetLocalePath(): void {
            // TEST: ENSURE VALID LOCALE PATH IS SET CORRECTLY.
            $valid_path = __DIR__;
            $this -> piper_lang -> setLocalePath($valid_path);
            $this -> assertEquals($valid_path, $this -> piper_lang -> locale_path);

            // TEST: THROW EXCEPTION FOR INVALID LOCALE PATH WHEN IN DEBUG MODE.
            $invalid_path = '/path/to/non/existent/directory';
            $this -> piper_lang -> debug = true;
            $this -> expectException(RuntimeException::class);
            $this -> piper_lang -> setLocalePath($invalid_path);
        }

        public function testReplaceVariables(): void {
            // TEST: REPLACING VARIABLES IN STRING WHERE THE VARIABLE EXISTS.
            $string = 'Hello, world!';
            $variables = ['name' => 'Bob'];
            $result = $this -> piper_lang -> replaceVariables($string, $variables);
            $this -> assertEquals('Hello, world!', $result);

            // TEST: REPLACING STRING WITH MISSING VARIABLE.
            $string = '{{missing}}, world!';
            $result = $this -> piper_lang -> replaceVariables($string, $variables);
            $this -> assertEquals('{{missing}}, world!', $result);

            // TEST: NO REPLACEMENT IF VARIABLE PATTERN IS NULL.
            $this -> piper_lang -> variable_pattern = null;
            $string = 'Hello, {{name}}!';
            $result = $this -> piper_lang -> replaceVariables($string, $variables);
            $this -> assertEquals('Hello, {{name}}!', $result);
        }

        public function testLoadFile(): void {
            // TEST: LOADING A VALID LOCALE FILE.
            $locale = $this -> piper_lang -> default_locale;
            $this -> piper_lang -> locale_path = 'locales/';
            $this -> piper_lang -> locale_file_extension = 'json';
            $this -> assertIsArray($this -> piper_lang -> loadFile($locale));

            // TEST: THROW EXCEPTION FOR NON-EXISTENT LOCALE FILE.
            $locale = 'non_existant_locale';
            $this -> piper_lang -> default_locale = 'default_locale';
            $this -> expectException(RuntimeException::class);
            $this -> piper_lang -> loadFile($locale);
        }

        public function testUnloadFile(): void {
            // TEST: UNLOAD A LOADED LOCALE FILE.
            $locale = $this -> piper_lang -> default_locale;
            $this -> piper_lang -> loadFile($locale);
            $this-> assertArrayHasKey($locale, $this-> piper_lang -> loaded_locales);
            $this -> piper_lang -> unloadFile($locale);
            $this-> assertArrayNotHasKey($locale, $this-> piper_lang -> loaded_locales);

            // TEST: THROW EXCEPTION FOR UNLOADING A NON-EXISTENT LOCALE IN DEBUG MODE.
            $this -> piper_lang -> debug = true;
            $this -> expectException(RuntimeException::class);
            $this -> piper_lang -> unloadFile($locale);
        }

        public function testFormatNumber(): void {
            // TEST: FORMAT NUMBER IN DIFFERENT LOCALES.
            $this -> piper_lang -> current_locale = 'en';
            $this -> assertEquals('1,234.57', $this -> piper_lang -> formatNumber(1234.56789));
            $this -> piper_lang -> current_locale = 'de';
            $this -> assertEquals('1.234,57', $this -> piper_lang -> formatNumber(1234.56789));

            // TEST: THROW EXCEPTION WHEN NO LOCALE IS SET.
            $this -> piper_lang -> current_locale = null;
            $this -> expectException(InvalidArgumentException::class);
            $this -> piper_lang -> formatNumber(1234.56789);

            // TEST: THROW EXCEPTION FOR INVALID NUMBER IN formatNumber.
            $this -> expectException(InvalidArgumentException::class);
            $this -> expectExceptionMessage("Not a valid number for formatting.");
            $this -> piper_lang -> formatNumber('non-numeric');
        }

        public function testFormatCurrency(): void {
            // TEST: FORMAT CURRENCY IN DIFFERENT LOCALES.
            $this -> piper_lang -> current_locale = 'en';
            $this -> assertEquals('$123.46', $this -> piper_lang -> formatCurrency(123.456, 'USD', true));

            // TEST: THROW EXCEPTION WHEN NO LOCALE IS SET FOR CURRENCY FORMATTING.
            $this -> piper_lang -> current_locale = null;
            $this -> expectException(InvalidArgumentException::class);
            $this -> piper_lang -> formatCurrency(123.456, 'USD', true);

            // TEST: THROW EXCEPTION FOR INVALID AMOUNT IN formatCurrency.
            $this -> expectException(null);
            $this -> expectException(InvalidArgumentException::class);
            $this -> expectExceptionMessage("Not a valid amount for currency formatting.");
            $this -> piper_lang -> formatCurrency('non-numeric', 'USD', true);

            // TEST: THROW EXCEPTION FOR INVALID ISO CURRENCY CODE IN formatCurrency.
            $this -> expectException(null);
            $this -> expectException(InvalidArgumentException::class);
            $this -> expectExceptionMessage('Not a valid ISO 4217 currency code.');
            $this -> piper_lang -> formatCurrency(123.456, 'INVALID', true);
        }

        public function testFormatDate(): void {
            // TEST: FORMAT DATE IN DIFFERENT LOCALES.
            $this -> piper_lang -> current_locale = 'en';
            $test_date = new DateTime('2023-01-01');
            $this -> assertEquals('January 1, 2023', $this -> piper_lang -> formatDate($test_date, 'long'));

            $this -> piper_lang -> current_locale = 'de';
            $test_date = new DateTime('2023-01-01');
            $this -> assertEquals('1. Januar 2023', $this -> piper_lang -> formatDate($test_date, 'long'));
        }

        public function testGetFormattingRules(): void {
            // TEST: ENSURE getFormattingRules RETURNS VALID DATA FOR THE CURRENT LOCALE.
            $this -> piper_lang -> current_locale = 'en';
            $this -> assertIsArray($this -> piper_lang -> getFormattingRules());

            // TEST: THROW EXCEPTION WHEN NO VALID LOCALE IS SET.
            $this -> piper_lang -> current_locale = null;
            $this -> piper_lang -> debug = true;
            $this -> expectException(InvalidArgumentException::class);
            $this -> piper_lang -> getFormattingRules();
        }
    }
