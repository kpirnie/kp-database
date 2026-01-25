<?php

namespace KPT\Tests;

require_once __DIR__ . '/DatabaseTestCase.php';

class HelperMethodsTest extends DatabaseTestCase
{
    public function testCountAllRecords(): void
    {
        $this->seedUsers(3);

        $count = $this->db->count('users');

        $this->assertEquals(3, $count);
    }

    public function testCountWithWhereClause(): void
    {
        $this->seedUsers(3);

        $count = $this->db->count('users', '*', 'active = ?', [1]);

        $this->assertEquals(2, $count);
    }

    public function testCountSpecificColumn(): void
    {
        $this->seedUsers(3);

        $count = $this->db->count('users', 'id', 'age > ?', [25]);

        $this->assertEquals(2, $count);
    }

    public function testCountDistinct(): void
    {
        $this->seedUsers(3);

        $count = $this->db->count('users', 'DISTINCT active');

        $this->assertEquals(2, $count);
    }

    public function testCountEmptyTableReturnsZero(): void
    {
        $count = $this->db->count('users');

        $this->assertEquals(0, $count);
    }

    public function testExistsReturnsTrue(): void
    {
        $this->seedUsers(1);

        $exists = $this->db->exists('users', 'email = ?', ['john@example.com']);

        $this->assertTrue($exists);
    }

    public function testExistsReturnsFalse(): void
    {
        $this->seedUsers(1);

        $exists = $this->db->exists('users', 'email = ?', ['nonexistent@example.com']);

        $this->assertFalse($exists);
    }

    public function testExistsWithMultipleConditions(): void
    {
        $this->seedUsers(3);

        $exists = $this->db->exists('users', 'name = ? AND active = ?', ['John Doe', 1]);

        $this->assertTrue($exists);
    }

    public function testExistsOnEmptyTable(): void
    {
        $exists = $this->db->exists('users', 'id = ?', [1]);

        $this->assertFalse($exists);
    }

    public function testFirstReturnsOneRecord(): void
    {
        $this->seedUsers(3);

        $user = $this->db->query("SELECT * FROM users ORDER BY id")->first();

        $this->assertIsObject($user);
        $this->assertEquals('John Doe', $user->name);
    }

    public function testFirstReturnsFalseWhenNoResults(): void
    {
        $user = $this->db->query("SELECT * FROM users WHERE id = 999")->first();

        $this->assertFalse($user);
    }

    public function testFirstWithBindParams(): void
    {
        $this->seedUsers(3);

        $user = $this->db
            ->query("SELECT * FROM users WHERE active = ?")
            ->bind([0])
            ->first();

        $this->assertEquals('Bob Wilson', $user->name);
    }

    public function testFirstAsArray(): void
    {
        $this->seedUsers(1);

        $user = $this->db->query("SELECT * FROM users")->asArray()->first();

        $this->assertIsArray($user);
        $this->assertEquals('John Doe', $user['name']);
    }
}
