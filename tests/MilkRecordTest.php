<?php
declare(strict_types=1);

namespace DMMS\Tests;

use PDO;
use Exception;

require_once __DIR__ . '/TestCase.php';

class MilkRecordTest extends \TestCase
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

        // Create test farmer
        $this->createTestFarmer();
    }

    private function createTestFarmer()
    {
        $this->pdo->exec("
            INSERT INTO users (id, first_name, last_name, email, password, role)
            VALUES (1, 'Test', 'Farmer', 'test@farmer.com', 'password', 'farmer')
        ");

        $this->pdo->exec("
            INSERT INTO farmers (id, user_id, phone, location)
            VALUES (1, 1, '1234567890', 'Test Location')
        ");
    }

    public function testMilkRecordCreation()
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO milk_records (farmer_id, quantity_liters, price_per_liter)
             VALUES (?, ?, ?)"
        );
        
        $result = $stmt->execute([1, 50.5, 45.00]);
        $this->assertTrue($result);
        
        // Verify record exists
        $stmt = $this->pdo->query("SELECT * FROM milk_records WHERE farmer_id = 1");
        $record = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->assertNotFalse($record);
        $this->assertEquals(50.5, (float)$record['quantity_liters']);
        $this->assertEquals(45.00, (float)$record['price_per_liter']);
    }

    public function testMilkRecordCalculation()
    {
        // Add multiple records
        $records = [
            [1, 50.5, 45.00],
            [1, 48.0, 45.00],
            [1, 52.5, 45.00]
        ];

        $stmt = $this->pdo->prepare(
            "INSERT INTO milk_records (farmer_id, quantity_liters, price_per_liter)
             VALUES (?, ?, ?)"
        );

        foreach ($records as $record) {
            $stmt->execute($record);
        }

        // Calculate total
        $stmt = $this->pdo->query(
            "SELECT ROUND(SUM(quantity_liters * price_per_liter), 2) as total
             FROM milk_records WHERE farmer_id = 1"
        );
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $expectedTotal = round((50.5 + 48.0 + 52.5) * 45.00, 2);
        $this->assertEquals($expectedTotal, (float)$result['total']);
    }
}
