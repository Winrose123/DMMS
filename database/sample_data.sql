USE dmms;

-- Sample Users
INSERT INTO users (first_name, last_name, email, password, role) VALUES
('John', 'Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'farmer'),
('Jane', 'Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'farmer'),
('Bob', 'Wilson', 'bob@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'farmer');

-- Sample Farmers
INSERT INTO farmers (user_id, phone, location) VALUES
(1, '+254700000001', 'Nairobi'),
(2, '+254700000002', 'Nakuru'),
(3, '+254700000003', 'Eldoret');

-- Sample Milk Records
INSERT INTO milk_records (farmer_id, quantity_liters, price_per_liter, created_at) VALUES
(1, 25.5, 50.00, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 30.0, 50.00, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(1, 28.5, 50.00, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 35.0, 50.00, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(2, 32.5, 50.00, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(3, 40.0, 50.00, DATE_SUB(NOW(), INTERVAL 4 DAY));

-- Sample Expenses
INSERT INTO expenses (farmer_id, amount, description, date) VALUES
(1, 2000.00, 'Feed', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 1500.00, 'Medicine', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 3000.00, 'Equipment', DATE_SUB(NOW(), INTERVAL 4 DAY)),
(3, 2500.00, 'Feed', DATE_SUB(NOW(), INTERVAL 5 DAY));

-- Sample Loans
INSERT INTO loans (farmer_id, amount, status, requested_at, approved_at) VALUES
(1, 10000.00, 'approved', DATE_SUB(NOW(), INTERVAL 30 DAY), DATE_SUB(NOW(), INTERVAL 29 DAY)),
(2, 15000.00, 'pending', DATE_SUB(NOW(), INTERVAL 2 DAY), NULL),
(3, 20000.00, 'rejected', DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_SUB(NOW(), INTERVAL 14 DAY));

-- Sample Withdrawals
INSERT INTO withdrawals (farmer_id, amount, status, requested_at, processed_at, notes) VALUES
(1, 5000.00, 'approved', DATE_SUB(NOW(), INTERVAL 10 DAY), DATE_SUB(NOW(), INTERVAL 9 DAY), 'Monthly withdrawal'),
(2, 7500.00, 'pending', DATE_SUB(NOW(), INTERVAL 2 DAY), NULL, 'Emergency funds needed'),
(3, 10000.00, 'approved', DATE_SUB(NOW(), INTERVAL 20 DAY), DATE_SUB(NOW(), INTERVAL 19 DAY), 'Equipment purchase');
