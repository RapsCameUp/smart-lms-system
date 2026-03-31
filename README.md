# Smart LMS System

## Project Description

Smart LMS is an AI-powered adaptive learning management system designed specifically for South African educational institutions. The system provides personalized learning experiences, intelligent tutoring, analytics, and comprehensive dashboards for students, teachers, parents, and lecturers. It supports multilingual content including isiZulu, isiXhosa, Afrikaans, and English to cater to diverse learners.

Key features include:
- AI-powered adaptive learning recommendations
- Real-time analytics and progress tracking
- Interactive course management
- Leaderboards and gamification
- Role-based dashboards for different user types
- Multilingual support

## System Requirements

### Frontend Layer
- Modern web browser (Chrome, Firefox, Safari, Edge)
- Internet connection for CDN resources (Bootstrap, GSAP, etc.)
- Responsive design support (mobile and desktop)

### Backend Layer
- PHP 7.4 or higher
- Apache web server
- XAMPP (recommended for development)

### Database Layer
- MySQL 5.7 or higher
- phpMyAdmin (included with XAMPP)

### Hardware Requirements
- Minimum 4GB RAM
- 500MB free disk space
- Windows/Linux/Mac OS

## Setup Instructions

### Frontend Setup
1. Ensure a modern web browser is installed
2. No additional setup required - the application uses CDN links for external libraries

### Backend Setup
1. Install XAMPP from https://www.apachefriends.org/
2. Start Apache and MySQL services from XAMPP control panel
3. Place the project files in `C:\xampp\htdocs\smart-lms-system\` (Windows) or `/opt/lampp/htdocs/smart-lms-system/` (Linux/Mac)

### Database Setup
1. Open phpMyAdmin (http://localhost/phpmyadmin/)
2. Create a new database named `smart_lms`
3. Import the `smart_lms.sql` file provided in the project root
4. Verify the database connection by checking `db_config.php` settings

## Installation and Deployment Steps

### Local Development Installation
1. Download and install XAMPP
2. Clone or download the project files
3. Extract files to XAMPP's htdocs directory
4. Start XAMPP services (Apache and MySQL)
5. Import the database schema using phpMyAdmin
6. Access the application at `http://localhost/smart-lms-system/`
7. Register a new account or use existing test accounts

### Production Deployment
1. Set up a web server with PHP and MySQL support (e.g., Apache/Nginx with PHP-FPM)
2. Upload project files to the web root directory
3. Create a MySQL database and import the schema
4. Update `db_config.php` with production database credentials
5. Configure proper file permissions for security
6. Set up SSL certificate for HTTPS
7. Configure backup routines for database and files

### Configuration Notes
- Default database credentials (XAMPP): host=localhost, username=root, password='' (empty)
- Update `db_config.php` for production environments
- Ensure PHP sessions are enabled
- Configure timezone settings if needed

## API Documentation

This application is a traditional PHP web application and does not expose REST APIs. All functionality is accessed through web pages and forms. Key endpoints include:

- `/index.php` - Landing page
- `/login.php` - User authentication
- `/register.php` - User registration
- `/student_dashboard.php` - Student dashboard
- `/teacher_dashboard.php` - Teacher dashboard
- `/parent_dashboard.php` - Parent dashboard
- `/lecturer_dashboard.php` - Lecturer dashboard
- `/AITutor.php` - AI tutoring interface
- `/Analytics.php` - Analytics and reporting
- `/Subjects.php` - Subject management

Data is exchanged through POST/GET requests and PHP sessions. No external API integrations are currently implemented.

## Known Limitations

- Requires XAMPP for local development (not compatible with other server stacks without modifications)
- Database uses MySQL only (no PostgreSQL or other database support)
- AI features are simulated and require integration with actual AI services for full functionality
- Multilingual support is basic and may require additional localization work
- No automated testing framework implemented
- Session-based authentication (no JWT or OAuth support)
- Limited scalability for high-traffic deployments without load balancing
- No built-in backup or recovery mechanisms
- Requires manual database schema updates for new features

## Team Members

- Boitumelo Molekoa - Software Engineer
- Moeng Chantelle - Software Engineer
- Lesego Sambo - Software Engineer
- Rabelani Rathala - Software Engineer
- [Placeholder] - Hardware Engineer
- [mahlogonolo phatlane] - Fullstuck developer
- [Lindokuhle Maphonyane] - AI Developer

## Contributing

Please follow standard PHP development practices. Ensure code is tested locally before committing. Update documentation for any new features.

## License

[Specify license if applicable]

## Support

For support, contact the development team or create an issue in the project repository.