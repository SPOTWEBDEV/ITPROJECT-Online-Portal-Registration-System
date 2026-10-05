# Online Portal Registration System

Built with HTML, CSS, Tailwind CSS, JavaScript, PHP and MySQL.

## How to run it (XAMPP)

1. Install XAMPP and start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Copy this whole `portal` folder into `C:\xampp\htdocs\` (so you have `C:\xampp\htdocs\portal`).
3. Open `http://localhost/phpmyadmin`, click **Import**, choose `database.sql`, and click **Go**.
4. Open `http://localhost/portal/setup_admin.php` and create the administrator account.
5. **Delete `setup_admin.php`** after that.
6. Open `http://localhost/portal/` and test the system.

Tailwind CSS and the Google Fonts (Bricolage Grotesque and Figtree) are loaded from the internet, so the computer needs internet access when you view the pages.
The design colours and fonts are set in the `tailwind.config` block in `includes/header.php` and in `assets/css/portal.css`.

## Pages

| Page | Purpose |
|---|---|
| `index.php` | Home page |
| `register.php` | Three-step student registration (JavaScript and PHP validation, photo preview and upload) |
| `login.php` / `logout.php` | Student login and logout |
| `dashboard.php` | Student pass, status timeline, details, course registration |
| `slip.php` | Printable registration slip |
| `admin/login.php` | Administrator login |
| `admin/students.php` | List, search, filter students and update status |
| `admin/courses.php` | Add and delete courses |

## Settings you may want to change

- Database login: `includes/db.php` (default is XAMPP: user `root`, no password)
- Academic session: `CURRENT_SESSION` in `includes/auth.php`
- Department list: `$departments` at the top of `register.php`

## Credits

Built by Udeh Iye Preciousfaith during Industrial Training at SPOTWEB TECH, for Enugu State University of Science and Technology.
The university logo is in `assets/img/logo.jpg`. Replace that file with a larger version if you have one (keep the same file name).
