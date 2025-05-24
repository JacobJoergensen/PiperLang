<?php
    namespace Tests;

    use DateTimeImmutable;
    use IntlDateFormatter;
    use InvalidArgumentException;
    use JsonException;
    use PHPUnit\Framework\Attributes\CoversClass;
    use PHPUnit\Framework\Attributes\DataProvider;
    use PHPUnit\Framework\Attributes\Test;
    use PHPUnit\Framework\TestCase;
    use PiperLang\PiperLang;
    use RuntimeException;

    #[CoversClass(PiperLang::class)]
    class PiperLangTest extends TestCase {
        private string $testLocalesPath;

        protected function setUp(): void {
            // Create a temporary directory for test locale files.
            $this->testLocalesPath = sys_get_temp_dir() . '/piperlang_test_' . bin2hex(random_bytes(8)) . '/locales/';
            mkdir($this->testLocalesPath, 0777, true);

            // Set up necessary environment variables.
            $_SERVER['DOCUMENT_ROOT'] = dirname($this->testLocalesPath);

            // Create test locale files.
            $this->createTestLocaleFiles();
        }

        protected function tearDown(): void {
            // Cleanup session.
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_destroy();
            }

            // Clean up cookies.
            foreach ($_COOKIE as $key => $value) {
                unset($_COOKIE[$key]);
            }

            // Clean up test locale files.
            $this->removeDirectory(dirname($this->testLocalesPath));

            // Reset superglobals.
            $_SERVER = [];
            $_SESSION = [];
            $_COOKIE = [];
            $_GET = [];
        }

        /**
         * Create test locale files for testing.
         */
        private function createTestLocaleFiles(): void {
            // English locale file.
            $en_content = json_encode([
                'variables' => [
                    'site_name' => 'Test Site',
                    'company' => 'Acme Corp',
                ],
                'welcome' => 'Welcome to {{site_name}}',
                'about' => 'About {{company}}',
                'html_content' => '<a href="test">Link</a> <br> Test <script>alert("xss")</script>',
            ], JSON_THROW_ON_ERROR);

            // French locale file.
            $fr_content = json_encode([
                'variables' => [
                    'site_name' => 'Site de Test',
                    'company' => 'Acme Corp',
                ],
                'welcome' => 'Bienvenue à {{site_name}}',
                'about' => 'À propos de {{company}}',
                'html_content' => '<a href="test">Lien</a> <br> Test <script>alert("xss")</script>',
            ], JSON_THROW_ON_ERROR);

            // Invalid locale file (for testing error handling).
            $invalid_content = '{invalid json}';

            file_put_contents($this->testLocalesPath . 'en.json', $en_content);
            file_put_contents($this->testLocalesPath . 'fr.json', $fr_content);
            file_put_contents($this->testLocalesPath . 'invalid.json', $invalid_content);
        }

        /**
         * Recursively remove a directory and its contents.
         */
        private function removeDirectory(string $dir): void {
            if (! is_dir($dir)) {
                return;
            }

            $files = array_diff(scandir($dir), ['.', '..']);

            foreach ($files as $file) {
                $path = "$dir/$file";

                if (is_dir($path)) {
                    $this->removeDirectory($path);
                } else {
                    unlink($path);
                }
            }

            rmdir($dir);
        }

        #[Test]
        public function constructorShouldInitializeWithDefaultValues(): void {
            $piperLang = new PiperLang();

            static::assertSame('en', $piperLang->default_locale);
            static::assertSame('en', $piperLang->current_locale);
            static::assertSame('/locales/', $piperLang->locale_path);
            static::assertSame('json', $piperLang->locale_file_extension);
            static::assertSame('<a><br>', $piperLang->allowed_tags);
            static::assertSame('/{{(.*?)}}/', $piperLang->variable_pattern);
            static::assertSame(['en'], $piperLang->supported_locales);
        }

        #[Test]
        public function constructorShouldInitializeWithCustomValues(): void {
            $path = $this->testLocalesPath; // already created in setup()

            $piperLang = new PiperLang(
                allowed_tags: '<a><br><p>',
                cookie_enabled: true,
                cookie_key: 'custom_locale',
                debug: true,
                default_locale: 'fr',
                locale_path: '/locales/',
                locale_file_extension: 'json',
                session_enabled: false,
                session_key: 'custom_session_key',
                supported_locales: ['fr', 'en', 'de']
            );

            static::assertSame('<a><br><p>', $piperLang->allowed_tags);
            static::assertTrue($piperLang->cookie_enabled);
            static::assertSame('custom_locale', $piperLang->cookie_key);
            static::assertTrue($piperLang->debug);
            static::assertSame('fr', $piperLang->default_locale);
            static::assertSame('/locales/', $piperLang->locale_path);
            static::assertSame('json', $piperLang->locale_file_extension);
            static::assertFalse($piperLang->session_enabled);
            static::assertSame('custom_session_key', $piperLang->session_key);
            static::assertSame(['fr', 'en', 'de'], $piperLang->supported_locales);
        }

        #[Test]
        public function getInfoShouldReturnCorrectConfigurationInformation(): void {
            $piperLang = new PiperLang(
                debug: true,
                default_locale: 'fr',
                supported_locales: ['fr', 'en']
            );

            $info = $piperLang->getInfo();

            static::assertTrue(isset($info['Debug Status']));
            static::assertSame('fr', $info['Default Locale']);
            static::assertSame(['fr', 'en'], $info['Supported Locales']);
            static::assertSame('/locales/', $info['Path to Locales']);
            static::assertSame('json', $info['Locale File Extension']);
            static::assertSame('<a><br>', $info['Allowed HTML Tags']);
            static::assertSame('/{{(.*?)}}/', $info['Variable Pattern']);
        }

        #[Test]
        public function detectLocaleShouldReturnDefaultLocaleWhenNoOtherLocaleIsSpecified(): void {
            $piperLang = new PiperLang(
                cookie_enabled: false,
                default_locale: 'en',
                session_enabled: false,
                supported_locales: ['en', 'fr']
            );

            static::assertSame('en', $piperLang->detectLocale());
        }

        #[Test]
        public function detectLocaleShouldRespectAcceptLanguageHeader(): void {
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr-FR,fr;q=0.9,en-US;q=0.8,en;q=0.7';

            $piperLang = new PiperLang(
                cookie_enabled: false,
                default_locale: 'en',
                session_enabled: false,
                supported_locales: ['en', 'fr']
            );

            static::assertSame('fr', $piperLang->detectLocale());
        }

        #[Test]
        public function detectLocaleShouldRespectSessionValue(): void {
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9';

            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            $_SESSION['locale'] = 'fr';

            $piperLang = new PiperLang(
                cookie_enabled: false,
                default_locale: 'en',
                session_enabled: true,
                supported_locales: ['en', 'fr']
            );

            static::assertSame('fr', $piperLang->detectLocale());
        }

        #[Test]
        public function detectLocaleShouldRespectCookieValue(): void {
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en;q=0.9';
            $_COOKIE['site_locale'] = 'fr';

            $piperLang = new PiperLang(
                cookie_enabled: true,
                default_locale: 'en',
                session_enabled: false,
                supported_locales: ['en', 'fr']
            );

            static::assertSame('fr', $piperLang->detectLocale());
        }

        #[Test]
        public function detectLocaleShouldUseSessionOverCookieAndHeader(): void {
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'de-DE,de;q=0.9';
            $_COOKIE['site_locale'] = 'fr';

            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start();
            }

            $_SESSION['locale'] = 'en';

            $piperLang = new PiperLang(
                cookie_enabled: true,
                default_locale: 'fr',
                session_enabled: true,
                supported_locales: ['en', 'fr', 'de']
            );

            static::assertSame('en', $piperLang->detectLocale());
        }

        #[Test]
        public function getLocaleShouldReturnCurrentLocale(): void {
            $piperLang = new PiperLang(
                default_locale: 'en',
                supported_locales: ['en', 'fr']
            );

            $piperLang->current_locale = 'fr';

            static::assertSame('fr', $piperLang->getLocale());
        }

        #[Test]
        public function getLocaleShouldReturnDefaultLocaleWhenCurrentLocaleIsNull(): void {
            $piperLang = new PiperLang(
                default_locale: 'en',
                supported_locales: ['en', 'fr']
            );

            $piperLang->current_locale = null;

            static::assertSame('en', $piperLang->getLocale());
        }

        #[Test]
        public function setLocaleShouldUpdateCurrentLocaleAndStoreInSession(): void {
            $_SERVER['DOCUMENT_ROOT'] = sys_get_temp_dir();

            // Mock loadLocale method to prevent actual file loading.
            $mock_piperLang = $this->getMockBuilder(PiperLang::class)
                ->setConstructorArgs([
                    'locale_path' => '/locales/',
                    'default_locale' => 'en',
                    'supported_locales' => ['en', 'fr'],
                    'session_enabled' => true,
                    'cookie_enabled' => false,
                ])
                ->onlyMethods(['loadLocale'])
                ->getMock();

            $mock_piperLang->expects(static::once())
                ->method('loadLocale')
                ->with('fr');

            $mock_piperLang->setLocale('fr');

            static::assertSame('fr', $mock_piperLang->current_locale);
            static::assertSame('fr', $_SESSION['locale']);
        }

        #[Test]
        public function setLocaleShouldFallbackToDefaultLocaleForUnsupportedLocales(): void {
            // Mock loadLocale method to prevent actual file loading
            $mock_piperLang = $this->getMockBuilder(PiperLang::class)
                ->setConstructorArgs([
                    'default_locale' => 'en',
                    'supported_locales' => ['en', 'fr'],
                    'session_enabled' => false,
                    'cookie_enabled' => false,
                ])
                ->onlyMethods(['loadLocale'])
                ->getMock();

            $mock_piperLang->expects(static::once())
                ->method('loadLocale')
                ->with('en');

            $mock_piperLang->setLocale('de');

            static::assertSame('en', $mock_piperLang->current_locale);
        }

        #[Test]
        public function getTranslationShouldReturnTranslationForValidKey(): void {
            $piperLang = new PiperLang();
            $piperLang->current_locale = 'en';

            $piperLang->loaded_locales = [
                'en' => [
                    'welcome' => 'Welcome',
                    'goodbye' => 'Goodbye',
                ],
            ];

            static::assertSame('Welcome', $piperLang->getTranslation('welcome'));
        }

        #[Test]
        public function getTranslationShouldReturnErrorMessageForInvalidKey(): void {
            $piperLang = new PiperLang();
            $piperLang->current_locale = 'en';

            $piperLang->loaded_locales = [
                'en' => [
                    'welcome' => 'Welcome',
                ],
            ];

            static::assertSame('Translation missing: missing_key', $piperLang->getTranslation('missing_key'));
        }

        #[Test]
        public function getTranslationShouldStripTagsWhenEscapeIsTrue(): void {
            $piperLang = new PiperLang(allowed_tags: '<a>');
            $piperLang->current_locale = 'en';

            $piperLang->loaded_locales = [
                'en' => [
                    'html_content' => '<a href="test">Link</a> <br> Test <script>alert("xss")</script>',
                ],
            ];

            $expected = '<a href="test">Link</a>  Test alert("xss")';

            static::assertSame($expected, $piperLang->getTranslation('html_content'));
        }

        #[Test]
        public function getTranslationShouldNotStripTagsWhenEscapeIsFalse(): void {
            $piperLang = new PiperLang();
            $piperLang->current_locale = 'en';

            $piperLang->loaded_locales = [
                'en' => [
                    'html_content' => '<a href="test">Link</a> <br> Test <script>alert("xss")</script>',
                ],
            ];

            $expected = '<a href="test">Link</a> <br> Test <script>alert("xss")</script>';

            static::assertSame($expected, $piperLang->getTranslation('html_content', false));
        }

        #[Test]
        public function loadLocaleShouldLoadTranslationsFromFile(): void {
            // Set up a test environment where files can be found.
            $_SERVER['DOCUMENT_ROOT'] = dirname($this->testLocalesPath);

            $piperLang = new PiperLang(
                debug: false,
                default_locale: 'en',
                locale_path: '/locales/',
                supported_locales: ['en', 'fr']
            );

            $piperLang->loadLocale('en');

            static::assertArrayHasKey('en', $piperLang->loaded_locales);
            static::assertArrayHasKey('welcome', $piperLang->loaded_locales['en']);
            static::assertSame('Welcome to Test Site', $piperLang->loaded_locales['en']['welcome']);
        }

        #[Test]
        public function loadLocaleShouldReplaceVariablesInTranslations(): void {
            $_SERVER['DOCUMENT_ROOT'] = dirname($this->testLocalesPath);

            $piperLang = new PiperLang(
                debug: false,
                default_locale: 'en',
                locale_path: '/locales/',
                supported_locales: ['en', 'fr']
            );

            $piperLang->loadLocale('en');

            static::assertSame('Welcome to Test Site', $piperLang->loaded_locales['en']['welcome']);
            static::assertSame('About Acme Corp', $piperLang->loaded_locales['en']['about']);
        }

        #[Test]
        public function loadLocaleShouldGracefullyHandleMissingDocumentRoot(): void {
            unset($_SERVER['DOCUMENT_ROOT']);

            $piperLang = new PiperLang(
                debug: false,
                default_locale: 'en',
                supported_locales: ['en', 'fr']
            );

            // This should not throw an exception.
            $piperLang->loadLocale('en');

            static::assertArrayNotHasKey('en', $piperLang->loaded_locales);
        }

        #[Test]
        public function loadLocaleShouldThrowExceptionForInvalidJsonInDebugMode(): void {
            $_SERVER['DOCUMENT_ROOT'] = dirname($this->testLocalesPath);

            $piperLang = new PiperLang(
                debug: true,
                default_locale: 'en',
                locale_path: '/locales/',
                supported_locales: ['en', 'fr', 'invalid']
            );

            $this->expectException(JsonException::class);
            $piperLang->loadLocale('invalid');
        }

        #[Test]
        public function loadLocaleShouldNotThrowExceptionForInvalidJsonWhenNotInDebugMode(): void {
            $_SERVER['DOCUMENT_ROOT'] = dirname($this->testLocalesPath);

            $piperLang = new PiperLang(
                debug: false,
                default_locale: 'en',
                locale_path: '/locales/',
                supported_locales: ['en', 'fr', 'invalid']
            );

            // This should not throw an exception.
            $piperLang->loadLocale('invalid');

            static::assertArrayNotHasKey('invalid', $piperLang->loaded_locales);
        }

        #[Test]
        public function loadLocaleShouldFallBackToDefaultLocaleIfFileNotFound(): void {
            $_SERVER['DOCUMENT_ROOT'] = dirname($this->testLocalesPath);

            $piperLang = new PiperLang(
                debug: false,
                default_locale: 'en',
                locale_path: '/locales/',
                locale_file_extension: 'json',
                supported_locales: ['en', 'fr', 'es']
            );

            // Ensure the file for 'es' doesn't exist so fallback is triggered.
            @unlink($this->testLocalesPath . 'es.json');

            $piperLang->loadLocale('es');

            // Check that fallback happened.
            static::assertArrayHasKey('en', $piperLang->loaded_locales);
            static::assertArrayNotHasKey('es', $piperLang->loaded_locales);
        }

        #[Test]
        public function unloadFileShouldRemoveLoadedLocale(): void {
            $piperLang = new PiperLang(debug: false);
            $piperLang->loaded_locales = [
                'en' => ['welcome' => 'Welcome'],
                'fr' => ['welcome' => 'Bienvenue'],
            ];

            $piperLang->unloadFile('en');

            static::assertArrayNotHasKey('en', $piperLang->loaded_locales);
            static::assertArrayHasKey('fr', $piperLang->loaded_locales);
        }

        #[Test]
        public function unloadFileShouldThrowExceptionInDebugModeForNonLoadedLocale(): void {
            $piperLang = new PiperLang(debug: true);
            $piperLang->loaded_locales = ['en' => ['welcome' => 'Welcome']];

            $this->expectException(RuntimeException::class);
            $piperLang->unloadFile('fr');
        }

        #[Test]
        public function unloadFileShouldNotThrowExceptionWhenNotInDebugModeForNonLoadedLocale(): void {
            $piperLang = new PiperLang(debug: false);
            $piperLang->loaded_locales = ['en' => ['welcome' => 'Welcome']];

            // This should not throw an exception.
            $piperLang->unloadFile('fr');

            static::assertArrayHasKey('en', $piperLang->loaded_locales);
        }

        #[Test]
        public function replaceVariablesShouldReplacePlaceholdersWithVariableValuesUsingDefaultPattern(): void {
            $piperLang = new PiperLang();

            $result = $piperLang->replaceVariables(
                'Hello {{name}}, welcome to {{site}}!',
                ['name' => 'John', 'site' => 'Example.com']
            );

            static::assertSame('Hello John, welcome to Example.com!', $result);
        }

        #[Test]
        public function replaceVariablesShouldReplacePlaceholdersWithVariableValuesUsingCustomPattern(): void {
            $piperLang = new PiperLang();
            $piperLang->variable_pattern = '/%(\w+)%/';

            $result = $piperLang->replaceVariables(
                'Hello %name%, welcome to %site%!',
                ['name' => 'John', 'site' => 'Example.com']
            );

            static::assertSame('Hello John, welcome to Example.com!', $result);
        }

        #[Test]
        public function replaceVariablesShouldKeepOriginalPlaceholderIfVariableNotFound(): void {
            $piperLang = new PiperLang();

            $result = $piperLang->replaceVariables(
                'Hello {{name}}, welcome to {{site}}!',
                ['name' => 'John']
            );

            static::assertSame('Hello John, welcome to {{site}}!', $result);
        }

        #[Test]
        public function formatNumberShouldFormatNumbersAccordingToLocale(): void {
            $piperLang = new PiperLang();
            $piperLang->current_locale = 'en_US';

            $result = $piperLang->formatNumber(1234.56);

            // US locale uses period as decimal separator.
            static::assertSame('1,234.56', $result);
        }

        #[Test]
        public function formatNumberShouldRespectMaxFractionDigitsParameter(): void {
            $piperLang = new PiperLang();
            $piperLang->current_locale = 'en_US';

            $result = $piperLang->formatNumber(1234.56789, 3);

            static::assertSame('1,234.568', $result);
        }

        #[Test]
        public function formatNumberShouldThrowExceptionWhenLocaleIsNotSet(): void {
            $piperLang = new PiperLang();
            $piperLang->current_locale = null;

            $this->expectException(InvalidArgumentException::class);
            $piperLang->formatNumber(1234.56);
        }

        #[Test]
        public function formatCurrencyShouldFormatCurrencyAccordingToLocaleWithSymbol(): void {
            $piperLang = new PiperLang();
            $piperLang->current_locale = 'en_US';

            $result = $piperLang->formatCurrency(1234.56, 'USD');

            // Format depends on the locale implementation but should contain the symbol
            static::assertStringContainsString('$', $result);
            static::assertStringContainsString('1,234.56', $result);
        }

        #[Test]
        public function formatCurrencyShouldFormatCurrencyWithoutSymbolWhenShowSymbolIsFalse(): void {
            $piperLang = new PiperLang();
            $piperLang->current_locale = 'en_US';

            $result = $piperLang->formatCurrency(1234.56, 'USD', false);

            // Should not contain the $ symbol but should have USD instead
            static::assertStringContainsString('USD', $result);
        }

        #[Test]
        public function formatCurrencyShouldThrowExceptionForInvalidCurrencyCode(): void {
            $piperLang = new PiperLang();
            $piperLang->current_locale = 'en_US';

            $this->expectException(InvalidArgumentException::class);
            $piperLang->formatCurrency(1234.56, 'INVALID');
        }

        #[Test]
        #[DataProvider('dateFormatProvider')]
        public function formatDateShouldFormatDateAccordingToLocaleAndFormatStyle(
            string $format
        ): void {
            $mock_piperLang = $this->getMockBuilder(PiperLang::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['formatDate'])
                ->getMock();

            $date = new DateTimeImmutable('2023-05-15 10:30:00');

            // We're testing that the correct IntlDateFormatter style is selected.
            // The actual formatting depends on the system's locale settings.
            $mock_piperLang->expects(static::once())
                ->method('formatDate')
                ->with($date, $format);

            $mock_piperLang->formatDate($date, $format);
        }

        /**
         * Data provider for date format tests.
         *
         * @return array<string, array{string, int}>
         */
        public static function dateFormatProvider(): array {
            return [
                'short format'    => ['short', IntlDateFormatter::SHORT],
                'medium format'   => ['medium', IntlDateFormatter::MEDIUM],
                'long format'     => ['long', IntlDateFormatter::LONG],
                'full format'     => ['full', IntlDateFormatter::FULL],
                'default to long' => ['unknown', IntlDateFormatter::LONG],
            ];
        }

        #[Test]
        public function getFormattingRulesShouldReturnLocaleSpecificFormattingRules(): void {
            $piperLang = new PiperLang();

            // Fallback-safe system locale.
            $piperLang->current_locale = 'C';

            $rules = $piperLang->getFormattingRules();

            static::assertArrayHasKey('decimal_point', $rules);
            static::assertArrayHasKey('thousands_sep', $rules);
        }

        #[Test]
        public function getFormattingRulesShouldThrowExceptionWhenLocaleIsNotSet(): void {
            $piperLang = new PiperLang();
            $piperLang->current_locale = null;

            $this->expectException(InvalidArgumentException::class);
            $piperLang->getFormattingRules();
        }
    }
