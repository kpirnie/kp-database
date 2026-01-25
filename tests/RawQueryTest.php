<?php

namespace KPT\Tests;

require_once __DIR__ . '/DatabaseTestCase.php';

class RawQueryTest extends DatabaseTestCase
{
    public function testRawSelectReturnsResults(): void
    {
        $this->seedUsers(3);

        $result = $this->db->raw("SELECT * FROM users ORDER BY id");

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
    }

    public function testRawSelectWithParams(): void
    {
        $this->seedUsers(3);

        $result = $this->db->raw(
            "SELECT * FROM users WHERE name = ? AND email = ?",
            ['John Doe', 'john@example.com']
        );

        $this->assertCount(1, $result);
        $this->assertEquals('John Doe', $result[0]->name);
    }

    public function testRawSelectNoResultsReturnsFalse(): void
    {
        $result = $this->db->raw("SELECT * FROM users WHERE id = 999");

        $this->assertFalse($result);
    }

    public function testRawInsertReturnsLastId(): void
    {
        $result = $this->db->raw(
            "INSERT INTO users (name, email, age) VALUES (?, ?, ?)",
            ['Raw User', 'raw@example.com', 28]
        );

        $this->assertEquals(1, $result);

        // Verify insert
        $user = $this->db->query("SELECT * FROM users WHERE id = 1")->single()->fetch();
        $this->assertEquals('Raw User', $user->name);
    }

    public function testRawUpdateReturnsAffectedRows(): void
    {
        $this->seedUsers(3);

        $result = $this->db->raw(
            "UPDATE users SET active = ? WHERE active = ?",
            [0, 1]
        );

        $this->assertEquals(2, $result);
    }

    public function testRawDeleteReturnsAffectedRows(): void
    {
        $this->seedUsers(3);

        $result = $this->db->raw(
            "DELETE FROM users WHERE id = ?",
            [1]
        );

        $this->assertEquals(1, $result);
    }

    public function testRawComplexJoinQuery(): void
    {
        $users = $this->seedUsers(2);
        $this->seedPosts($users[0]['id'], 2);

        $result = $this->db->raw("
            SELECT u.name, p.title 
            FROM users u 
            INNER JOIN posts p ON u.id = p.user_id 
            WHERE u.id = ?
        ", [$users[0]['id']]);

        $this->assertCount(2, $result);
        $this->assertEquals('John Doe', $result[0]->name);
    }

    public function testRawWithSubquery(): void
    {
        $users = $this->seedUsers(2);
        $this->seedPosts($users[0]['id'], 3);

        $result = $this->db->raw("
            SELECT * FROM users 
            WHERE id IN (SELECT DISTINCT user_id FROM posts)
        ");

        $this->assertCount(1, $result);
        $this->assertEquals('John Doe', $result[0]->name);
    }
}
