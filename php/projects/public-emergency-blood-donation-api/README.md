# Public Emergency Blood Donation API

A RESTful API for locating blood donors and blood banks during medical emergencies.

## Overview

This API helps hospitals, healthcare institutions, and NGOs quickly find available blood donors in emergency situations. It provides real-time access to donor information including blood type, location, and contact details.

## Base URL

```
https://your-domain.com/
```

## Endpoints

### 1. Get API Information

```
GET /
```

Returns API information and available endpoints.

### 2. Register Blood Donor

```
POST /api/donors
```

Register a new blood donor in the system.

**Request Body:**

```json
{
  "name": "John Doe",
  "blood_type": "O+",
  "city": "Kigali",
  "phone": "+250788123456",
  "last_donation_date": "2024-01-15"
}
```

**Response:**

```json
{
  "status": "success",
  "message": "Donor registered successfully",
  "data": {
    "id": 1
  }
}
```

### 3. Get All Donors

```
GET /api/donors
```

Retrieve all registered donors.

**Response:**

```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "blood_type": "O+",
      "city": "Kigali",
      "phone": "+250788123456",
      "last_donation_date": "2024-01-15"
    }
  ]
}
```

### 4. Search Donors by Blood Type

```
GET /api/donors?blood_type=O+
```

Filter donors by specific blood type.

**Response:**

```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "blood_type": "O+",
      "city": "Kigali",
      "phone": "+250788123456",
      "last_donation_date": "2024-01-15"
    }
  ]
}
```

### 5. Search Donors by Location

```
GET /api/donors?city=Kigali
```

Filter donors by city.

**Response:**

```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "blood_type": "O+",
      "city": "Kigali",
      "phone": "+250788123456",
      "last_donation_date": "2024-01-15"
    }
  ]
}
```

### 6. Get Emergency Donors

```
GET /api/emergency-donors
```

Retrieve donors who are immediately available for donation (haven't donated in the last 56 days).

**Response:**

```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "John Doe",
      "blood_type": "O+",
      "city": "Kigali",
      "phone": "+250788123456",
      "last_donation_date": null
    }
  ]
}
```

### 7. Delete Donor (Admin)

```
DELETE /api/donors?id=1
```

Remove a donor from the system (Admin functionality).

**Response:**

```json
{
  "status": "success",
  "message": "Donor deleted successfully"
}
```

## Database Schema

### Donors Table

| Field              | Type               | Description                                   |
| ------------------ | ------------------ | --------------------------------------------- |
| id                 | INT AUTO_INCREMENT | Unique identifier                             |
| name               | VARCHAR(255)       | Donor's full name                             |
| blood_type         | VARCHAR(10)        | Blood type (A+, A-, B+, B-, AB+, AB-, O+, O-) |
| city               | VARCHAR(100)       | City of residence                             |
| phone              | VARCHAR(20)        | Contact phone number                          |
| last_donation_date | DATE               | Date of last blood donation                   |
| created_at         | TIMESTAMP          | Record creation time                          |
| updated_at         | TIMESTAMP          | Last update time                              |

## Setup Instructions

### 1. Database Setup

1. Create a MySQL database named `blood_donation_db`
2. Import the `database/schema.sql` file to create the donors table
3. Update database credentials in `config/database.php`

### 2. Web Server Configuration

1. Place files in your web server's document root
2. Ensure mod_rewrite is enabled for clean URLs
3. Configure PHP to allow CORS requests

### 3. Testing

#### Local Development

Use the router script for local testing with PHP built-in server:

```bash
# Start server with router
php -S localhost:8000 router.php

# Test endpoints
curl -X GET "http://localhost:8000/api/donors"
curl -X GET "http://localhost:8000/api/emergency-donors"
```

#### Production Testing

Use Postman or curl to test the API endpoints on Apache servers:

```bash
# Get all donors
curl -X GET "http://localhost/api/donors"

# Register a new donor
curl -X POST "http://localhost/api/donors" \
  -H "Content-Type: application/json" \
  -d '{"name":"Jane Smith","blood_type":"A+","city":"Kigali","phone":"+250788234567"}'

# Get emergency donors
curl -X GET "http://localhost/api/emergency-donors"
```

## Error Handling

The API returns appropriate HTTP status codes:

- `200` - Success
- `201` - Created
- `400` - Bad Request
- `404` - Not Found
- `405` - Method Not Allowed
- `500` - Internal Server Error

Error responses follow this format:

```json
{
  "status": "error",
  "message": "Error description"
}
```

## Security Considerations

- Input validation and sanitization
- SQL injection prevention using prepared statements
- CORS configuration for cross-origin requests
- Rate limiting should be implemented in production

## Deployment Options

### Render

1. Create a new Web Service on Render
2. Connect your GitHub repository
3. Set build command: `echo "No build required"`
4. Set start command: `php -S 0.0.0.0:10000`
5. Add environment variables for database credentials

### Railway

1. Create a new project on Railway
2. Connect your GitHub repository
3. Configure PHP environment
4. Set up MySQL add-on
5. Update database connection with Railway credentials

### Heroku

1. Install Heroku CLI
2. Create a new Heroku app
3. Add PHP buildpack
4. Add JawsDB MySQL add-on
5. Deploy using Git

## API Usage Examples

### JavaScript (Fetch API)

```javascript
// Get all donors
fetch("/api/donors")
  .then((response) => response.json())
  .then((data) => console.log(data));

// Register new donor
fetch("/api/donors", {
  method: "POST",
  headers: {
    "Content-Type": "application/json",
  },
  body: JSON.stringify({
    name: "John Doe",
    blood_type: "O+",
    city: "Kigali",
    phone: "+250788123456",
  }),
})
  .then((response) => response.json())
  .then((data) => console.log(data));
```

### Python (requests)

```python
import requests

# Get emergency donors
response = requests.get('https://your-api.com/api/emergency-donors')
donors = response.json()
print(donors)

# Register new donor
donor_data = {
    'name': 'Jane Smith',
    'blood_type': 'A+',
    'city': 'Kigali',
    'phone': '+250788234567'
}
response = requests.post('https://your-api.com/api/donors', json=donor_data)
print(response.json())
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is open source and available under the MIT License.

## Contact

For support or inquiries, please contact the development team.

---

**Note**: This API is designed to save lives by improving emergency response for blood shortages. Please use responsibly and ensure donor privacy and consent.
