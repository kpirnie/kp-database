<?php

namespace KPT\Tests;

require_once __DIR__ . '/DatabaseTestCase.php';

class BatchOperationsTest extends DatabaseTestCase
{
    public function testInsertBatchReturnsRowCount(): void
    {
        $columns = ['name', 'email', 'age'];
        $rows = [
            ['User 1', 'user1@example.com', 25],
            ['User 2', 'user2@example.com', 30],
            ['User 3', 'user3@example.com', 35],
        ];

        $result = $this->db->insertBatch('users', $columns, $rows);

        $this->assertEquals(3, $result);
    }

    public function testInsertBatchInsertsAllRows(): void
    {
        $columns = ['name', 'email'];
        $rows = [
            ['User 1', 'user1@example.com'],
            ['User 2', 'user2@example.com'],
        ];

        $this->db->insertBatch('users', $columns, $rows);

        $count = $this->db->count('users');
        $this->assertEquals(2, $count);

        $users = $this->db->query("SELECT * FROM users ORDER BY id")->fetch();
        $this->assertEquals('User 1', $users[0]->name);
        $this->assertEquals('User 2', $users[1]->name);
    }

    public function testInsertBatchWithEmptyColumnsReturnsFalse(): void
    {
        $result = $this->db->insertBatch('users', [], [['value']]);

        $this->assertFalse($result);
    }

    public function testInsertBatchWithEmptyRowsReturnsFalse(): void
    {
        $result = $this->db->insertBatch('users', ['name'], []);

        $this->assertFalse($result);
    }

    public function testInsertBatchWithMismatchedColumnCountReturnsFalse(): void
    {
        $columns = ['name', 'email', 'age'];
        $rows = [
            ['User 1', 'user1@example.com'], // Missing age
        ];

        $result = $this->db->insertBatch('users', $columns, $rows);

        $this->assertFalse($result);
    }

    public function testInsertBatchWithNullValues(): void
    {
        $columns = ['name', 'email', 'age'];
        $rows = [
            ['User 1', 'user1@example.com', null],
        ];

        $result = $this->db->insertBatch('users', $columns, $rows);

        $this->assertEquals(1, $result);

        $user = $this->db->query("SELECT * FROM users WHERE id = 1")->single()->fetch();
        $this->assertNull($user->age);
    }

    public function testReplaceInsertsNewRecord(): void
    {
        $data = [
            'name' => 'New User',
            'email' => 'new@example.com',
            'age' => 25
        ];

        $result = $this->db->replace('users', $data);

        $this->assertNotFalse($result);

        $user = $this->db->query("SELECT * FROM users WHERE email = ?")->bind(['new@example.com'])->single()->fetch();
        $this->assertEquals('New User', $user->name);
    }

    public function testReplaceUpdatesExistingRecord(): void
    {
        // Insert initial record
        $this->db->query("INSERT INTO users (id, name, email) VALUES (?, ?, ?)")
            ->bind([1, 'Original', 'test@example.com'])
            ->execute();

        // Replace with same ID
        $data = [
            'id' => 1,
            'name' => 'Replaced',
            'email' => 'replaced@example.com'
        ];

        $this->db->replace('users', $data);

        // Should only have one record
        $count = $this->db->count('users');
        $this->assertEquals(1, $count);

        // Should have updated values
        $user = $this->db->query("SELECT * FROM users WHERE id = 1")->single()->fetch();
        $this->assertEquals('Replaced', $user->name);
        $this->assertEquals('replaced@example.com', $user->email);
    }

    public function testReplaceWithEmptyDataReturnsFalse(): void
    {
        $result = $this->db->replace('users', []);

        $this->assertFalse($result);
    }

    public function testUpsertInsertsNewRecord(): void
    {
        $data = [
            'name' => 'New User',
            'email' => 'new@example.com'
        ];

        $update = [
            'name' => 'Updated User'
        ];

        $result = $this->db->upsert('users', $data, $update);

        $this->assertNotFalse($result);

        $user = $this->db->query("SELECT * FROM users WHERE email = ?")->bind(['new@example.com'])->single()->fetch();
        $this->assertEquals('New User', $user->name);
    }

    public function testUpsertWithEmptyDataReturnsFalse(): void
    {
        $result = $this->db->upsert('users', [], ['name' => 'Test']);

        $this->assertFalse($result);
    }

    public function testUpsertWithEmptyUpdateReturnsFalse(): void
    {
        $result = $this->db->upsert('users', ['name' => 'Test', 'email' => 'test@example.com'], []);

        $this->assertFalse($result);
    }
}
