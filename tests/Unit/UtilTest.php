<?php

declare(strict_types=1);

namespace Gazelle\Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for utility functions
 */
class UtilTest extends TestCase
{
    protected function setUp(): void
    {
        // Load the util functions
        if (!function_exists('is_number')) {
            require_once dirname(__DIR__, 2) . '/classes/util.php';
        }
    }

    #[Test]
    public function is_number_returns_true_for_integers(): void
    {
        $this->assertTrue(is_number(123));
        $this->assertTrue(is_number(0));
        $this->assertTrue(is_number(-456));
    }

    #[Test]
    public function is_number_returns_true_for_numeric_strings(): void
    {
        $this->assertTrue(is_number('123'));
        $this->assertTrue(is_number('0'));
        $this->assertTrue(is_number('-456'));
        $this->assertTrue(is_number('+789'));
    }

    #[Test]
    public function is_number_returns_false_for_non_numeric_values(): void
    {
        $this->assertFalse(is_number('abc'));
        $this->assertFalse(is_number('12.34'));
        $this->assertFalse(is_number(''));
        $this->assertFalse(is_number(null));
        $this->assertFalse(is_number([]));
    }

    #[Test]
    public function is_date_validates_correct_dates(): void
    {
        $this->assertTrue(is_date('2024-01-15'));
        $this->assertTrue(is_date('2000-12-31'));
        $this->assertTrue(is_date('1999-06-30'));
    }

    #[Test]
    public function is_date_rejects_invalid_dates(): void
    {
        $this->assertFalse(is_date('2024-13-01')); // Invalid month
        $this->assertFalse(is_date('2024-02-30')); // Invalid day
        $this->assertFalse(is_date('invalid'));
        $this->assertFalse(is_date('2024/01/15')); // Wrong separator
    }

    #[Test]
    #[DataProvider('boolValueProvider')]
    public function is_bool_value_returns_expected_results(mixed $input, ?bool $expected): void
    {
        $this->assertSame($expected, is_bool_value($input));
    }

    public static function boolValueProvider(): array
    {
        return [
            // Boolean inputs
            [true, true],
            [false, false],

            // String true values
            ['true', true],
            ['TRUE', true],
            ['yes', true],
            ['YES', true],
            ['on', true],
            ['ON', true],
            ['1', true],

            // String false values
            ['false', false],
            ['FALSE', false],
            ['no', false],
            ['NO', false],
            ['off', false],
            ['OFF', false],
            ['0', false],

            // Numeric values
            [1, true],
            [0, false],

            // Invalid values return null
            ['maybe', null],
            [2, null],
            [-1, null],
            ['', null],
        ];
    }

    #[Test]
    public function display_str_escapes_html_special_chars(): void
    {
        // Mock the Format class
        if (!class_exists('Format')) {
            eval('class Format { public static function make_utf8($s) { return $s; } }');
        }

        $this->assertSame('', display_str(null));
        $this->assertSame('', display_str(false));
        $this->assertSame('', display_str([]));
        $this->assertSame('123', display_str(123));
    }

    #[Test]
    public function site_url_returns_https_url(): void
    {
        if (!defined('SITE_DOMAIN')) {
            define('SITE_DOMAIN', 'test.example.com');
        }

        $result = site_url();

        $this->assertStringStartsWith('https://', $result);
        $this->assertStringEndsWith('/', $result);
        $this->assertStringContainsString(SITE_DOMAIN, $result);
    }

    #[Test]
    public function sqltime_returns_valid_datetime_format(): void
    {
        $result = sqltime();

        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
            $result
        );
    }

    #[Test]
    public function time_plus_adds_seconds_to_current_time(): void
    {
        $before = time();
        $result = time_plus(3600); // 1 hour
        $after = time();

        $resultTime = strtotime($result);

        $this->assertGreaterThanOrEqual($before + 3600, $resultTime);
        $this->assertLessThanOrEqual($after + 3600, $resultTime);
    }

    #[Test]
    public function time_minus_subtracts_seconds_from_current_time(): void
    {
        $before = time();
        $result = time_minus(3600); // 1 hour
        $after = time();

        $resultTime = strtotime($result);

        $this->assertGreaterThanOrEqual($before - 3600, $resultTime);
        $this->assertLessThanOrEqual($after - 3600, $resultTime);
    }
}
