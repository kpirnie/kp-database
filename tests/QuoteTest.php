<?php

namespace KPT\Tests;

require_once __DIR__ . '/DatabaseTestCase.php';

class QuoteTest extends DatabaseTestCase
{
    public function testQuoteReturnsQuotedString(): void
    {
        $result = $this->db->quote("test");

        $this->assertIsString($result);
        $this->assertStringContainsString('test', $result);
    }

    public function testQuoteEscapesSingleQuotes(): void
    {
        $result = $this->db->quote("O'Brien");

        $this->assertIsString($result);
        // SQLite escapes single quotes by doubling them
        $this->assertStringContainsString("O''Brien", $result);
    }

    public function testQuoteHandlesEmptyString(): void
    {
        $result = $this->db->quote("");

        $this->assertIsString($result);
        $this->assertEquals("''", $result);
    }

    public function testQuoteHandlesSpecialCharacters(): void
    {
        $result = $this->db->quote("test\nwith\ttabs");

        $this->assertIsString($result);
    }

    public function testQuoteCanBeUsedInRawQuery(): void
    {
        $this->seedUsers(1);

        $quotedEmail = $this->db->quote("john@example.com");

        // Remove the surrounding quotes for the comparison
        $result = $this->db->raw("SELECT * FROM users WHERE email = {$quotedEmail}");

        $this->assertNotFalse($result);
        $this->assertEquals('John Doe', $result[0]->name);
    }
}
