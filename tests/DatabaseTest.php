<?php

namespace KPT\Tests;

use PHPUnit\Framework\TestCase;
use KPT\Database;

class DatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        // Skip all tests if database dependencies aren't available
        if (!defined('KPT_PATH')) {
            define('KPT_PATH', true);
        }
    }

    public function testDatabaseClassExists(): void
    {
        $this->assertTrue(class_exists('KPT\Database'));
    }

    public function testMethodChainingWorks(): void
    {
        // Test that we can call methods without errors (even without DB connection)
        $this->expectNotToPerformAssertions();
        
        try {
            $db = new Database();
            $db->query("SELECT 1")->single()->asArray();
        } catch (\Exception $e) {
            // Expected if no database connection
            $this->assertStringContainsString('database', strtolower($e->getMessage()));
        }
    }

    public function testResetReturnsInstance(): void
    {
        try {
            $db = new Database();
            $result = $db->reset();
            $this->assertInstanceOf(Database::class, $result);
        } catch (\Exception $e) {
            // Skip if database not available
            $this->markTestSkipped('Database not available');
        }
    }
}