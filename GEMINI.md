# GEMINI.md

## Project Overview

This project is a simple, yet feature-rich file uploading and hosting service built with PHP. It provides a clean web interface for users to upload files via drag-and-drop or a traditional file selector. Key features include a client-side progress bar, URL shortening for shared links, and the ability for users to see their past uploads, which are tracked via a browser cookie. The application is designed with SOLID principles and PSR-12 standards in mind.

## Key Technologies

- **Backend:** PHP 8.0+
- **Database:** SQLite (default, via PDO), with instructions for using MySQL.
- **Frontend:** HTML5, CSS3, and modern vanilla JavaScript (using `XMLHttpRequest` for AJAX uploads).
- **Web Server:** Apache (utilizing `mod_rewrite` in the `.htaccess` file), but adaptable to Nginx.
- **Dependency Management:** Composer for autoloading.

## Core Features

- **Drag & Drop File Upload:** Modern interface for selecting files.
- **Real-time Progress Bar:** Visual feedback during the upload process.
- **URL Shortener:** Generates a unique, short hash (`/f/{hash}`) for every uploaded file.
- **Secure File Storage:** Uploaded files are stored on the server with a randomized filename to prevent direct enumeration.
- **User Upload History:** Tracks a user's uploaded files using an HTTP-only cookie and displays them on the main page.
- **Dynamic Background:** Features a random background image from `picsum.photos` on each page load.

## Project Structure

The project follows a clear and organized structure:

```
/
├─ public/                # Web root, entry points for users
│  ├─ .htaccess           # Apache rewrite rules
│  ├─ index.php           # Main page with the uploader UI
│  ├─ upload.php          # API endpoint for handling file uploads
│  └─ f.php               # Endpoint for serving files via short URL
├─ src/                   # PSR-4 autoloaded PHP classes
│  ├─ StorageInterface.php # Interface for storage implementations
│  ├─ LocalStorage.php    # Stores files on the local server
│  ├─ UrlShortener.php    # Manages the database mapping of hashes to file paths
│  ├─ CookieManager.php   # Handles reading/writing the user's upload history cookie
│  └─ FileUploader.php    # Orchestrates the file validation and upload process
├─ data/                  # Contains the SQLite database file (files.db)
├─ uploads/               # Directory where uploaded files are stored
├─ vendor/                # Composer dependencies
└─ composer.json          # Project dependencies and autoload configuration
```

## How to Run the Project

1.  **Clone the repository.**
2.  **Install dependencies:**
    ```bash
    composer install
    ```
3.  **Create necessary directories:**
    ```bash
    mkdir -p uploads data
    ```
4.  **Set permissions:** Ensure your web server (e.g., `www-data`) can write to the `uploads` and `data` directories.
    ```bash
    sudo chown -R www-data:www-data uploads data
    sudo chmod 755 uploads data
    ```
5.  **Configure your web server:**
    -   Set the document root to the `public/` directory.
    -   Ensure `mod_rewrite` (or equivalent) is enabled to handle the rules in `.htaccess`.
6.  **Access the application:** Open your browser and navigate to the configured domain. The base URL is automatically detected in `public/index.php`.
