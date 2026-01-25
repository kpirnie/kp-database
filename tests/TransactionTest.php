<?php

namespace KPT\Tests;

require_once __DIR__ . '/DatabaseTestCase.php';

class TransactionTest extends DatabaseTestCase
{
    public function testTransactionReturnsTrue(): void
    {
        $result = $this->db->transaction();
        $this->assertTrue($result);
        $this->db->rollback();
    }

    public function testCommitReturnsTrue(): void
    {
        $this->db->transaction();

        $this->db->query("INSERT INTO users (name, email) VALUES (?, ?)")
            ->bind(['Test User', 'test@example.com'])
            ->execute();

        $result = $this->db->commit();
        $this->assertTrue($result);

        // Verify data was committed
        $user = $this->db->query("SELECT * FROM users WHERE email = ?")->bind(['test@example.com'])->single()->fetch();
        $this->assertEquals('Test User', $user->name);
    }

    public function testRollbackReturnsTrue(): void
    {
        $this->db->transaction();

        $this->db->query("INSERT INTO users (name, email) VALUES (?, ?)")
            ->bind(['Test User', 'test@example.com'])
            ->execute();

        $result = $this->db->rollback();
        $this->assertTrue($result);

        // Verify data was rolled back
        $user = $this->db->query("SELECT * FROM users WHERE email = ?")->bind(['test@example.com'])->single()->fetch();
        $this->assertFalse($user);
    }

    public function testInTransactionReturnsFalseInitially(): void
    {
        $this->assertFalse($this->db->inTransaction());
    }

    public function testInTransactionReturnsTrueDuringTransaction(): void
    {
        $this->db->transaction();
        $this->assertTrue($this->db->inTransaction());
        $this->db->rollback();
    }

    public function testInTransactionReturnsFalseAfterCommit(): void
    {
        $this->db->transaction();
        $this->db->commit();
        $this->assertFalse($this->db->inTransaction());
    }

    public function testInTransactionReturnsFalseAfterRollback(): void
    {
        $this->db->transaction();
        $this->db->rollback();
        $this->assertFalse($this->db->inTransaction());
    }

    public function testTransactionWithMultipleOperations(): void
    {
        $this->db->transaction();

        $userId = $this->db->query("INSERT INTO users (name, email) VALUES (?, ?)")
            ->bind(['Test User', 'test@example.com'])
            ->execute();

        $this->db->query("INSERT INTO posts (user_id, title, body) VALUES (?, ?, ?)")
            ->bind([$userId, 'Test Post', 'Post content'])
            ->execute();

        $this->db->commit();

        // Verify both records exist
        $user = $this->db->query("SELECT * FROM users WHERE id = ?")->bind([$userId])->single()->fetch();
        $post = $this->db->query("SELECT * FROM posts WHERE user_id = ?")->bind([$userId])->single()->fetch();

        $this->assertEquals('Test User', $user->name);
        $this->assertEquals('Test Post', $post->title);
    }

    public function testTransactionRollbackOnError(): void
    {
        $this->db->transaction();

        $this->db->query("INSERT INTO users (name, email) VALUES (?, ?)")
            ->bind(['Test User', 'test@example.com'])
            ->execute();

        // Manually rollback to simulate error handling
        $this->db->rollback();

        // Verify no data was committed
        $result = $this->db->query("SELECT * FROM users")->fetch();
        $this->assertFalse($result);
    }
}
