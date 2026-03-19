# Course Management Web Application

A comprehensive Course Management System with Role-Based Access Control (RBAC) built with PHP, MySQL, HTML, and CSS.

## Features

### User Authentication
- User registration with role selection (Admin/Student)
- Secure login system with session management
- Password hashing with PHP's password_hash()
- CSRF protection for all forms
- Role-based redirection after login

### Role-Based Access Control (RBAC)
- **Administrator Role**: Full access to course management
  - Create, Read, Update, Delete (CRUD) operations for courses
  - View all users and their enrollments
  - Access to admin dashboard with system statistics
- **Student Role**: Limited access focused on learning
  - Browse available courses
  - Enroll/unenroll from courses
  - View personal dashboard with enrolled courses
  - Track course progress and grades

### Course Management
- Course creation with detailed information
- Course enrollment limits and availability tracking
- Course status management (upcoming, active, completed)
- Instructor assignment and course scheduling
- Real-time enrollment statistics

### User Experience
- Responsive design that works on all devices
- Modern, clean interface with gradient styling
- Interactive course cards with hover effects
- Alert system for user feedback
- Intuitive navigation and user flows

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3
- **Security**: PDO for database operations, prepared statements, CSRF tokens

## Database Design

### Database Name
`course_management`

### Table Structure

#### 1. users Table
Stores user account information and roles.

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_role (role)
);
```

**Columns:**
- `id`: Primary key
- `username`: Unique username for login
- `email`: Unique email address
- `password_hash`: Hashed password using PHP's password_hash()
- `full_name`: User's full name
- `role`: User role ('admin' or 'user')
- `created_at`: Account creation timestamp
- `updated_at`: Last update timestamp

#### 2. courses Table
Stores course information and metadata.

```sql
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    instructor VARCHAR(100),
    duration_weeks INT DEFAULT 8,
    max_students INT DEFAULT 50,
    current_enrollments INT DEFAULT 0,
    start_date DATE,
    end_date DATE,
    status ENUM('upcoming', 'active', 'completed') DEFAULT 'upcoming',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_start_date (start_date)
);
```

**Columns:**
- `id`: Primary key
- `title`: Course title
- `description`: Detailed course description
- `instructor`: Instructor name
- `duration_weeks`: Course duration in weeks
- `max_students`: Maximum number of students
- `current_enrollments`: Current number of enrolled students
- `start_date`: Course start date
- `end_date`: Course end date
- `status`: Course status ('upcoming', 'active', 'completed')
- `created_by`: Foreign key to users table (admin who created course)
- `created_at`: Course creation timestamp
- `updated_at`: Last update timestamp

#### 3. enrollments Table
Junction table linking users to courses.

```sql
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('active', 'completed', 'dropped') DEFAULT 'active',
    grade DECIMAL(5,2) DEFAULT NULL,
    UNIQUE KEY unique_enrollment (user_id, course_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_course_id (course_id),
    INDEX idx_status (status)
);
```

**Columns:**
- `id`: Primary key
- `user_id`: Foreign key to users table
- `course_id`: Foreign key to courses table
- `enrollment_date`: When the user enrolled
- `status`: Enrollment status ('active', 'completed', 'dropped')
- `grade`: Final grade (nullable)
- `unique_enrollment`: Ensures a user can only enroll once per course

### Table Relationships

```
users (1) -----> (N) enrollments (N) <----- (1) courses
   |                                                   |
   |                                                   |
   +------------------- created_by --------------------+
```

- A user can have many enrollments
- A course can have many enrollments
- A user can only be enrolled once per course (unique constraint)
- An admin can create many courses (created_by relationship)

## Installation Guide

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache, Nginx, or PHP built-in server)

### Step 1: Database Setup

1. Create a MySQL database named `course_management`
2. Import the provided SQL schema:

```bash
mysql -u root -p course_management < database_setup.sql
```

Or execute the SQL commands manually from the `database_setup.sql` file.

### Step 2: Configuration

1. Update database credentials in `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'course_management');
define('DB_USER', 'your_mysql_username');
define('DB_PASS', 'your_mysql_password');
```

### Step 3: Web Server Setup

#### Option A: Apache/Nginx
1. Place the project files in your web server's document root
2. Ensure the `public` directory is accessible via web browser
3. Configure URL rewriting if needed for clean URLs

#### Option B: PHP Built-in Server
```bash
cd public
php -S localhost:8000
```

### Step 4: Access the Application

1. Open your web browser and navigate to the application URL
2. Register a new account or use the default admin credentials:
   - Username: `admin`
   - Password: `admin123`

## Project Structure

```
class_project_01/
├── admin/                      # Admin-specific pages
│   ├── dashboard.php          # Admin dashboard
│   ├── courses.php            # Course management (CRUD)
│   └── users.php              # User management
├── auth/                       # Authentication pages
│   ├── login.php              # User login
│   ├── register.php           # User registration
│   └── logout.php             # User logout
├── config/                     # Configuration files
│   └── database.php           # Database connection settings
├── courses/                    # Course-related pages
│   ├── index.php              # Course listing
│   ├── enroll.php             # Course enrollment
│   └── unenroll.php           # Course unenrollment
├── includes/                   # Shared functionality
│   ├── functions.php          # Helper functions
│   └── auth_middleware.php    # Authentication middleware
├── public/                     # Public entry point
│   └── index.php              # Main entry point with role redirection
├── user/                       # User-specific pages
│   └── dashboard.php          # Student dashboard
├── assets/                     # Static assets
│   └── css/
│       └── style.css          # Main stylesheet
├── database_setup.sql          # Database schema and sample data
└── README.md                   # This file
```

## Security Features

1. **Password Security**: All passwords are hashed using PHP's `password_hash()` function
2. **SQL Injection Prevention**: All database queries use prepared statements with PDO
3. **CSRF Protection**: All forms include CSRF tokens to prevent cross-site request forgery
4. **Session Security**: Secure session configuration with HTTP-only cookies
5. **Input Validation**: All user inputs are sanitized and validated
6. **Role-Based Access**: Proper access control ensures users can only access authorized pages

## Default Users

After running the database setup script, the following default users are created:

### Administrator
- **Username**: admin
- **Email**: admin@coursemgmt.com
- **Password**: admin123
- **Role**: Administrator

### Sample Students
- **Username**: student1
- **Email**: student1@example.com
- **Password**: password
- **Role**: Student

- **Username**: student2
- **Email**: student2@example.com
- **Password**: password
- **Role**: Student

- **Username**: student3
- **Email**: student3@example.com
- **Password**: password
- **Role**: Student

## Usage Instructions

### For Administrators
1. Login with admin credentials
2. Access the admin dashboard to view system statistics
3. Create, edit, and delete courses using the course management interface
4. View all users and their enrollment statistics
5. Monitor course enrollment limits and availability

### For Students
1. Register a new account or login with existing credentials
2. Browse available courses on the courses page
3. Enroll in courses of interest
4. View enrolled courses on the personal dashboard
5. Track course progress and manage enrollments

## API Endpoints

The application follows a traditional web application pattern rather than REST API. All interactions are handled through PHP pages with proper HTTP methods:

- `GET /public/index.php` - Main entry point with role-based redirection
- `GET/POST /auth/login.php` - User authentication
- `GET/POST /auth/register.php` - User registration
- `GET /auth/logout.php` - User logout
- `GET /admin/dashboard.php` - Admin dashboard (admin only)
- `GET/POST /admin/courses.php` - Course management (admin only)
- `GET /admin/users.php` - User management (admin only)
- `GET /user/dashboard.php` - Student dashboard (user only)
- `GET /courses/index.php` - Course listing (public)
- `GET/POST /courses/enroll.php` - Course enrollment (authenticated users)
- `GET/POST /courses/unenroll.php` - Course unenrollment (authenticated users)

## Browser Compatibility

The application is compatible with all modern browsers:
- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is for educational purposes. Feel free to use and modify as needed for learning and development.

## Support

For any questions or issues, please refer to the code comments and this documentation.
