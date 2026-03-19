# Curated Programming Tutorials Web Platform

A full-stack web application that curates and recommends high-quality programming tutorials from YouTube, offering a focused and distraction-free learning experience.

## 🚀 Features

### Core Features
- **Homepage**: Browse programming courses with clean, user-friendly layout
- **Course Selection**: View curated YouTube tutorials for each programming language
- **Video Curation**: Only videos with 1M+ views and educational value
- **YouTube Integration**: Direct redirection to YouTube videos
- **Database Storage**: MySQL backend for courses, videos, users, and enrollments

### Admin Dashboard
- **Secure Authentication**: Role-based access control
- **Course Management**: CRUD operations for programming courses
- **Video Management**: Add, edit, and delete YouTube tutorials
- **User Management**: Monitor student enrollments and progress
- **System Analytics**: View platform statistics and metrics

### Student Dashboard
- **User Registration**: Secure student account creation
- **Course Enrollment**: Enroll in multiple programming courses
- **Progress Tracking**: Monitor learning progress across courses
- **Favorite Videos**: Save and access favorite tutorials
- **Learning History**: Track recently watched videos

## 🛠️ Technology Stack

### Frontend
- **HTML5**: Semantic markup structure
- **CSS3**: Responsive design with modern UI components
- **JavaScript**: Interactive features and animations
- **Responsive Design**: Mobile-friendly interface

### Backend
- **PHP 7+**: Server-side logic and API endpoints
- **MySQL**: Database management and storage
- **PDO**: Secure database interactions
- **Session Management**: User authentication and authorization

### Security Features
- **CSRF Protection**: Cross-site request forgery prevention
- **Password Hashing**: Secure password storage with bcrypt
- **Input Sanitization**: XSS and SQL injection prevention
- **Role-Based Access**: Admin and user role separation

## 📁 Project Structure

```
group-project/
├── database/
│   └── schema.sql              # Database schema and sample data
├── config/
│   └── database.php           # Database configuration
├── includes/
│   └── functions.php          # Common functions and utilities
├── admin/
│   ├── dashboard.php          # Admin dashboard
│   ├── courses.php            # Course management
│   ├── add_course.php         # Add new course
│   ├── videos.php             # Video management
│   └── users.php              # User management
├── student/
│   ├── dashboard.php          # Student dashboard
│   ├── favorites.php          # Favorite videos
│   ├── profile.php            # User profile
│   └── history.php            # Learning history
├── assets/
│   └── css/
│       └── style.css          # Main stylesheet
├── index.php                  # Homepage
├── courses.php                # All courses listing
├── course.php                 # Individual course page
├── login.php                  # User login
├── register.php               # User registration
├── logout.php                 # User logout
└── README.md                  # This file
```

## 🗄️ Database Schema

### Tables
- **users**: User accounts and authentication
- **courses**: Programming courses information
- **videos**: YouTube video metadata
- **enrollments**: Student course enrollments
- **user_favorites**: User favorite videos
- **video_progress**: Video watching progress

### Sample Data
The database includes:
- 5 sample programming courses (Python, Java, JavaScript, PHP, C++)
- 10 curated YouTube tutorials
- Admin account for testing
- Sample enrollment data

## 🚀 Installation

### Prerequisites
- PHP 7.0 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- Web browser with JavaScript enabled

### Setup Instructions

1. **Database Setup**
   ```bash
   # Import the database schema
   mysql -u root -p < database/schema.sql
   ```

2. **Configuration**
   - Update database credentials in `config/database.php`
   - Ensure proper file permissions for web server

3. **Web Server Configuration**
   - Point document root to project directory
   - Enable PHP module
   - Configure .htaccess if using Apache

4. **Access the Application**
   - Open browser to `http://localhost/group-project`
   - Register as student or login as admin

### Default Admin Account
- **Email**: admin@tutorialplatform.com
- **Password**: admin123

## 🎯 Usage Guide

### For Students
1. **Registration**: Create account with email and password
2. **Browse Courses**: Explore available programming courses
3. **Enroll**: Join courses to track progress
4. **Watch Videos**: Click on video cards to view tutorials
5. **Track Progress**: Monitor learning in student dashboard

### For Administrators
1. **Login**: Use admin credentials to access dashboard
2. **Manage Courses**: Add, edit, or deactivate courses
3. **Curate Videos**: Add high-quality YouTube tutorials
4. **Monitor Users**: View student enrollments and activity
5. **Analytics**: Review platform statistics

## 🔧 Configuration Options

### Database Settings
```php
// config/database.php
private $host = "localhost";
private $db_name = "programming_tutorials";
private $username = "root";
private $password = "";
```

### YouTube API Integration
The platform is designed to integrate with YouTube API for:
- Automatic video metadata retrieval
- View count and engagement metrics
- Channel information
- Thumbnail images

### Customization Options
- **Themes**: Modify CSS variables for color schemes
- **Languages**: Add new programming languages
- **Filters**: Implement additional search filters
- **Features**: Extend with additional learning features

## 🌟 Key Features Highlight

### Distraction-Free Learning
- No YouTube recommendations or sidebar distractions
- Focused course-based learning paths
- Clean, minimal interface design

### Quality Curation
- Manual selection of high-quality tutorials
- Minimum 1M views requirement
- Educational value assessment

### Progress Tracking
- Course enrollment management
- Video watching progress
- Learning statistics and analytics

### Responsive Design
- Mobile-friendly interface
- Tablet and desktop optimization
- Touch-friendly interactions

## 🔒 Security Considerations

- **Input Validation**: All user inputs are sanitized
- **SQL Injection Prevention**: Using prepared statements
- **XSS Protection**: Output encoding and CSP headers
- **CSRF Protection**: Token-based request validation
- **Password Security**: Bcrypt hashing for passwords

## 🚀 Future Enhancements

### Planned Features
- **YouTube API Integration**: Real-time video data
- **Advanced Search**: Full-text search capabilities
- **Ratings System**: User ratings and reviews
- **Dark Mode**: Theme switching functionality
- **Mobile App**: Native mobile application
- **Live Chat**: Real-time student interaction
- **Certificates**: Course completion certificates

### API Endpoints
- RESTful API for mobile applications
- Webhook integrations for notifications
- Third-party learning management system integration

## 📞 Support

For issues, questions, or contributions:
- Create GitHub issues for bug reports
- Submit pull requests for feature enhancements
- Contact development team for support

## 📄 License

This project is open source and available under the MIT License.

---

**Built with ❤️ for the programming community**
