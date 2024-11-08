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

        public function testModifierInheritsMethods(): void {
            $modifier = new Modifier();

            $info = $modifier -> getInfo();

            $this -> assertIsArray($info);

            $this -> assertArrayHasKey('Debug Status', $info);
            $this -> assertArrayHasKey('Current Locale', $info);
            $this -> assertArrayHasKey('Default Locale', $info);
        }
    }
