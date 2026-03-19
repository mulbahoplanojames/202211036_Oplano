# Installation Guide

## 🚀 Quick Start

Follow these steps to get your Curated Programming Tutorials Web Platform up and running.

## 📋 Prerequisites

### Required Software

- **PHP 7.0+** (recommended 7.4+)
- **MySQL 5.7+** or **MariaDB 10.2+**
- **Apache** (with mod_rewrite) or **Nginx**
- **Composer** (optional, for dependency management)

### System Requirements

- **RAM**: 512MB minimum (1GB recommended)
- **Storage**: 100MB minimum disk space
- **Network**: Internet connection for YouTube integration

## 🗄️ Database Setup

### Step 1: Create Database

```sql
-- Log in to MySQL as root
mysql -u root -p

-- Create database
CREATE DATABASE programming_tutorials;
```

### Step 2: Import Schema

```bash
# Navigate to project directory
cd /path/to/group-project

# Import the database schema
mysql -u root -p programming_tutorials < database/schema.sql
```

### Step 3: Verify Installation

```sql
-- Check tables were created
USE programming_tutorials;
SHOW TABLES;

-- Verify sample data
SELECT COUNT(*) FROM courses;
SELECT COUNT(*) FROM videos;
SELECT COUNT(*) FROM users;
```

## ⚙️ Configuration

### Step 1: Database Configuration

Edit `config/db.php`:

```php
<?php
class Database {
    private $host = "localhost";        // Your database host
    private $db_name = "programming_tutorials";  // Database name
    private $username = "root";       // Database username
    private $password = "your_password";  // Database password

    // ... rest of the file
}
?>
```

### Step 2: Web Server Configuration

#### Apache Configuration

1. Enable mod_rewrite:

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

2. Create virtual host:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/group-project

    <Directory /var/www/group-project>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

#### Nginx Configuration

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/group-project;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Step 3: File Permissions

```bash
# Set proper permissions
sudo chown -R www-data:www-data /var/www/group-project
sudo chmod -R 755 /var/www/group-project
sudo chmod -R 777 /var/www/group-project/assets  # For uploads if needed
```

## 🌐 Access the Application

### Default Login Credentials

- **Admin**: admin@tutorialplatform.com / admin123
- **Student**: Register new account

### Access URLs

- **Homepage**: http://yourdomain.com/
- **Admin Dashboard**: http://yourdomain.com/admin/dashboard.php
- **Student Dashboard**: http://yourdomain.com/student/dashboard.php
- **Login**: http://yourdomain.com/login.php
- **Register**: http://yourdomain.com/register.php

## 🔧 Testing the Installation

### Step 1: Basic Functionality Test

1. Open homepage in browser
2. Verify courses are displayed
3. Test course navigation
4. Check video cards and YouTube links

### Step 2: Authentication Test

1. Register new student account
2. Login as student
3. Access student dashboard
4. Logout and test admin login

### Step 3: Admin Features Test

1. Login as admin
2. Access admin dashboard
3. Add new course
4. Add video to course
5. Test course management

## 🐛 Common Issues and Solutions

### Issue 1: Blank White Page

**Cause**: PHP error or syntax issue
**Solution**: Enable error reporting in `index.php`:

```php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
```

### Issue 2: Database Connection Failed

**Cause**: Incorrect database credentials
**Solution**: Verify `config/db.php` settings:

```bash
# Test database connection
mysql -u username -p database_name
```

### Issue 3: 404 Not Found Errors

**Cause**: URL rewriting not working
**Solution**: Check Apache/Nginx configuration and .htaccess

### Issue 4: Permission Denied

**Cause**: File permissions issue
**Solution**: Set proper ownership and permissions:

```bash
sudo chown -R www-data:www-data /path/to/project
sudo chmod -R 755 /path/to/project
```

### Issue 5: Sessions Not Working

**Cause**: PHP session configuration
**Solution**: Check session save path and permissions:

```bash
# Check session save path
php -i | grep session.save_path

# Create directory if needed
sudo mkdir -p /var/lib/php/sessions
sudo chown www-data:www-data /var/lib/php/sessions
```

## 🔒 Security Configuration

### Step 1: Production Security

1. Change default admin password
2. Update database credentials
3. Disable PHP error display in production
4. Enable HTTPS/SSL certificate

### Step 2: File Security

```bash
# Protect sensitive files
<Files "config/db.php">
    Require all denied
</Files>

<Files "*.sql">
    Require all denied
</Files>
```

### Step 3: Database Security

```sql
-- Create dedicated database user
CREATE USER 'tutorialuser'@'localhost' IDENTIFIED BY 'strong_password';
GRANT SELECT, INSERT, UPDATE, DELETE ON programming_tutorials.* TO 'tutorialuser'@'localhost';
FLUSH PRIVILEGES;
```

## 📊 Performance Optimization

### Step 1: PHP Optimization

Edit `php.ini`:

```ini
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 64M
post_max_size = 64M
```

### Step 2: Database Optimization

```sql
-- Add indexes for better performance
CREATE INDEX idx_courses_active ON courses(is_active);
CREATE INDEX idx_videos_course ON videos(course_id);
CREATE INDEX idx_enrollments_user ON enrollments(user_id);
```

### Step 3: Caching

- Enable OPcache for PHP
- Configure browser caching headers
- Consider Redis for session storage

## 🚀 Deployment Options

### Option 1: Shared Hosting

1. Upload files via FTP
2. Import database via phpMyAdmin
3. Update database configuration
4. Set file permissions

### Option 2: VPS/Dedicated Server

1. Install LAMP/LEMP stack
2. Configure virtual hosts
3. Set up firewall rules
4. Enable SSL certificate

### Option 3: Docker Deployment

```dockerfile
FROM php:7.4-apache
RUN docker-php-ext-install pdo pdo_mysql
COPY . /var/www/html/
EXPOSE 80
```

### Option 4: Cloud Platform

- **AWS EC2**: Deploy on Amazon Linux
- **DigitalOcean**: Use LEMP droplet
- **Heroku**: Deploy with PHP buildpack

## 📞 Support Resources

### Documentation

- [README.md](README.md) - Project overview
- [Database Schema](database/schema.sql) - Database structure
- [API Documentation](API.md) - API endpoints (coming soon)

### Community

- GitHub Issues: Report bugs and request features
- Forums: Community discussion and support
- Wiki: Additional documentation and tutorials

### Troubleshooting

1. Check error logs: `/var/log/apache2/error.log`
2. Verify PHP version: `php -v`
3. Test database: `mysql -u user -p`
4. Check permissions: `ls -la`

---

## 🎉 You're Ready!

Your Curated Programming Tutorials Web Platform is now installed and ready to use!

**Next Steps:**

1. Customize the platform with your branding
2. Add your own curated YouTube tutorials
3. Promote to your learning community
4. Monitor analytics and user feedback

Happy coding! 🚀
