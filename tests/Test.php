<?php declare(strict_types=1);
    namespace Tests;

    use PHPUnit\Framework\TestCase;
    use PiperLang\PiperLang;
    use JsonException;
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
            $result = $this -> piper_lang->replaceVariables($string, $variables);
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
        }

        /**
         * @throws JsonException
         */
        public function testTranslateWithPlural(): void {
            $translations = [
                'en' => [
                    'key_1' => 'You have {{count}} new message',
                    'key_other' => 'You have {{count}} new messages'
                ],
                'es' => [
                    'key_1' => 'Tienes {{count}} nuevo mensaje',
                    'key_other' => 'Tienes {{count}} nuevos mensajes'
                ]
            ];

            $this -> piper_lang -> translateWithPlural('key', 1, $translations);
            $this -> expectException(RuntimeException::class);
            $this -> expectExceptionMessage('Translation not found.');

            $this -> piper_lang -> setLocale('en');

            $result = $this -> piper_lang -> translateWithPlural('key', 1, $translations);
            $this -> assertEquals('You have 1 new message', $result);

            $result = $this -> piper_lang -> translateWithPlural('key', 2, $translations);
            $this -> assertEquals('You have 2 new messages', $result);

            $this -> piper_lang -> setLocale('es');

            $result = $this -> piper_lang -> translateWithPlural('key', 1, $translations);
            $this -> assertEquals('Tienes 1 nuevo mensaje', $result);

            $result = $this -> piper_lang -> translateWithPlural('key', 2, $translations);
            $this -> assertEquals('Tienes 2 nuevos mensajes', $result);
        }
    }
