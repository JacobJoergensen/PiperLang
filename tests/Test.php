<?php declare(strict_types=1);
    namespace Tests;

    use PHPUnit\Framework\TestCase;
    use PiperLang\PiperLang;

    final class Test extends TestCase {
        private PiperLang $piper_lang;

        protected function setUp(): void {
            $this -> piper_lang = new PiperLang();
        }
    }
