<?php

namespace KPT\Tests;

use KPT\Database;

require_once __DIR__ . '/DatabaseTestCase.php';

class ProfilingTest extends DatabaseTestCase
{
    public function testEnableProfilingReturnsInstance(): void
    {
        $result = $this->db->enableProfiling();

        $this->assertInstanceOf(Database::class, $result);
    }

    public function testDisableProfilingReturnsInstance(): void
    {
        $result = $this->db->disableProfiling();

        $this->assertInstanceOf(Database::class, $result);
    }

    public function testGetQueryLogReturnsArray(): void
    {
        $log = $this->db->getQueryLog();

        $this->assertIsArray($log);
    }

    public function testClearQueryLogReturnsInstance(): void
    {
        $result = $this->db->clearQueryLog();

        $this->assertInstanceOf(Database::class, $result);
    }

    public function testQueryLogIsEmptyByDefault(): void
    {
        $this->db->enableProfiling();
        $log = $this->db->getQueryLog();

        $this->assertEmpty($log);
    }

    public function testQueryLogCapturesSelectQueries(): void
    {
        $this->db->enableProfiling();
        $this->seedUsers(1);

        $this->db->query("SELECT * FROM users")->fetch();

        $log = $this->db->getQueryLog();

        $this->assertNotEmpty($log);
        $this->assertStringContainsString('SELECT * FROM users', $log[0]['query']);
    }

    public function testQueryLogCapturesInsertQueries(): void
    {
        $this->db->enableProfiling();

        $this->db->query("INSERT INTO users (name, email) VALUES (?, ?)")
            ->bind(['Test', 'test@example.com'])
            ->execute();

        $log = $this->db->getQueryLog();

        // Find the INSERT query in the log
        $insertLog = array_filter($log, fn($entry) => str_contains($entry['query'], 'INSERT INTO users'));

        $this->assertNotEmpty($insertLog);
    }

    public function testQueryLogIncludesParams(): void
    {
        $this->db->enableProfiling();
        $this->seedUsers(1);

        $this->db->query("SELECT * FROM users WHERE id = ?")
            ->bind([1])
            ->fetch();

        $log = $this->db->getQueryLog();

        $selectLog = array_filter($log, fn($entry) => str_contains($entry['query'], 'WHERE id'));
        $selectLog = array_values($selectLog)[0];

        $this->assertArrayHasKey('params', $selectLog);
        $this->assertEquals([1], $selectLog['params']);
    }

    public function testQueryLogIncludesDuration(): void
    {
        $this->db->enableProfiling();
        $this->seedUsers(1);

        $this->db->query("SELECT * FROM users")->fetch();

        $log = $this->db->getQueryLog();
        $lastEntry = end($log);

        $this->assertArrayHasKey('duration_ms', $lastEntry);
        $this->assertIsFloat($lastEntry['duration_ms']);
        $this->assertGreaterThanOrEqual(0, $lastEntry['duration_ms']);
    }

    public function testQueryLogIncludesTimestamp(): void
    {
        $this->db->enableProfiling();
        $this->seedUsers(1);

        $this->db->query("SELECT * FROM users")->fetch();

        $log = $this->db->getQueryLog();
        $lastEntry = end($log);

        $this->assertArrayHasKey('timestamp', $lastEntry);
        $this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $lastEntry['timestamp']);
    }

    public function testClearQueryLogEmptiesLog(): void
    {
        $this->db->enableProfiling();
        $this->seedUsers(1);

        $this->db->query("SELECT * FROM users")->fetch();

        $this->db->clearQueryLog();
        $log = $this->db->getQueryLog();

        $this->assertEmpty($log);
    }

    public function testDisabledProfilingDoesNotLog(): void
    {
        $this->db->disableProfiling();
        $this->seedUsers(1);

        $this->db->query("SELECT * FROM users")->fetch();

        $log = $this->db->getQueryLog();

        $this->assertEmpty($log);
    }

    public function testProfilingCanBeToggledMidSession(): void
    {
        $this->seedUsers(1);

        // Profiling off
        $this->db->query("SELECT * FROM users")->fetch();

        // Turn on profiling
        $this->db->enableProfiling();
        $this->db->query("SELECT * FROM users WHERE id = ?")->bind([1])->fetch();

        $log = $this->db->getQueryLog();

        // Should only have the second query
        $this->assertCount(1, $log);
        $this->assertStringContainsString('WHERE id', $log[0]['query']);
    }
}
