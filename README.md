# BlogSphere - A Simple PHP Blog Application

BlogSphere is a minimalist blog application built with HTML, CSS, JavaScript for the frontend, PHP for the backend, and MySQL as the database. It allows users to register, log in, create, view, update, and delete their own blog posts.

## Features

*   **User Authentication:** Register, Log In, Log Out.
*   **Session & Cookie Management:** Users stay logged in using PHP sessions, with a "Remember Me" option using persistent cookies.
*   **Authorization:** Users can only manage (create, update, delete) their own blog posts.
*   **Blog Management:**
    *   Create new blog posts (with a title, content, and optional image URL).
    *   View all blog posts on the home page.
    *   View individual blog posts.
    *   Update existing blog posts.
    *   Delete own blog posts.
*   **Responsive UI:** Clean and responsive user interface for various screen sizes.
*   **Custom Dark Theme:** Modern dark mode design.

## Technologies Used

*   **Frontend:**
    *   HTML5
    *   CSS3
    *   JavaScript (for minor interactivity like password toggle - *removed in current version*, and for future enhancements)
    *   Google Fonts (`DM Serif Display`, `Inter`)
    *   Font Awesome (for icons)
*   **Backend:**
    *   PHP (version 7.4+ recommended)
    *   PDO (PHP Data Objects) for secure database interaction
*   **Database:**
    *   MySQL
*   **Local Development Environment:**
    *   XAMPP / WAMP / MAMP (Apache web server, MySQL database, PHP interpreter)
    *   VS Code (code editor)
*   **Deployment:**
    *   Free Hosting Provider (e.g., InfinityFree, 000WebHost)
    *   FTP Client (e.g., FileZilla)

## Setup Instructions (Local Development)

Follow these steps to get the project running on your local machine.

### Prerequisites

*   Install [XAMPP](https://www.apachefriends.org/index.html) (or WAMP/MAMP)
*   Install a code editor like [VS Code](https://code.visualstudio.com/)

### 1. Project Setup

1.  **Clone or Download:** Get the project files and place the `myblog` folder inside your web server's document root:
    *   `C:\xampp\htdocs\` (for Windows XAMPP)
    *   `/Applications/XAMPP/htdocs/` (for macOS XAMPP)
    *   `C:\wamp\www\` (for Windows WAMP)
    *   `/Applications/MAMP/htdocs/` (for macOS MAMP)
    So, your project path will be `.../htdocs/myblog/`.

### 2. Database Setup (Local)

1.  **Start XAMPP/WAMP/MAMP:** Open the control panel and start `Apache` and `MySQL`.
2.  **Access phpMyAdmin:** Open your browser and go to `http://localhost/phpmyadmin/`.
3.  **Create Database:** Click `New` on the left sidebar, enter `blog_app` as the database name, and click `Create`.
4.  **Import Tables:**
    *   Select the `blog_app` database on the left.
    *   Go to the `SQL` tab.
    *   Copy and paste the following SQL code into the query box and click `Go`:
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
          `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ```
        *(Alternatively, you can export your local `blog_app.sql` from your previous setup and import it here)*

### 3. Configure Database Connection

1.  **Open `myblog/config/db_connect.php`** in VS Code.
2.  **Ensure the credentials are set for your local database:**
    ```php
    $host = 'localhost';
    $dbname = 'blog_app';
    $username = 'root';
    $password = ''; // Typically empty for XAMPP/WAMP/MAMP
    ```

### 4. Run the Application

1.  Ensure `Apache` and `MySQL` are running in your XAMPP/WAMP/MAMP control panel.
2.  Open your browser and go to `http://localhost/myblog/`.

## Deployment Instructions (Online Hosting)

### 1. Prerequisites

*   A free hosting account (e.g., InfinityFree, 000WebHost).
*   FTP client software (e.g., [FileZilla Client](https://filezilla-project.org/)).

### 2. Database Setup (Online)

1.  **Access Hosting Control Panel:** Log in to your hosting provider's control panel (e.g., cPanel).
2.  **Create Database & User:**
    *   Go to "MySQL Databases".
    *   Create a new database (e.g., `youruser_blogdb`). Note down the full database name.
    *   Create a new MySQL user and set a strong password. Note down the full username and password.
    *   Add the newly created user to your database (`youruser_blogdb`) and grant "ALL PRIVILEGES".
3.  **Export Local Database:**
    *   Go to your local phpMyAdmin (`http://localhost/phpmyadmin/`), select `blog_app`, click the `Export` tab, choose `Custom`, ensure `SQL` format, select all tables, and check "Add DROP TABLE..." then click `Go` to download `blog_app.sql`.
4.  **Import to Online Database:**
    *   In your hosting control panel, open phpMyAdmin for your *online* database (`youruser_blogdb`).
    *   Select `youruser_blogdb` on the left.
    *   Go to the `Import` tab, choose the `blog_app.sql` file you exported, and click `Go`.

### 3. Configure Database Connection (Online)

1.  **Open `myblog/config/db_connect.php`** in VS Code.
2.  **Change the credentials to your *online* database details:**
    ```php
    $host = 'your_online_database_host'; // e.g., 'sql100.infinityfree.com'
    $dbname = 'your_online_database_name'; // e.g., 'if0_XXXXXXXX_blogdb'
    $username = 'your_online_database_username'; // e.g., 'if0_XXXXXXXX'
    $password = 'your_online_database_password'; // The password you set for the DB user
    ```

### 4. Upload Files via FTP

1.  **Connect with FileZilla:** Open FileZilla, enter your FTP Host, Username, Password, and Port (usually 21), then `Quickconnect`.
2.  **Navigate Remote Site:** On the right (Remote Site), go into your `public_html` (or `htdocs`) folder.
3.  **Navigate Local Site:** On the left (Local Site), go into your `myblog` project folder (e.g., `C:\xampp\htdocs\myblog`).
4.  **Upload:** Select *all* files and folders *inside* your local `myblog` folder. Drag them directly into the `public_html` folder on the remote site.
5.  **Overwrite:** If prompted, choose "Overwrite" and "Always use this action".

### 5. Access Your Live Site

1.  Open your browser and go to your public URL (e.g., `http://yourusername.infinityfreeapp.com/`).
2.  Clear your browser cache (`Ctrl+F5` or `Cmd+Shift+R`) for a fresh view.

## Usage

1.  **Register:** Create a new user account.
2.  **Login:** Access your account.
3.  **Create Blog:** Add new blog posts with a title, content, and an image URL.
4.  **View Blogs:** See all posts on the home page or click on a post to view its full content.
5.  **Edit Blog:** Update your own blog posts (visible only when logged in as the author).
6.  **Delete Blog:** Remove your own blog posts (visible only when logged in as the author).
7.  **Logout:** End your session.

## Future Enhancements (Ideas)

*   Implement a full-featured Markdown/Rich Text Editor.
*   Add a "Tags" system for blog posts.
*   Improve image management (actual file uploads instead of URLs).
*   Add user profile pages.
*   Implement comments section for blog posts.
*   Better error handling and user feedback.
*   Use a proper `.env` file for configuration (see below).

## Contact

Feel free to reach out if you have questions or suggestions!