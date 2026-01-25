<?php

namespace KPT\Tests;

use PHPUnit\Framework\TestCase;
use KPT\Database;

// Include the mock logger
require_once __DIR__ . '/MockLogger.php';

/**
 * Base test case with SQLite in-memory database setup
 */
abstract class DatabaseTestCase extends TestCase
{
    protected Database $db;
    protected object $settings;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear any previous logs
        \KPT\Logger::clearLogs();

        // SQLite in-memory settings
        $this->settings = (object) [
            'driver' => 'sqlite',
            'path' => ':memory:'
        ];

        // Create database instance
        $this->db = new Database($this->settings);

        // Set up test tables
        $this->createTestTables();
    }

    protected function tearDown(): void
    {
        // Close any singleton instances
        Database::closeInstance('default');
        Database::closeInstance('test');
        Database::closeInstance('secondary');

        parent::tearDown();
    }

    protected function createTestTables(): void
    {
        // Users table
        $this->db->raw("
            CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT UNIQUE NOT NULL,
                age INTEGER,
                active INTEGER DEFAULT 1,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Posts table
        $this->db->raw("
            CREATE TABLE posts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title TEXT NOT NULL,
                body TEXT,
                published INTEGER DEFAULT 0,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id)
            )
        ");

        // Tags table for many-to-many testing
        $this->db->raw("
            CREATE TABLE tags (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT UNIQUE NOT NULL
            )
        ");

        // Post tags pivot table
        $this->db->raw("
            CREATE TABLE post_tags (
                post_id INTEGER NOT NULL,
                tag_id INTEGER NOT NULL,
                PRIMARY KEY (post_id, tag_id),
                FOREIGN KEY (post_id) REFERENCES posts(id),
                FOREIGN KEY (tag_id) REFERENCES tags(id)
            )
        ");
    }

    protected function seedUsers(int $count = 3): array
    {
        $users = [
            ['name' => 'John Doe', 'email' => 'john@example.com', 'age' => 30, 'active' => 1],
            ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'age' => 25, 'active' => 1],
            ['name' => 'Bob Wilson', 'email' => 'bob@example.com', 'age' => 35, 'active' => 0],
        ];

        $inserted = [];
        for ($i = 0; $i < min($count, count($users)); $i++) {
            $id = $this->db->query(
                "INSERT INTO users (name, email, age, active) VALUES (?, ?, ?, ?)"
            )->bind([
                $users[$i]['name'],
                $users[$i]['email'],
                $users[$i]['age'],
                $users[$i]['active']
            ])->execute();

            $users[$i]['id'] = $id;
            $inserted[] = $users[$i];
        }

        return $inserted;
    }

    protected function seedPosts(int $userId, int $count = 2): array
    {
        $posts = [];
        for ($i = 1; $i <= $count; $i++) {
            $id = $this->db->query(
                "INSERT INTO posts (user_id, title, body, published) VALUES (?, ?, ?, ?)"
            )->bind([
                $userId,
                "Post Title {$i}",
                "Post body content {$i}",
                $i % 2
            ])->execute();

            $posts[] = [
                'id' => $id,
                'user_id' => $userId,
                'title' => "Post Title {$i}",
                'body' => "Post body content {$i}",
                'published' => $i % 2
            ];
        }

        return $posts;
    }
}
