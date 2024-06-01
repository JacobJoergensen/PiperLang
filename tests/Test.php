<?php declare(strict_types=1);
    namespace Tests;

    use PHPUnit\Framework\TestCase;
    use PiperLang\PiperLang;

    use DateTime;
    use InvalidArgumentException;
    use RuntimeException;

    final class Test extends TestCase {
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

        public function testAddHook(): void {
            $hook_name = 'some_hook_name';

            $some_hook_function = function($arg1, $arg2) {
                echo $arg1 . $arg2;
            };

            $this -> piper_lang -> addHook($hook_name, $some_hook_function);

            $this -> assertIsCallable(
                $this -> piper_lang -> hooks[$hook_name][10][0], "The hook function is missing or not callable"
            );

            $different_priority_hook_function = function($arg1) {
                echo strtoupper($arg1);
            };

            $this -> piper_lang -> addHook($hook_name, $different_priority_hook_function, 1);

            $this -> assertIsCallable(
                $this -> piper_lang -> hooks[$hook_name][1][0], "The priority hook function is missing or not callable"
            );
        }

        public function testRunHooks(): void {
            $hook_name = 'some_hook_name';

            $some_hook_function = function($arg1, $arg2) {
                echo $arg1 . $arg2;
            };

            $this -> piper_lang -> addHook($hook_name, $some_hook_function, 10);

            ob_start();

            $this -> piper_lang -> runHooks($hook_name, ['Hello ', 'world!']);
            $output = ob_get_clean();
            $this -> assertEquals('Hello world!', $output, "The hooks didn't produce expected output");

            $different_priority_hook_function = function($arg1) {
                echo strtoupper($arg1);
            };

            $this -> piper_lang -> addHook($hook_name, $different_priority_hook_function, 1);

            ob_start();

            $this -> piper_lang -> runHooks($hook_name, ['Hello ', '']);
            $output = ob_get_clean();
            $this -> assertEquals('HELLO Hello ', $output, "The hooks with different priorities didn't run in the correct order");

            ob_start();

            $this -> piper_lang -> runHooks('non_existent_hook');
            $output = ob_get_clean();
            $this -> assertEmpty($output, "Running a non-existent hook produced output.");
        }

        public function testGetHttpAcceptLanguage(): void {
            $original = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9';
    
            $piper_lang = new PiperLang();
    
            $this -> assertEquals($piper_lang -> getHttpAcceptLanguage(), "The getHttpAcceptLanguage method didn't return the correct value");
    
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
                'Debug Status', 'Hooks List', 'Current Locale', 'Default Locale',
                'Supported Locales', 'Path to Locales', 'Locale File Extension',
                'Loaded Locales', 'Variable Pattern', 'Plural Rules',
                'Session Enabled', 'Session Key',
                'Cookie Enabled', 'Cookie Key'
            ];

            foreach ($expected_keys as $key) {
                $this -> assertArrayHasKey($key, $info);
            }

            $this -> assertIsBool($info['Debug Status']);
            $this -> assertIsArray($info['Hooks List']);
            $this -> assertTrue(is_string($info['Current Locale']) || is_null($info['Current Locale']));
            $this -> assertIsString($info['Default Locale']);
            $this -> assertIsArray($info['Supported Locales']);
            $this -> assertIsString($info['Path to Locales']);
            $this -> assertIsString($info['Locale File Extension']);
            $this -> assertIsArray($info['Loaded Locales']);
            $this -> assertIsString($info['Variable Pattern']);
            $this -> assertIsArray($info['Plural Rules']);
            $this -> assertIsString($this -> piper_lang -> getHttpAcceptLanguage());
            $this -> assertIsBool($info['Session Enabled']);
            $this -> assertIsString($info['Session Key']);
            $this -> assertIsBool($info['Cookie Enabled']);
            $this -> assertIsString($info['Cookie Key']);
        }

        public function testDetectBrowserLocale(): void {
            $original = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
            $http_accept_language = $this -> piper_lang -> getHttpAcceptLanguage();

            $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9,es-ES;q=0.8,fr-FR;q=0.7';
            $this -> assertEquals('en', $this -> piper_lang -> detectBrowserLocale());

            $this -> assertEquals($http_accept_language, $this -> piper_lang -> detectBrowserLocale());

            if ($original !== null) {
                $_SERVER['HTTP_ACCEPT_LANGUAGE'] = $original;
            } else {
                unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
            }
        }

        public function testDetectUserLocale(): void {
            $this -> piper_lang -> default_locale = 'en';
            $this -> piper_lang -> supported_locales = ['en', 'fr', 'de'];

            $result = $this -> piper_lang -> detectUserLocale();
            $this -> assertEquals('en', $result, "Expected default locale when no session or cookie is set");

            $this -> piper_lang -> session_enabled = true;
            $_SESSION[$this -> piper_lang -> session_key] = 'fr';
            $result = $this -> piper_lang -> detectUserLocale();
            $this -> assertEquals('fr', $result, "Expected locale from session when session is set");

            $this -> piper_lang -> cookie_enabled = true;
            unset($_SESSION[$this -> piper_lang -> session_key]);
            $_COOKIE[$this -> piper_lang -> cookie_key] = 'de';
            $result = $this -> piper_lang -> detectUserLocale('cookie');
            $this -> assertEquals('de', $result, "Expected locale from cookie when cookie is set");

            $_COOKIE[$this -> piper_lang -> cookie_key] = 'es';
            $result = $this -> piper_lang -> detectUserLocale('cookie');
            $this -> assertEquals('en', $result, "Expected default locale when unsupported locale is set in cookie");
        }

        public function testGetLocale(): void {
            $this -> assertNull($this -> piper_lang -> getLocale());

            $this -> piper_lang -> current_locale = 'en';
            $this -> assertEquals('en', $this -> piper_lang -> getLocale());

            $this -> piper_lang -> current_locale = 'es';
            $this -> assertEquals('es', $this -> piper_lang -> getLocale());

            $this -> piper_lang -> current_locale = null;
            $this -> assertNull($this -> piper_lang -> getLocale());
        }

        public function testSetLocale(): void {
            $_SESSION = [];

            $this -> piper_lang -> setLocale('es');
            $this -> assertEquals('es', $this -> piper_lang -> current_locale);
            $this -> assertEquals('es', $_SESSION['current_locale']);

            $_SESSION = [];

            $this -> piper_lang -> setLocale('fr');
            $this -> assertEquals('en', $this -> piper_lang -> current_locale);
            $this -> assertEquals('en', $_SESSION['current_locale']);

            $this -> piper_lang -> default_locale = 'de';
            $this -> piper_lang -> setLocale();
            $this -> assertContains('de', $this -> piper_lang -> supported_locales);
        }

        public function testSetLocalePath(): void {
            $valid_path = __DIR__;
            $this -> piper_lang -> setLocalePath($valid_path);
            $this -> assertEquals($valid_path, $this -> piper_lang -> locale_path);

            $invalid_path = '/path/to/non/existent/directory';
            $this -> piper_lang -> debug = true;
            $this -> expectException(RuntimeException::class);
            $this -> piper_lang -> setLocalePath($invalid_path);
        }

        public function testSwitchLocale(): void {
            $_SESSION = [];

            $this -> piper_lang -> switchLocale('es');
            $this -> assertEquals('es', $this -> piper_lang -> current_locale);
            $this -> assertEquals('es', $_SESSION['current_locale']);
        }

        public function testReplaceVariables(): void {
            $string = 'Hello, world!';
            $variables = ['name' => 'Bob'];
            $result = $this -> piper_lang -> replaceVariables($string, $variables);
            $this -> assertEquals('Hello, world!', $result);

            $string = 'Hello, {{name}}!';
            $result = $this -> piper_lang -> replaceVariables($string, $variables);
            $this -> assertEquals('Hello, Bob!', $result);

            $string = '{{greeting}}, {{name}}!';
            $variables = ['greeting' => 'Hello', 'name' => 'Bob'];
            $result = $this -> piper_lang -> replaceVariables($string, $variables);
            $this -> assertEquals('Hello, Bob!', $result);

            $string = '{{greeting}}, {{greeting}}!';
            $result = $this -> piper_lang -> replaceVariables($string, $variables);
            $this -> assertEquals('Hello, Hello!', $result);

            $string = '{{missing}}, world!';
            $result = $this -> piper_lang -> replaceVariables($string, $variables);
            $this -> assertEquals('{{missing}}, world!', $result);

            $this -> piper_lang -> variable_pattern = null;
            $string = 'Hello, {{name}}!';
            $variables = ['name' => 'Bob'];
            $result = $this -> piper_lang -> replaceVariables($string, $variables);
            $this -> assertEquals('Hello, {{name}}!', $result);
        }

        public function testTranslateWithPlural(): void {
            $key = 'message';
            $count = 1;
            $variables = ['name' => 'John'];

            $this -> piper_lang -> current_locale = 'en';
            $this -> piper_lang -> default_locale = 'en';

            $this -> piper_lang -> plural_rules['en'] = '_1';

            $this -> assertIsString($this -> piper_lang -> translateWithPlural($key, $count, $variables));

            unset($this -> piper_lang -> plural_rules['en']);

            $this -> assertIsString($this -> piper_lang -> translateWithPlural($key, $count, $variables));

            $count = 2;
            $this -> assertIsString($this -> piper_lang -> translateWithPlural($key, $count));

            $this -> piper_lang -> current_locale = 'non_existant_locale';
            $this -> assertIsString($this -> piper_lang -> translateWithPlural($key, $count));

            $this -> piper_lang -> default_locale = 'non_existant_locale';
            $this -> piper_lang -> debug = true;
            $this -> expectException(RuntimeException::class);
            $this -> assertIsString($this -> piper_lang -> translateWithPlural($key, $count));
        }

        public function testLoadFile(): void {
            $locale = $this -> piper_lang -> default_locale;

            $this -> piper_lang -> locale_path = 'locales/';
            $this -> piper_lang -> locale_file_extension = 'json';

            $this -> assertIsArray($this -> piper_lang -> loadFile($locale));

            $locale = 'non_existant_locale';
            $this -> piper_lang -> default_locale = 'default_locale';
            $this -> expectException(RuntimeException::class);
            $this -> piper_lang -> loadFile($locale);
        }  

        public function testUnloadFile(): void {
            $locale = $this -> piper_lang -> default_locale;

            $this -> piper_lang -> loadFile($locale);
            $this-> assertArrayHasKey($locale, $this-> piper_lang -> loaded_locales);

            $this -> piper_lang -> unloadFile($locale);
            $this-> assertArrayNotHasKey($locale, $this-> piper_lang -> loaded_locales);

            $this -> piper_lang -> debug = true;
            $this -> expectException(RuntimeException::class);
            $this -> piper_lang -> unloadFile($locale);
        }

        public function testFormatNumber(): void {
            $this -> piper_lang -> current_locale = 'en';
            $this -> assertEquals('1,234.57', $this -> piper_lang -> formatNumber(1234.56789));

            $this -> piper_lang -> current_locale = 'de';
            $this -> assertEquals('1.234,57', $this -> piper_lang -> formatNumber(1234.56789));

            $this -> piper_lang -> current_locale = null;
            $this -> expectException(InvalidArgumentException::class);
            $this -> piper_lang -> formatNumber(1234.56789);

            $this -> piper_lang -> current_locale = 'en';
            $this -> expectException(InvalidArgumentException::class);
            $this -> piper_lang -> formatNumber(NAN);
        }

        public function testFormatCurrency(): void {
            $this -> piper_lang -> current_locale = 'en';
            $this -> assertEquals('$123.46', $this -> piper_lang -> formatCurrency(123.456, 'USD', true));

            $this -> piper_lang -> current_locale = null;
            $this -> expectException(InvalidArgumentException::class);
            $this -> piper_lang -> formatCurrency(123.456, 'USD', true);

            $this -> expectException(InvalidArgumentException::class);
            $this -> piper_lang -> formatCurrency('string currency', 'USD', true);
        }

        public function testFormatDate(): void {
            $this -> piper_lang -> current_locale = 'en';
            $test_date = new DateTime('2023-01-01');
            $this -> assertEquals('January 1, 2023', $this -> piper_lang -> formatDate($test_date, 'long'));

            $this -> piper_lang -> current_locale = 'de';
            $test_date = new DateTime('2023-01-01');
            $this -> assertEquals('1. Januar 2023', $this -> piper_lang -> formatDate($test_date, 'long'));
        }

        public function testGetFormattingRules(): void {
            $this -> piper_lang -> current_locale = 'en';
            $this -> assertIsArray($this -> piper_lang -> getFormattingRules());

            $this -> piper_lang -> current_locale = null;
            $this -> piper_lang -> debug = true;
            $this -> expectException(InvalidArgumentException::class);
            $this -> piper_lang -> getFormattingRules();
        }

        public function testInvalidArgumentCases(): void {
            $this -> piper_lang -> default_locale = 'en';
            $this -> piper_lang -> supported_locales = ['en', 'fr', 'de'];
            $this -> piper_lang -> debug = true;

            $this -> expectException(InvalidArgumentException::class);
            $this -> expectExceptionMessage("Invalid or disabled source 'invalidSource' for detecting locale.");
            $this -> piper_lang -> detectUserLocale('invalidSource');
            $this -> expectException(null);

            $this -> expectException(InvalidArgumentException::class);
            $this -> expectExceptionMessage("Not a valid number for formatting.");
            $this -> piper_lang -> formatNumber('non-numeric');

            $this -> expectException(null);
            $this -> expectException(InvalidArgumentException::class);
            $this -> expectExceptionMessage("Not a valid amount for currency formatting.");
            $this -> piper_lang -> formatCurrency('non-numeric', 'USD', true);

            $this -> expectException(null);
            $this -> expectException(InvalidArgumentException::class);
            $this -> expectExceptionMessage('Not a valid ISO 4217 currency code.');
            $this -> piper_lang -> formatCurrency(123.456, 'INVALID', true);
        }
    }
