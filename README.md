#  MyBlog - Modern PHP & MySQL Blogging Platform

![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-InnoDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-ES6%2B-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)
![Security](https://img.shields.io/badge/Security-CSRF%20%7C%20HMAC%20%7C%20PDO-success?style=for-the-badge)

**MyBlog** is a feature-rich, minimalist blog application built with HTML5, CSS3, Vanilla JavaScript, native PHP (PDO), and MySQL. It empowers writers to create, publish, tag, edit, and manage stories while providing readers with an engaging, dark-themed reading experience.

---

## ✨ Features & Highlights

### 🔑 Authentication & Session Security
- **User Authentication**: Secure registration, login, and session destruction.
- **HMAC Signed "Remember Me" Cookies**: Uses HMAC SHA-256 signatures (`user_id:signature`) to prevent cookie forgery and account impersonation.
- **Session Fixation Defense**: Automatically regenerates session IDs (`session_regenerate_id(true)`) upon authentication.

### 🛡️ Security Hardening
- **Anti-CSRF Tokens**: All POST forms include anti-CSRF token fields (`csrf_field()`) and server-side verification (`verify_csrf_token()`).
- **PDO Prepared Statements**: Prevents SQL injection across all database queries.
- **MIME & Extension Upload Validation**: Image uploads validate both file extensions and binary MIME types (`mime_content_type()`).
- **HTTP Security Headers**: Enforces `X-Content-Type-Options`, `X-Frame-Options`, `X-XSS-Protection`, and `Referrer-Policy`.
- **Upload Execution Lockdown**: `.htaccess` protection in `/uploads/images/` blocks execution of scripts.

### 📝 Content & Interactive Features
- **Markdown Parsing**: Renders Markdown syntax (`# Headers`, `**bold**`, `*italics*`, `` `code` ``, `> quotes`, `- lists`) safely with XSS protection.
- **Category Tags & Filtering**: Tag posts with comma-separated tags and filter posts on the homepage by clicking tag badges.
- **Live AJAX Liking**: Non-blocking asynchronous Like/Unlike button updates counts instantly without page reloads.
- **Estimated Reading Time**: Dynamic reading time calculation (`e.g. 2 min read`) on blog cards.
- **Keyword Search**: Homepage search bar to search posts by title, content, or tags.

### 🎨 Visual & UI Design
- **Glassmorphic Sticky Header**: Sticky top navigation bar with backdrop blur filter (`backdrop-filter: blur(14px)`).
- **Interactive Card Hover Animations**: Smooth image scale zoom and glowing neon borders on hover.
- **Custom Dark Theme**: Modern dark mode aesthetics built with custom CSS variables.

---

## 🛠️ Technology Stack

| Layer | Technologies Used |
| :--- | :--- |
| **Frontend** | HTML5, CSS3 (Variables, Flexbox/Grid, Glassmorphism), Vanilla JS (Fetch API) |
| **Icons & Fonts** | Font Awesome 6, Google Fonts (*DM Serif Display*, *Inter*) |
| **Backend** | PHP 7.4+ / PHP 8.x (PDO Object-Oriented Interface) |
| **Database** | MySQL (InnoDB Engine, Foreign Key Constraints & Cascades) |
| **Security** | CSRF Tokens, HMAC SHA-256 Cookie Signing, Password Bcrypt Hashing |
| **Deployment** | InfinityFree / cPanel / Apache Web Server |

---

## 📁 Repository Structure

```
myblog/
├── actions/
│   ├── delete_blog.php       # Handles post deletion with ownership & CSRF checks
│   └── toggle_like.php       # Handles AJAX & standard POST like toggling
├── auth/
│   ├── login.php             # User login & HMAC Remember Me cookie setting
│   ├── logout.php            # Session destruction & cookie invalidation
│   └── register.php          # Account creation & password hashing
├── config/
│   └── db_connect.php        # PDO connection, secure session, & Remember Me auto-login
├── css/
│   └── style.css             # Main dark theme & glassmorphic stylesheet
├── includes/
│   ├── csrf.php              # CSRF token generation & validation helper
│   ├── footer.php            # Page footer template & script imports
│   ├── header.php            # Glassmorphic header nav & HTTP security headers
│   └── markdown.php          # Lightweight safe Markdown parser
├── js/
│   └── script.js             # Client-side JS & AJAX fetch handling for likes
├── uploads/
│   └── images/               # Header images upload folder (.htaccess secured)
├── create_blog.php           # Post editor (create & edit) with tags & image upload
├── index.php                 # Homepage listing trending posts, search, & tag filters
├── single_blog.php           # Post details view with Markdown rendering & likes
└── README.md                 # Project documentation
```

---

## 🗄️ Database Schema (SQL)

Run the following SQL script in **phpMyAdmin** to set up the database tables:

```sql
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` varchar(20) DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `blogPosts` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` int(11) NOT NULL,
  `blog_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`blog_id`) REFERENCES `blogPosts`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

---

## 💻 Local Setup Instructions

### Prerequisites
- Install [XAMPP](https://www.apachefriends.org/index.html), WAMP, or MAMP.
- Code Editor (e.g. [VS Code](https://code.visualstudio.com/)).

### 1. Clone & Place Files
Clone or place the `myblog` directory inside your web server document root:
- Windows XAMPP: `C:\xampp\htdocs\myblog`
- macOS XAMPP: `/Applications/XAMPP/htdocs/myblog`

### 2. Database Configuration
1. Open XAMPP Control Panel and start **Apache** and **MySQL**.
2. Open phpMyAdmin at `http://localhost/phpmyadmin/`.
3. Create a new database named `blog_app`.
4. Import the SQL table schema above in the **SQL** tab.
5. Check `config/db_connect.php` to ensure credentials match your local database:
   ```php
   $host = getenv('DB_HOST') ?: 'localhost';
   $dbname = getenv('DB_NAME') ?: 'blog_app';
   $username = getenv('DB_USER') ?: 'root';
   $password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';
   ```

### 3. Run Locally
Open your browser and visit: `http://localhost/myblog/`

---

## 🌐 Live Online Deployment (InfinityFree / cPanel)

### 1. Database Setup
1. Create a MySQL database in your InfinityFree / cPanel control panel.
2. Open phpMyAdmin and import the SQL table schema.

### 2. File Upload via FileZilla FTP
1. Open **FileZilla** and connect using your FTP Host, Username, and Password (Port `21`).
2. Open the **`htdocs/`** directory on the remote site panel.
3. Delete the default `index2.html` file if present.
4. Upload all project files directly into `htdocs/`.

### 3. Update Database Credentials
In `config/db_connect.php`, update the default fallback values to your live hosting database details:
```php
$host = getenv('DB_HOST') ?: 'sql105.infinityfree.com';
$dbname = getenv('DB_NAME') ?: 'if0_XXXXXXXX_blogdb';
$username = getenv('DB_USER') ?: 'if0_XXXXXXXX';
$password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'your_password';
```

---

## 📄 License & Acknowledgments

- **Fonts**: [Google Fonts](https://fonts.google.com/) (*DM Serif Display*, *Inter*)
- **Icons**: [Font Awesome](https://fontawesome.com/)
- Developed with PHP, MySQL, and Modern Web Standards.
```
