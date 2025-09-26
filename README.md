# Academic Attendance System

A comprehensive web-based attendance management system built with Laravel 12 that integrates face recognition technology for automated student attendance tracking in academic institutions.

## Features

### 🎯 Core Functionality

-   **Multi-Role Support**: Admin, Lecturer, and Student dashboards
-   **Face Recognition**: Advanced facial recognition for contactless attendance
-   **QR Code Integration**: Alternative attendance method using QR codes
-   **Real-time Tracking**: Live attendance monitoring and reporting
-   **Schedule Management**: Class schedule and timetable management

### 👥 User Roles

-   **Admin**: User management, system configuration, and comprehensive reporting
-   **Lecturer**: Class management, attendance monitoring, and student data access
-   **Student**: Face registration, attendance history, and schedule viewing

### 📊 Advanced Features

-   Attendance analytics and reporting
-   Face data update requests management
-   Semester and course management
-   Automated attendance calculations (present, absent, sick, permitted hours)
-   Permission-based access control using Spatie Laravel Permission

## Technology Stack

### Backend

-   **Framework**: Laravel 12
-   **Language**: PHP 8.2+
-   **Database**: MySQL/PostgreSQL
-   **Authentication**: Laravel's built-in authentication
-   **Permissions**: Spatie Laravel Permission

### Frontend

-   **CSS Framework**: Tailwind CSS 4.0
-   **Build Tool**: Vite 6.0
-   **JavaScript**: Modern ES6+ with Axios for API calls

### Additional Libraries

-   **QR Code Generation**: SimpleSoftwareIO Simple QR Code
-   **HTTP Client**: Guzzle HTTP
-   **Testing**: PHPUnit with Feature and Unit tests

## Installation

### Prerequisites

-   PHP 8.2 or higher
-   Composer
-   Node.js and npm
-   MySQL/PostgreSQL database

### Setup Instructions

1. **Clone the repository**

    ```bash
    git clone <repository-url>
    cd academic-attendance-system-app
    ```

2. **Install PHP dependencies**

    ```bash
    composer install
    ```

3. **Install Node.js dependencies**

    ```bash
    npm install
    ```

4. **Environment Configuration**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5. **Database Setup**

    - Configure your database credentials in `.env`
    - Run migrations and seeders:

    ```bash
    php artisan migrate --seed
    ```

6. **Build Frontend Assets**

    ```bash
    npm run build
    ```

7. **Start the Application**
    ```bash
    php artisan serve
    npm run dev
    ```

## Usage

### For Administrators

-   Manage users (students, lecturers, admins)
-   Configure class schedules and courses
-   Generate comprehensive attendance reports
-   Handle face data update requests

### For Lecturers

-   View and manage class schedules
-   Monitor student attendance in real-time
-   Generate attendance reports for their courses
-   Manually adjust attendance records when needed

### For Students

-   Register face data for recognition
-   Check in/out using face recognition or QR codes
-   View personal attendance history
-   Access class schedules

## API Integration

The system includes face recognition service integration for:

-   Face registration and enrollment
-   Real-time face verification during attendance
-   Face data updates and management

## Testing

Run the test suite:

```bash
php artisan test
```

The application includes both Feature and Unit tests to ensure reliability and functionality.

## Configuration

### Face Recognition Setup

Configure face recognition service endpoints and credentials in your `.env` file or service configuration.

### Permissions

The system uses role-based permissions. Default roles and permissions are seeded during installation.

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/new-feature`)
3. Commit your changes (`git commit -am 'Add new feature'`)
4. Push to the branch (`git push origin feature/new-feature`)
5. Create a Pull Request

## Security

-   All face data is securely stored and encrypted
-   Role-based access control ensures data privacy
-   Input validation and sanitization prevent security vulnerabilities
-   Regular security updates and dependency maintenance

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and questions, please open an issue in the repository or contact the development team.

---

_Built with ❤️ for modern educational institutions seeking efficient attendance management solutions._
