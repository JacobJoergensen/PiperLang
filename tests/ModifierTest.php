<?php
    namespace Tests;

    use PHPUnit\Framework\TestCase;

    use PiperLang\PiperLang;
    use PiperLang\Modifier;

    class ModifierTest extends TestCase {
        public function testModifierInstance(): void {
            $modifier = new Modifier();

            $this -> assertInstanceOf(Modifier::class, $modifier);
            $this -> assertInstanceOf(PiperLang::class, $modifier);
        }

        public function testModifierInheritsProperties(): void {
            $modifier = new Modifier();

            $this -> assertIsBool($modifier -> debug);
            $this -> assertEquals('en', $modifier -> default_locale);
            $this -> assertEquals('/locales/', $modifier -> locale_path);
            $this -> assertIsArray($modifier -> supported_locales);
            $this -> assertContains('en', $modifier -> supported_locales);
        }
    }
