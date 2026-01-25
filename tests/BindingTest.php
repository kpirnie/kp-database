<?php

namespace KPT\Tests;

require_once __DIR__ . '/DatabaseTestCase.php';

class BindingTest extends DatabaseTestCase
{
    public function testBindSingleValue(): void
    {
        $this->seedUsers(1);

        $result = $this->db
            ->query("SELECT * FROM users WHERE id = ?")
            ->bind(1)
            ->single()
            ->fetch();

        $this->assertEquals('John Doe', $result->name);
    }

    public function testBindMultiplePositionalParams(): void
    {
        $this->seedUsers(3);

        $result = $this->db
            ->query("SELECT * FROM users WHERE name = ? AND email = ?")
            ->bind(['John Doe', 'john@example.com'])
            ->single()
            ->fetch();

        $this->assertEquals('John Doe', $result->name);
    }

    public function testBindNamedParams(): void
    {
        $this->seedUsers(3);

        $result = $this->db
            ->query("SELECT * FROM users WHERE name = :name AND email = :email")
            ->bind(['name' => 'John Doe', 'email' => 'john@example.com'])
            ->single()
            ->fetch();

        $this->assertEquals('John Doe', $result->name);
    }

    public function testBindNamedParamsWithColonPrefix(): void
    {
        $this->seedUsers(3);

        $result = $this->db
            ->query("SELECT * FROM users WHERE name = :name")
            ->bind([':name' => 'John Doe'])
            ->single()
            ->fetch();

        $this->assertEquals('John Doe', $result->name);
    }

    public function testBindInteger(): void
    {
        $this->seedUsers(3);

        $result = $this->db
            ->query("SELECT * FROM users WHERE age = ?")
            ->bind([30])
            ->single()
            ->fetch();

        $this->assertEquals('John Doe', $result->name);
    }

    public function testBindBoolean(): void
    {
        $this->seedUsers(3);

        // SQLite stores booleans as integers
        $result = $this->db
            ->query("SELECT * FROM users WHERE active = ?")
            ->bind([0])
            ->fetch();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('Bob Wilson', $result[0]->name);
    }

    public function testBindNull(): void
    {
        // Insert a user with null age
        $this->db->query("INSERT INTO users (name, email, age) VALUES (?, ?, ?)")
            ->bind(['Null User', 'null@example.com', null])
            ->execute();

        $result = $this->db
            ->query("SELECT * FROM users WHERE age IS NULL")
            ->single()
            ->fetch();

        $this->assertEquals('Null User', $result->name);
    }

    public function testBindString(): void
    {
        $this->seedUsers(1);

        $result = $this->db
            ->query("SELECT * FROM users WHERE email = ?")
            ->bind(['john@example.com'])
            ->single()
            ->fetch();

        $this->assertEquals('John Doe', $result->name);
    }

    public function testBindWithLikeOperator(): void
    {
        $this->seedUsers(3);

        $result = $this->db
            ->query("SELECT * FROM users WHERE name LIKE ?")
            ->bind(['%Doe%'])
            ->fetch();

        $this->assertCount(1, $result);
    }

    public function testBindEmptyArrayExecutes(): void
    {
        $this->seedUsers(1);

        $result = $this->db
            ->query("SELECT * FROM users")
            ->bind([])
            ->fetch();

        $this->assertIsArray($result);
    }
}
