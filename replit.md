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
- Light/dark theme toggle (light is default)
- Animated landing page with glassmorphism design
- Mobile-only animated background (color blobs, mesh gradient, floating shapes)
- Scroll-progress bar + back-to-top button
- Booking form saves to DB
- **Automated owner notifications** on every booking:
  - **Email** via Replit's email service (delivered to verified Replit account email)
  - **WhatsApp** via CallMeBot if `CALLMEBOT_API_KEY` env var set; otherwise wa.me auto-open as fallback
- Protected admin dashboard (session-based auth) with completed-booking auto-removal animation
- Dynamic pricing (editable via admin panel)
- Mobile responsive layout

## Notification Setup Notes
- **Email**: Goes to whatever email is verified on this Replit account. To send to abhijitghosh9749332827@gmail.com, that address must be the verified Replit-account email (or set up Gmail forwarding from the current verified address).
- **WhatsApp automation (optional)**:
  1. Owner sends `"I allow callmebot to send me messages"` from phone 9749332827 to **+34 644 51 95 23**
  2. CallMeBot replies with a personal API key
  3. Add it as a Replit secret: `CALLMEBOT_API_KEY=<key>` and restart workflow
  4. Bookings will then auto-deliver to WhatsApp without any tap
- Without API key, the booking-confirmation page still auto-opens wa.me/919749332827 with pre-filled details so owner just taps Send.

## Files Added for Automation
- `mailer.php` — `sendBookingEmail()` and `sendBookingWhatsApp()` helpers

## Admin Access
- URL: `/admin-login.html`
- Dashboard: `/admin-dashboard.php`
- Sessions are used for authentication (session fixation protection via `session_regenerate_id`)
