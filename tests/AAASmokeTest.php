<?php

declare(strict_types=1);

/**
 * Contains the AAASmokeTest.php class.
 *
 * @copyright   Copyright (c) 2023 Attila Fulop
 * @author      Attila Fulop
 * @license     MIT
 * @since       2023-11-12
 *
 */

namespace Konekt\History\Tests;

class AAASmokeTest extends TestCase
{
    public const MIN_PHP_VERSION = '8.1.0';

    /**
     * @test
     */
    public function smoke()
    {
        $this->assertTrue(true);
    }

    /**
     * Test for minimum PHP version
     *
     * @depends smoke
     * @test
     */
    public function php_version_satisfies_requirements()
    {
        $this->assertFalse(
            version_compare(PHP_VERSION, self::MIN_PHP_VERSION, '<'),
            'PHP version ' . self::MIN_PHP_VERSION . ' or greater is required but only '
            . PHP_VERSION . ' found.'
        );
    }
}
