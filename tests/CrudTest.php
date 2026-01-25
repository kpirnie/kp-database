<?php

namespace KPT\Tests;

require_once __DIR__ . '/DatabaseTestCase.php';

class CrudTest extends DatabaseTestCase
{
    public function testInsertReturnsLastInsertId(): void
    {
        $id = $this->db
            ->query("INSERT INTO users (name, email, age) VALUES (?, ?, ?)")
            ->bind(['Test User', 'test@example.com', 25])
            ->execute();

        $this->assertEquals(1, $id);
    }

    public function testInsertMultipleReturnsIncrementingIds(): void
    {
        $id1 = $this->db
            ->query("INSERT INTO users (name, email) VALUES (?, ?)")
            ->bind(['User 1', 'user1@example.com'])
            ->execute();

        $id2 = $this->db
            ->query("INSERT INTO users (name, email) VALUES (?, ?)")
            ->bind(['User 2', 'user2@example.com'])
            ->execute();

        $this->assertEquals(1, $id1);
        $this->assertEquals(2, $id2);
    }

    public function testUpdateReturnsAffectedRows(): void
    {
        $this->seedUsers(3);

        $affected = $this->db
            ->query("UPDATE users SET name = ? WHERE id = ?")
            ->bind(['Updated Name', 1])
            ->execute();

        $this->assertEquals(1, $affected);

        // Verify the update
        $user = $this->db->query("SELECT * FROM users WHERE id = 1")->single()->fetch();
        $this->assertEquals('Updated Name', $user->name);
    }

    public function testUpdateMultipleRowsReturnsCount(): void
    {
        $this->seedUsers(3);

        $affected = $this->db
            ->query("UPDATE users SET active = ?")
            ->bind([1])
            ->execute();

        $this->assertEquals(3, $affected);
    }

    public function testUpdateNoMatchReturnsZero(): void
    {
        $this->seedUsers(1);

        $affected = $this->db
            ->query("UPDATE users SET name = ? WHERE id = ?")
            ->bind(['Updated', 999])
            ->execute();

        $this->assertEquals(0, $affected);
    }

    public function testDeleteReturnsAffectedRows(): void
    {
        $this->seedUsers(3);

        $affected = $this->db
            ->query("DELETE FROM users WHERE id = ?")
            ->bind([1])
            ->execute();

        $this->assertEquals(1, $affected);

        // Verify deletion
        $result = $this->db->query("SELECT * FROM users WHERE id = 1")->single()->fetch();
        $this->assertFalse($result);
    }

    public function testDeleteMultipleRowsReturnsCount(): void
    {
        $this->seedUsers(3);

        $affected = $this->db
            ->query("DELETE FROM users WHERE active = ?")
            ->bind([1])
            ->execute();

        $this->assertEquals(2, $affected);
    }

    public function testDeleteNoMatchReturnsZero(): void
    {
        $this->seedUsers(1);

        $affected = $this->db
            ->query("DELETE FROM users WHERE id = ?")
            ->bind([999])
            ->execute();

        $this->assertEquals(0, $affected);
    }

    public function testGetLastId(): void
    {
        $this->db
            ->query("INSERT INTO users (name, email) VALUES (?, ?)")
            ->bind(['Test User', 'test@example.com'])
            ->execute();

        $lastId = $this->db->getLastId();

        $this->assertEquals(1, $lastId);
    }

    public function testGetLastIdAfterMultipleInserts(): void
    {
        $this->db->query("INSERT INTO users (name, email) VALUES (?, ?)")
            ->bind(['User 1', 'user1@example.com'])
            ->execute();

        $this->db->query("INSERT INTO users (name, email) VALUES (?, ?)")
            ->bind(['User 2', 'user2@example.com'])
            ->execute();

        $lastId = $this->db->getLastId();

        $this->assertEquals(2, $lastId);
    }
}
