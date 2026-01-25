<?php

namespace KPT\Tests;

use KPT\Database;

require_once __DIR__ . '/DatabaseTestCase.php';

class QueryTest extends DatabaseTestCase
{
    public function testQueryReturnsInstance(): void
    {
        $result = $this->db->query("SELECT 1");
        $this->assertInstanceOf(Database::class, $result);
    }

    public function testBindReturnsInstance(): void
    {
        $result = $this->db->query("SELECT ?")->bind(1);
        $this->assertInstanceOf(Database::class, $result);
    }

    public function testSingleReturnsInstance(): void
    {
        $result = $this->db->query("SELECT 1")->single();
        $this->assertInstanceOf(Database::class, $result);
    }

    public function testManyReturnsInstance(): void
    {
        $result = $this->db->query("SELECT 1")->many();
        $this->assertInstanceOf(Database::class, $result);
    }

    public function testAsArrayReturnsInstance(): void
    {
        $result = $this->db->query("SELECT 1")->asArray();
        $this->assertInstanceOf(Database::class, $result);
    }

    public function testAsObjectReturnsInstance(): void
    {
        $result = $this->db->query("SELECT 1")->asObject();
        $this->assertInstanceOf(Database::class, $result);
    }

    public function testResetReturnsInstance(): void
    {
        $result = $this->db->reset();
        $this->assertInstanceOf(Database::class, $result);
    }

    public function testMethodChaining(): void
    {
        $this->seedUsers(1);

        $result = $this->db
            ->query("SELECT * FROM users WHERE id = ?")
            ->bind(1)
            ->single()
            ->asArray()
            ->fetch();

        $this->assertIsArray($result);
        $this->assertEquals('John Doe', $result['name']);
    }

    public function testFetchWithoutQueryThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No query has been set');

        $this->db->fetch();
    }

    public function testExecuteWithoutQueryThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No query has been set');

        $this->db->execute();
    }

    public function testFetchReturnsObjectByDefault(): void
    {
        $this->seedUsers(1);

        $result = $this->db->query("SELECT * FROM users")->single()->fetch();

        $this->assertIsObject($result);
        $this->assertEquals('John Doe', $result->name);
    }

    public function testFetchAsArrayReturnsArray(): void
    {
        $this->seedUsers(1);

        $result = $this->db->query("SELECT * FROM users")->single()->asArray()->fetch();

        $this->assertIsArray($result);
        $this->assertEquals('John Doe', $result['name']);
    }

    public function testFetchManyReturnsArray(): void
    {
        $this->seedUsers(3);

        $result = $this->db->query("SELECT * FROM users")->fetch();

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }

    public function testFetchSingleReturnsOneRecord(): void
    {
        $this->seedUsers(3);

        $result = $this->db->query("SELECT * FROM users ORDER BY id")->single()->fetch();

        $this->assertIsObject($result);
        $this->assertEquals('John Doe', $result->name);
    }

    public function testFetchWithLimitOne(): void
    {
        $this->seedUsers(3);

        $result = $this->db->query("SELECT * FROM users ORDER BY id")->fetch(1);

        $this->assertIsObject($result);
        $this->assertEquals('John Doe', $result->name);
    }

    public function testFetchWithLimitMany(): void
    {
        $this->seedUsers(3);

        $result = $this->db->query("SELECT * FROM users ORDER BY id")->fetch(2);

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
    }

    public function testFetchNoResultsReturnsFalse(): void
    {
        $result = $this->db->query("SELECT * FROM users WHERE id = 999")->fetch();

        $this->assertFalse($result);
    }

    public function testFetchSingleNoResultsReturnsFalse(): void
    {
        $result = $this->db->query("SELECT * FROM users WHERE id = 999")->single()->fetch();

        $this->assertFalse($result);
    }
}
