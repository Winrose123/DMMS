# Dairy Management System (DMMS)

A comprehensive web-based system for managing dairy farm operations, including milk production recording, farmer management, and financial transactions.

## Features

1. Role-based Authentication
   - Admin and Farmer roles
   - Secure password hashing
   - Protected routes

2. Milk Production Management
   - Daily milk production recording
   - Image upload support for records
   - Production history tracking
   - Detailed statistics

3. Financial Management
   - Expense tracking for farmers
   - Loan request system
   - Withdrawal management
   - Monthly financial summaries

4. Farmer Management
   - Detailed farmer profiles
   - Production history
   - Financial records
   - Activity tracking

5. Reporting
   - Monthly production trends
   - Farmer activity distribution
   - Financial summaries
   - Export capabilities

## Technology Stack

- PHP 7.4+
- MySQL/MariaDB
- Bootstrap 5
- Font Awesome 5
- Chart.js
- PDO for database operations

## Installation

1. Clone the repository:
   ```bash
   git clone [your-repository-url]
   ```

2. Import the database schema:
   ```bash
   mysql -u your_username -p your_database < database/schema.sql
   ```

3. Configure your database connection:
   - Copy `config.example.php` to `config.php`
   - Update the database credentials

4. Set up the upload directories:
   ```bash
   mkdir -p uploads/milk_records
   chmod 755 uploads
   ```

5. Configure your web server:
   - Point your web root to the project directory
   - Ensure PHP has write permissions to the uploads directory

## Security Features

- Input sanitization
- Password hashing
- CSRF protection
- Secure session management
- Role-based access control

## Directory Structure

```
DMMS/
├── database/
│   ├── connection.php
│   └── schema.sql
├── partials/
│   ├── app-header-scripts.php
│   ├── app-scripts.php
│   └── app-sidebar.php
├── uploads/
│   └── milk_records/
├── config.php
├── index.php
└── [other PHP files]
```

## Usage

1. Admin Features:
   - Record milk production
   - Manage farmers
   - Process loan requests
   - Handle withdrawal requests
   - View reports and statistics

2. Farmer Features:
   - View production history
   - Track expenses
   - Request loans
   - Request withdrawals
   - View personal statistics

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.
