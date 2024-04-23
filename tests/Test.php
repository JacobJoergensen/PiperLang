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
            $this -> piper_lang -> variable_pattern = '/{{(.*?)}}/';
            $this -> piper_lang -> session_enabled = true;
            $this -> piper_lang -> session_key = 'current_locale';
        }

        public function testDetectBrowserLocale(): void {
            $this -> piper_lang -> http_accept_locale = 'en-US,en;q=0.9,es-ES;q=0.8,fr-FR;q=0.7';
            $this -> assertEquals('en', $this -> piper_lang -> detectBrowserLocale());

            $this -> piper_lang -> http_accept_locale = 'es-ES,es;q=0.9,en-US;q=0.8,fr-FR;q=0.7';
            $this -> assertEquals('es', $this -> piper_lang -> detectBrowserLocale());

            $this -> piper_lang -> http_accept_locale = 'fr-FR,fr;q=0.9,de-DE;q=0.8,en-US;q=0.7';
            $this -> assertEquals('de', $this -> piper_lang -> detectBrowserLocale());

            $this -> piper_lang -> http_accept_locale = 'fr-FR,fr;q=0.9,it-IT;q=0.8,ja-JP;q=0.7';
            $this -> assertEquals('en', $this -> piper_lang -> detectBrowserLocale());
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

            $this -> piper_lang -> setLocale('fr_FR');
            $this -> assertEquals('en', $this -> piper_lang -> current_locale);
            $this -> assertEquals('en', $_SESSION['current_locale']);
        }

        public function testSwitchLocale(): void {
            $_SESSION = [];

            $this -> piper_lang -> switchLocale('es');
            $this -> assertEquals('es', $this -> piper_lang -> current_locale);
            $this -> assertEquals('es', $_SESSION['current_locale']);
        }

        public function testSetLocalePath(): void {
            $valid_path = __DIR__;
            $this -> piper_lang -> setLocalePath($valid_path);
            $this -> assertEquals($valid_path, $this -> piper_lang -> locale_path);

            $invalid_path = '/path/to/non/existent/directory';
            $this -> expectException(RuntimeException::class);
            $this -> piper_lang -> setLocalePath($invalid_path);
        }

        public function testReplaceVariables(): void {
            $string = 'Hello, world!';
            $variables = ['name' => 'Bob'];
            $result = $this->piper_lang->replaceVariables($string, $variables);
            $this->assertEquals('Hello, world!', $result);

            $string = 'Hello, {{name}}!';
            $result = $this->piper_lang->replaceVariables($string, $variables);
            $this->assertEquals('Hello, Bob!', $result);

            $string = '{{greeting}}, {{name}}!';
            $variables = ['greeting' => 'Hello', 'name' => 'Bob'];
            $result = $this->piper_lang->replaceVariables($string, $variables);
            $this->assertEquals('Hello, Bob!', $result);

            $string = '{{greeting}}, {{greeting}}!';
            $result = $this->piper_lang->replaceVariables($string, $variables);
            $this->assertEquals('Hello, Hello!', $result);

            $string = '{{missing}}, world!';
            $result = $this->piper_lang->replaceVariables($string, $variables);
            $this->assertEquals('{{missing}}, world!', $result);
        }

        public function testNumberFormat(): void {
            $this -> piper_lang -> current_locale = 'en';
            $this -> assertEquals('1,234.57', $this -> piper_lang -> numberFormat(1234.56789));

            $this -> piper_lang -> current_locale = 'de';
            $this -> assertEquals('1.234,57', $this -> piper_lang -> numberFormat(1234.56789));

            $this -> piper_lang -> current_locale = null;
            $this -> expectException(InvalidArgumentException::class);
            $this -> piper_lang -> numberFormat(1234.56789);

            $this -> expectException(InvalidArgumentException::class);
            $this -> piper_lang -> numberFormat('string number');
        }

        public function testCurrencyFormat(): void {
            $this -> piper_lang -> current_locale = 'en';
            $this -> assertEquals('$123.46', $this -> piper_lang -> currencyFormat(123.456, 'USD', true));

            $this -> piper_lang -> current_locale = 'de';
            $this -> assertEquals('123,46 €', $this -> piper_lang -> currencyFormat(123.456, 'EUR', true));

            $this -> piper_lang -> current_locale = null;
            $this -> expectException(InvalidArgumentException::class);
            $this -> piper_lang -> currencyFormat(123.456, 'USD', true);

            $this -> expectException(InvalidArgumentException::class);
            $this -> piper_lang -> currencyFormat('string currency', 'USD', true);
        }

        public function testDateFormat(): void {
            $this -> piper_lang -> current_locale = 'en';
            $test_date = new DateTime('2023-01-01');
            $this -> assertEquals('January 1, 2023', $this -> piper_lang -> dateFormat($test_date, 'long'));

            $this -> piper_lang -> current_locale = 'de';
            $test_date = new DateTime('2023-01-01');
            $this -> assertEquals('1. Januar 2023', $this -> piper_lang -> dateFormat($test_date, 'long'));
        }

        public function testGetFormattingRules(): void {
            $this -> piper_lang -> current_locale = 'en';
            $this -> assertIsArray($this -> piper_lang -> getFormattingRules());

            $this -> piper_lang -> current_locale = null;
            $this -> expectException(InvalidArgumentException::class);
            $this -> piper_lang -> getFormattingRules();
        }
    }
