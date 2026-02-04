🎮 GameMGT – Laravel + SQL Server Setup Guide

This project is built using Laravel with Microsoft SQL Server as the database and runs locally using XAMPP (Apache + PHP).

🧰 Tech Stack

PHP 8.3 (XAMPP)

Laravel 10+

Microsoft SQL Server 2022

SQL Server Management Studio (SSMS)

Composer

Apache (XAMPP)

📋 Prerequisites

Make sure the following are installed before proceeding:

XAMPP (PHP 8.3, Thread Safe)

Composer (latest)

Microsoft SQL Server (2019/2022 – Developer Edition)

SQL Server Management Studio (SSMS)

VC++ Redistributable (x64)

🚀 Installation Steps
1️⃣ Clone the Repository
git clone <your-repo-url>
cd gamemgt

2️⃣ Install PHP Dependencies
composer install

3️⃣ Environment Configuration

Copy the example environment file:

copy .env.example .env


Generate the application key:

php artisan key:generate

4️⃣ Create Database in SQL Server (SSMS)

Navigation:

SSMS
 → Connect to Server
 → Object Explorer
 → Right-click Databases
 → New Database…


Database name:

gamemgt


Click OK.

5️⃣ Create SQL Login for Laravel (IMPORTANT)

Laravel must use SQL authentication, not Windows authentication.

Navigation:

Server
 → Security
 → Logins
 → Right-click → New Login


Details:

Login name: laravel_user

Authentication: SQL Server Authentication

Password: StrongPassword@123

Default database: gamemgt

Uncheck: Enforce password policy

User Mapping tab:

Check gamemgt

Role: db_owner

6️⃣ Configure .env for SQL Server

Update your .env file:

DB_CONNECTION=sqlsrv
DB_HOST=127.0.0.1
DB_PORT=1433
DB_DATABASE=gamemgt
DB_USERNAME=laravel_user
DB_PASSWORD=StrongPassword@123


⚠️ Do not use localhost
⚠️ Do not use Windows Authentication

7️⃣ Enable SQL Server TCP/IP

Navigation:

SQL Server Configuration Manager
 → SQL Server Network Configuration
 → Protocols for MSSQLSERVER


Enable TCP/IP

Open TCP/IP → Properties → IP Addresses

Under IPAll:

TCP Dynamic Ports: (empty)

TCP Port: 1433

Restart:

SQL Server (MSSQLSERVER)

8️⃣ Install SQL Server PHP Drivers

Download from Microsoft:

php_sqlsrv_83_ts_x64.dll

php_pdo_sqlsrv_83_ts_x64.dll

Place them in:

C:\xampp\php\ext


Edit php.ini:

extension=php_sqlsrv_83_ts_x64
extension=php_pdo_sqlsrv_83_ts_x64


Restart Apache.

9️⃣ Verify Driver Installation
php -m | findstr sqlsrv


Expected output:

sqlsrv
pdo_sqlsrv

🔟 Run Migrations

Clear config cache:

php artisan config:clear
php artisan cache:clear


Run migrations:

php artisan migrate

✅ Application Ready

Start the server:

php artisan serve


Open:

http://127.0.0.1:8000

🛠 Common Errors & Fixes
❌ Error: Unable to load dynamic library 'sqlsrv'

Cause:

Wrong DLL (NTS instead of TS)

Missing VC++ runtime

Fix:

Use TS x64 DLLs only

Install VC++ Redistributable (x64)

Restart Apache

❌ Error: No connection could be made because the target machine actively refused it

Cause:

TCP/IP disabled in SQL Server

Fix:

Enable TCP/IP

Set port 1433

Restart SQL Server

❌ Error: Login failed. The login is from an untrusted domain

Cause:

Windows Authentication used in Laravel

Fix:

Create SQL login

Use username/password in .env

❌ Error: SQLSTATE[08001] [Microsoft][ODBC Driver 18]

Cause:

Wrong host or encryption mismatch

Fix:

DB_HOST=127.0.0.1

📌 Notes

SQL Server + Laravel works best with SQL Authentication

Always clear config cache after .env changes

Apache (XAMPP) must be restarted after PHP config changes
