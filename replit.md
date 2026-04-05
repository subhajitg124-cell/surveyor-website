# SG Survey — Professional Land Surveying Services

## Project Overview
A professional service website for **Swarupananda Ghosh**, a licensed land surveyor based in Bankura, India. The site allows clients to view services, book appointments, and contact the surveyor. An admin dashboard allows management of bookings and site settings.

## Tech Stack
- **Language**: PHP 8.2
- **Database**: SQLite 3 (via PDO)
- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Icons/Fonts**: Font Awesome, Google Fonts (Playfair Display, DM Sans)

## Project Structure
```
/
├── index.php           # Main landing page (home, services, booking form)
├── db.php              # Database connection + auto-initialization
├── book.php            # Booking form submission handler
├── admin-login.html    # Admin login page
├── login.php           # Admin login authentication (returns JSON)
├── admin-dashboard.php # Protected admin interface
├── save-data.php       # Saves site settings from admin dashboard
├── logout.php          # Admin logout
├── style.css           # All styles (glassmorphism, dark theme, responsive)
├── script.js           # Frontend JS (slider, animations, form validation)
├── setup.sql           # SQL schema reference
├── visiting-card.jpg.jpeg  # Business card image used on homepage
└── attached_assets/    # Uploaded images/screenshots
```

## Database
- **Type**: SQLite (file-based, auto-created at `database.sqlite` on first run)
- **Tables**: `bookings`, `admin_users`, `site_data`
- The database is **excluded from git** (`.gitignore`) and created automatically
- Default admin: username `admin`, password `admin123`
- Second admin: username `subhajitghosh`

## Running the Application
- **Command**: `php -S 0.0.0.0:5000`
- **Port**: 5000 (mapped to external port 80)
- The workflow "Start application" handles this automatically

## Key Features
- Dark/light theme toggle
- Animated landing page with glassmorphism design
- Booking form with WhatsApp redirect notification
- Protected admin dashboard (session-based auth)
- Dynamic pricing (editable via admin panel)
- Mobile responsive layout

## Admin Access
- URL: `/admin-login.html`
- Dashboard: `/admin-dashboard.php`
- Sessions are used for authentication (session fixation protection via `session_regenerate_id`)
