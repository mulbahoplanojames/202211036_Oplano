# School Management API

```
cd /path/to/demo_01
php -S localhost:8000
php -S localhost:8000 -t public
```

A simple RESTful API for managing users, courses, and marks in a school system.

## Setup

1. **Database Setup**
   - Import the `setup_database.sql` file into your MySQL database
   - Update database credentials in `config/database.php` if needed

2. **Web Server**
   - Place this project in your web server's document root (e.g., htdocs, www)
   - Ensure mod_rewrite is enabled for clean URLs

## API Endpoints

### Users API

| Method | Endpoint          | Description       |
| ------ | ----------------- | ----------------- |
| GET    | `/api/users`      | Get all users     |
| GET    | `/api/users/{id}` | Get specific user |
| POST   | `/api/users`      | Create new user   |
| PUT    | `/api/users/{id}` | Update user       |
| DELETE | `/api/users/{id}` | Delete user       |

#### User Data Format

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "1234567890"
}
```

### Courses API

| Method | Endpoint            | Description         |
| ------ | ------------------- | ------------------- |
| GET    | `/api/courses`      | Get all courses     |
| GET    | `/api/courses/{id}` | Get specific course |
| POST   | `/api/courses`      | Create new course   |
| PUT    | `/api/courses/{id}` | Update course       |
| DELETE | `/api/courses/{id}` | Delete course       |

#### Course Data Format

```json
{
  "course_name": "Mathematics",
  "course_code": "MATH101",
  "description": "Basic Mathematics Course",
  "credits": 4
}
```

### Marks API

| Method | Endpoint          | Description                                     |
| ------ | ----------------- | ----------------------------------------------- |
| GET    | `/api/marks`      | Get all marks (with student and course details) |
| GET    | `/api/marks/{id}` | Get specific mark                               |
| POST   | `/api/marks`      | Create new mark                                 |
| PUT    | `/api/marks/{id}` | Update mark                                     |
| DELETE | `/api/marks/{id}` | Delete mark                                     |

#### Mark Data Format

```json
{
  "user_id": 1,
  "course_id": 1,
  "mark": 85.5,
  "semester": "Fall",
  "academic_year": "2023-2024"
}
```

## Grade Calculation

The system automatically calculates grades based on marks:

- 90-100: A
- 85-89: A-
- 80-84: B+
- 75-79: B
- 70-74: B-
- 65-69: C+
- 60-64: C
- 55-59: C-
- 50-54: D
- Below 50: F

## Testing with Postman

### Sample Requests

1. **Get All Users**
   - Method: GET
   - URL: `http://localhost/your-project-path/api/users`

2. **Create New User**
   - Method: POST
   - URL: `http://localhost/your-project-path/api/users`
   - Body (raw, JSON):

   ```json
   {
     "name": "Alice Johnson",
     "email": "alice@example.com",
     "phone": "5551234567"
   }
   ```

3. **Get All Courses**
   - Method: GET
   - URL: `http://localhost/your-project-path/api/courses`

4. **Create New Course**
   - Method: POST
   - URL: `http://localhost/your-project-path/api/courses`
   - Body (raw, JSON):

   ```json
   {
     "course_name": "Biology",
     "course_code": "BIO101",
     "description": "Introduction to Biology",
     "credits": 3
   }
   ```

5. **Create New Mark**
   - Method: POST
   - URL: `http://localhost/your-project-path/api/marks`
   - Body (raw, JSON):
   ```json
   {
     "user_id": 1,
     "course_id": 1,
     "mark": 92.0,
     "semester": "Spring",
     "academic_year": "2023-2024"
   }
   ```

## Response Format

All API responses are in JSON format:

**Success Response:**

```json
{
  "message": "Operation completed successfully"
}
```

**Data Response:**

```json
{
  "records": [
    {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "created_at": "2023-01-01 12:00:00"
    }
  ]
}
```

**Error Response:**

```json
{
  "message": "Error description"
}
```

## HTTP Status Codes

- 200: OK
- 201: Created
- 400: Bad Request
- 404: Not Found
- 405: Method Not Allowed
- 503: Service Unavailable

## How to Run the Application

### Option 1: Using XAMPP/MAMP/WAMP (Recommended for Beginners)

1. **Install XAMPP** (if not already installed)
   - Download from https://www.apachefriends.org/
   - Install and start Apache and MySQL services

2. **Setup Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Create a new database named `school_api`
   - Import the `setup_database.sql` file:
     - Click on the `school_api` database
     - Click "Import" tab
     - Choose the `setup_database.sql` file
     - Click "Go"

3. **Deploy the Application**
   - Copy the entire `demo_01` folder to:
     - XAMPP: `C:/xampp/htdocs/` (Windows) or `/Applications/XAMPP/htdocs/` (Mac)
     - MAMP: `/Applications/MAMP/htdocs/` (Mac)
     - WAMP: `C:/wamp64/www/` (Windows)

4. **Test the APIs**
   - Base URL: `http://localhost/demo_01/`
   - Test endpoints: `http://localhost/demo_01/api/users`, etc.

### Option 2: Using PHP Built-in Server

1. **Setup Database** (same as above)

2. **Start PHP Server**

   ```bash
   cd /path/to/demo_01
   php -S localhost:8000
   ```

3. **Test the APIs**
   - Base URL: `http://localhost:8000/`
   - Test endpoints: `http://localhost:8000/api/users`, etc.

### Option 3: Using Docker

1. **Create Dockerfile** (if you want containerized deployment)
2. **Build and run container**
3. **Map ports and volumes**

## Complete API Routes List

### Users Routes

```
GET    /api/users           - Get all users
GET    /api/users/{id}      - Get specific user by ID
POST   /api/users           - Create new user
PUT    /api/users/{id}      - Update existing user
DELETE /api/users/{id}      - Delete user
```

### Courses Routes

```
GET    /api/courses         - Get all courses
GET    /api/courses/{id}    - Get specific course by ID
POST   /api/courses         - Create new course
PUT    /api/courses/{id}    - Update existing course
DELETE /api/courses/{id}    - Delete course
```

### Marks Routes

```
GET    /api/marks           - Get all marks (with student/course details)
GET    /api/marks/{id}      - Get specific mark by ID
POST   /api/marks           - Create new mark
PUT    /api/marks/{id}      - Update existing mark
DELETE /api/marks/{id}      - Delete mark
```

## Quick Testing Commands

You can use curl to quickly test the APIs:

```bash
# Get all users
curl -X GET http://localhost/demo_01/api/users

# Create new user
curl -X POST http://localhost/demo_01/api/users \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com","phone":"1234567890"}'

# Get all courses
curl -X GET http://localhost/demo_01/api/courses

# Create new mark
curl -X POST http://localhost/demo_01/api/marks \
  -H "Content-Type: application/json" \
  -d '{"user_id":1,"course_id":1,"mark":85.5,"semester":"Fall","academic_year":"2023-2024"}'
```

## Troubleshooting

- **404 Errors**: Check if .htaccess is working and mod_rewrite is enabled
- **Database Connection**: Verify MySQL credentials in `config/database.php`
- **CORS Issues**: The APIs include CORS headers, but check your browser console
- **Permission Errors**: Ensure web server has read permissions on all files

## Sample Data

The database setup includes sample data for testing:

- 3 users
- 4 courses
- 6 marks

You can use this data to test all CRUD operations immediately after setup.
