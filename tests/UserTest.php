<?php
declare(strict_types=1);

namespace DMMS\Tests;

use PDO;
use Exception;

require_once __DIR__ . '/TestCase.php';

class UserTest extends \TestCase
{
    private $pdo;

    public function setUp(): void
    {
        $this->pdo = new PDO('mysql:host=localhost;dbname=dmms_test', 'root', '');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Clear test database in correct order (child tables first)
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        $this->pdo->exec('TRUNCATE TABLE milk_records');
        $this->pdo->exec('TRUNCATE TABLE farmers');
        $this->pdo->exec('TRUNCATE TABLE users');
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function testUserCreation()
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (first_name, last_name, email, password, role) 
             VALUES (?, ?, ?, ?, ?)"
        );
        
        $result = $stmt->execute([
            'John',
            'Doe',
            'john@example.com',
            password_hash('password123', PASSWORD_DEFAULT),
            'farmer'
        ]);

        $this->assertTrue($result);
        
        // Verify user exists
        $stmt = $this->pdo->query("SELECT * FROM users WHERE email = 'john@example.com'");
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertNotFalse($user);
        $this->assertEquals('John', $user['first_name']);
        $this->assertEquals('farmer', $user['role']);
    }

    public function testUserAuthentication()
    {
        // Create test user
        $password = password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare(
            "INSERT INTO users (first_name, last_name, email, password, role) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute(['John', 'Doe', 'john@example.com', $password, 'farmer']);

        // Test authentication
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute(['john@example.com']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertTrue(password_verify('password123', $user['password']));
        $this->assertFalse(password_verify('wrongpassword', $user['password']));
    }
}
