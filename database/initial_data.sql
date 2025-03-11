-- Admin user (password: admin123)
INSERT INTO users (first_name, last_name, email, password, role) VALUES 
('Admin', 'User', 'admin@dmms.com', '$2y$10$RVwpB3/zMjrMVEVwNQD0k.WGWrGsxuL5uHjCY.LhI1SqMYOQTjVPa', 'admin');

-- Farmer user (password: farmer123)
INSERT INTO users (first_name, last_name, email, password, role) VALUES 
('John', 'Doe', 'farmer@dmms.com', '$2y$10$RVwpB3/zMjrMVEVwNQD0k.WGWrGsxuL5uHjCY.LhI1SqMYOQTjVPa', 'farmer');

-- Create farmer profile for the farmer user
INSERT INTO farmers (user_id, phone, location) 
SELECT id, '+254712345678', 'Nairobi' FROM users WHERE email = 'farmer@dmms.com';

-- Add some milk records for the farmer
INSERT INTO milk_records (farmer_id, quantity_liters, price_per_liter, created_at)
SELECT 
    f.id,
    ROUND(RAND() * (50 - 20) + 20, 2), -- Random quantity between 20-50 liters
    60.00, -- Fixed price per liter
    DATE_SUB(NOW(), INTERVAL ROUND(RAND() * 30) DAY) -- Random date within last 30 days
FROM farmers f
JOIN users u ON f.user_id = u.id
WHERE u.email = 'farmer@dmms.com'
UNION ALL
SELECT 
    f.id,
    ROUND(RAND() * (50 - 20) + 20, 2),
    60.00,
    DATE_SUB(NOW(), INTERVAL ROUND(RAND() * 30) DAY)
FROM farmers f
JOIN users u ON f.user_id = u.id
WHERE u.email = 'farmer@dmms.com'
UNION ALL
SELECT 
    f.id,
    ROUND(RAND() * (50 - 20) + 20, 2),
    60.00,
    DATE_SUB(NOW(), INTERVAL ROUND(RAND() * 30) DAY)
FROM farmers f
JOIN users u ON f.user_id = u.id
WHERE u.email = 'farmer@dmms.com';

-- Add some test loan requests
INSERT INTO loans (farmer_id, amount, status, requested_at)
SELECT 
    f.id,
    25000.00,
    'pending',
    DATE_SUB(NOW(), INTERVAL 2 DAY)
FROM farmers f
JOIN users u ON f.user_id = u.id
WHERE u.email = 'farmer@dmms.com'
UNION ALL
SELECT 
    f.id,
    15000.00,
    'approved',
    DATE_SUB(NOW(), INTERVAL 15 DAY)
FROM farmers f
JOIN users u ON f.user_id = u.id
WHERE u.email = 'farmer@dmms.com'
UNION ALL
SELECT 
    f.id,
    10000.00,
    'rejected',
    DATE_SUB(NOW(), INTERVAL 30 DAY)
FROM farmers f
JOIN users u ON f.user_id = u.id
WHERE u.email = 'farmer@dmms.com';
