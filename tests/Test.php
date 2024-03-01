<?php declare(strict_types=1);
    namespace Tests;

    use PHPUnit\Framework\TestCase;
    
    final class Test extends TestCase {
        public function greet(string $name): string {
            return 'Hello, ' . $name . '!';
        }
    }
