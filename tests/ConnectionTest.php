<?php

namespace KPT\Tests;

use KPT\Database;
use InvalidArgumentException;

require_once __DIR__ . '/DatabaseTestCase.php';

class ConnectionTest extends DatabaseTestCase
{
    public function testCanCreateInstanceWithValidSettings(): void
    {
        $this->assertInstanceOf(Database::class, $this->db);
    }

    public function testThrowsExceptionWithNullSettings(): void
    {
        $this->expectException(\TypeError::class);
        new Database(null);
    }

    public function testThrowsExceptionWithMissingRequiredSettings(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('missing required property');

        $settings = (object) [
            'driver' => 'mysql',
            'server' => 'localhost'
            // missing schema, username, password
        ];

        new Database($settings);
    }

    public function testSqliteDoesNotRequireCredentials(): void
    {
        $settings = (object) [
            'driver' => 'sqlite',
            'path' => ':memory:'
        ];

        $db = new Database($settings);
        $this->assertInstanceOf(Database::class, $db);
    }

    public function testConfigureWithArray(): void
    {
        $config = [
            'driver' => 'sqlite',
            'path' => ':memory:'
        ];

        $db = Database::configure($config);
        $this->assertInstanceOf(Database::class, $db);
    }

    public function testConfigureWithObject(): void
    {
        $config = (object) [
            'driver' => 'sqlite',
            'path' => ':memory:'
        ];

        $db = Database::configure($config);
        $this->assertInstanceOf(Database::class, $db);
    }

    public function testGetInstanceCreatesSingleton(): void
    {
        $db1 = Database::getInstance('test', $this->settings);
        $db2 = Database::getInstance('test');

        $this->assertSame($db1, $db2);
    }

    public function testGetInstanceThrowsExceptionWithoutSettings(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Database settings required');

        Database::getInstance('nonexistent');
    }

    public function testCloseInstanceRemovesConnection(): void
    {
        $db1 = Database::getInstance('test', $this->settings);
        Database::closeInstance('test');

        // Should require settings again
        $this->expectException(InvalidArgumentException::class);
        Database::getInstance('test');
    }

    public function testMultipleNamedInstances(): void
    {
        $db1 = Database::getInstance('primary', $this->settings);
        $db2 = Database::getInstance('secondary', $this->settings);

        $this->assertNotSame($db1, $db2);
    }

    public function testLazyConnection(): void
    {
        // Creating instance should not connect immediately
        $settings = (object) [
            'driver' => 'sqlite',
            'path' => ':memory:'
        ];

        $db = new Database($settings);

        // Connection happens on first query
        $result = $db->raw("SELECT 1 as test");
        $this->assertNotEmpty($result);
    }
}
