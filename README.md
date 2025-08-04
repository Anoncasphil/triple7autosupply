# Triple 7 Auto Supply - Automotive Parts Management System

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)](LICENSE)

A comprehensive web-based automotive parts management system designed for Triple 7 Auto Supply. This system provides a complete solution for managing inventory, users, and customer interactions for an automotive parts business.

## 🌟 Features

### 🏠 Public Website
- **Responsive Design**: Mobile-first approach with modern UI/UX
- **Product Catalog**: Browse automotive parts with search and filtering
- **Product Details**: Detailed product information with images
- **Contact Information**: Business details and social media links
- **Location Services**: Interactive Google Maps integration
- **SEO Optimized**: Search engine friendly structure

### 🔐 Admin Panel
- **Secure Authentication**: Role-based access control (Admin/Staff)
- **User Management**: Create, edit, and manage system users
- **Product Management**: Complete CRUD operations for inventory
- **Image Upload**: Secure file upload with validation
- **Responsive Dashboard**: Mobile-friendly admin interface
- **Real-time Search**: Instant product and user search functionality

### 🛡️ Security Features
- **Password Hashing**: Secure password storage using PHP password_hash()
- **Session Management**: Secure session handling
- **File Upload Security**: Strict file type validation
- **SQL Injection Prevention**: Prepared statements
- **XSS Protection**: Input sanitization and output escaping
- **Directory Protection**: .htaccess security rules
- **CSRF Protection**: Form token validation

### 📱 Responsive Design
- **Mobile-First**: Optimized for all device sizes
- **Touch-Friendly**: Mobile-optimized interactions
- **Fast Loading**: Optimized assets and caching
- **Cross-Browser**: Compatible with all modern browsers

## 🚀 Quick Start

### Prerequisites
- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache/Nginx web server
- mod_rewrite enabled (for Apache)

### Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/Anoncasphil/triple7autosupply.git
   cd triple7autosupply
   ```

2. **Set Up Database**
   ```sql
   -- Create database
   CREATE DATABASE triple7auto_supply;
   USE triple7auto_supply;
   
   -- Import database structure
   -- Run the SQL files in the database/ directory
   ```

3. **Configure Database Connection**
   ```php
   // Edit config/database.php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'triple7auto_supply');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

4. **Set Up Web Server**
   - Point your web server to the project root directory
   - Ensure mod_rewrite is enabled (Apache)
   - Set proper file permissions (755 for directories, 644 for files)

5. **Create Upload Directory**
   ```bash
   mkdir uploads/images
   chmod 755 uploads/images
   ```

6. **Access the Application**
   - Public Website: `http://your-domain.com/`
   - Admin Panel: `http://your-domain.com/login/`

## 📁 Project Structure

```
triple7auto/
├── admin/                    # Admin panel files
│   ├── dashboard/           # Dashboard functionality
│   ├── products/            # Product management
│   ├── users/               # User management
│   └── sidebar/             # Admin sidebar component
├── api/                     # API endpoints
│   ├── add_users.php        # User creation API
│   ├── get_existing_users.php # User data API
│   ├── get_product_data.php # Product data API
│   └── get_user_data.php    # User data API
├── assets/                  # Static assets
│   └── images/              # Images and logos
├── config/                  # Configuration files
│   ├── config.php           # General configuration
│   ├── database.php         # Database connection
│   └── example_usage.php    # Usage examples
├── database/                # Database files
│   ├── products_table.sql   # Products table structure
│   └── users_table.sql      # Users table structure
├── login/                   # Authentication
│   ├── index.php            # Login page
│   └── logout.php           # Logout functionality
├── uploads/                 # File uploads
│   └── images/              # Product images
├── .htaccess                # Security rules
├── index.php                # Main website
└── README.md                # This file
```

## 🔧 Configuration

### Database Configuration
Edit `config/database.php` to set your database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'triple7auto_supply');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### Security Configuration
The system includes comprehensive security measures:

- **File Upload Restrictions**: Only images allowed in uploads
- **Directory Protection**: Sensitive directories blocked from web access
- **Session Security**: Secure session handling
- **Input Validation**: All user inputs validated and sanitized

## 👥 User Roles

### Admin
- Full system access
- User management
- Product management
- System configuration

### Staff
- Limited admin access
- Product management
- Basic user operations

## 🛠️ API Endpoints

### User Management
- `GET /api/get_user_data.php?id={user_id}` - Get user information
- `GET /api/get_existing_users.php` - Get all users for validation

### Product Management
- `GET /api/get_product_data.php?id={product_id}` - Get product information

## 🔒 Security Features

### File Upload Security
- File type validation (images only)
- File size limits
- Secure file naming
- Directory traversal prevention

### Database Security
- Prepared statements
- Input sanitization
- SQL injection prevention
- Error handling

### Web Security
- XSS protection
- CSRF protection
- Directory listing prevention
- Security headers

## 📱 Mobile Responsiveness

The system is fully responsive and optimized for:
- Mobile phones (320px+)
- Tablets (768px+)
- Desktop computers (1024px+)
- Large screens (1440px+)

## 🎨 UI/UX Features

- **Modern Design**: Clean, professional interface
- **Intuitive Navigation**: Easy-to-use menu system
- **Loading States**: Visual feedback for user actions
- **Toast Notifications**: Success/error message display
- **Modal Dialogs**: Clean form interfaces
- **Search Functionality**: Real-time search with filters

## 🚀 Performance Optimizations

- **Optimized Images**: Compressed and properly sized
- **Minified CSS/JS**: Reduced file sizes
- **Efficient Queries**: Optimized database queries
- **Caching**: Browser caching enabled
- **CDN Integration**: External resources from CDN

## 🐛 Troubleshooting

### Common Issues

1. **500 Internal Server Error**
   - Check .htaccess file syntax
   - Verify mod_rewrite is enabled
   - Check file permissions

2. **Database Connection Error**
   - Verify database credentials
   - Check database server status
   - Ensure database exists

3. **Upload Issues**
   - Check upload directory permissions
   - Verify file size limits
   - Check allowed file types

4. **Login Issues**
   - Clear browser cache
   - Check session configuration
   - Verify user credentials

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📞 Support

For support and questions:
- **Business**: Contact Triple 7 Auto Supply
- **Technical**: Create an issue on GitHub
- **Email**: triple7autoparts@gmail.com

## 🔄 Version History

- **v1.0.0** - Initial release with core functionality
- **v1.1.0** - Added responsive design and mobile optimization
- **v1.2.0** - Enhanced security features and API endpoints
- **v1.3.0** - Improved UI/UX and performance optimizations

## 🙏 Acknowledgments

- **Tailwind CSS** for the beautiful UI framework
- **Font Awesome** for the icon library
- **Google Maps** for location services
- **PHP Community** for excellent documentation

---

**Built with ❤️ for Triple 7 Auto Supply**

*Automotive parts management made simple and secure.* 