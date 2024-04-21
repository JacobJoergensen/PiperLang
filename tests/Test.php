<?php declare(strict_types=1);
    namespace Tests;

    use PHPUnit\Framework\TestCase;
    use PiperLang\PiperLang;

    final class Test extends TestCase {
        private PiperLang $piper_lang;

        protected function setUp(): void {
            $this -> piper_lang = new PiperLang();
            $this -> piper_lang -> supported_locales = ['en', 'es', 'de'];
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
    }
