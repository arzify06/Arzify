# 🚌 Desire Travel - Premium Bus Fleet & Reservation System

<div align="center">
  <img src="assets/images/logo.svg" alt="Desire Travel Logo" width="380"/>
  <p><strong>A Modern, Secure &amp; Comprehensive Intercity Bus Reservation &amp; Fleet Management Portal</strong></p>
  
  [![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
  [![MySQL](https://img.shields.io/badge/MySQL-MariaDB-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
  [![Bootstrap 5](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white)](https://getbootstrap.com)
  [![Multi-Language](https://img.shields.io/badge/Language-English%20%7C%20%E0%AA%97%E0%AB%81%E0%AA%9C%E0%AA%B0%E0%AA%BE%E0%AA%A4%E0%AB%80-orange?style=for-the-badge)](https://github.com)
  [![Themes](https://img.shields.io/badge/Themes-5%20Color%20Palettes-blueviolet?style=for-the-badge)](https://github.com)
</div>

---

## 🌟 Highlights & Features

### 🌐 1. Bilingual Localization (English & ગુજરાતી)
- Seamless one-click toggle between **English** and **Gujarati (ગુજરાતી)** across all forms, tables, boarding passes, buttons, and alert messages.

### 🎨 2. Dynamic 5-Theme Palette Switcher
- Choose from 5 themes with instant persistence across sessions:
  - 🔵 **Royal Navy Blue** (Default Executive Corporate)
  - 🟢 **Emerald Green** (Fresh Modern Eco)
  - 🔴 **Luxury Crimson** (VIP Travel Express)
  - 🌌 **Cyber Dark Mode** (High-Contrast Night Mode)
  - 🟠 **Sunset Amber** (Warm Energetic)

### 🪑 3. Interactive Visual Bus Seat Reservation
- Real-time visual seat map showing driver cabin, passenger entry door, booked seats in **Red (✕)**, available seats in **White/Gray**, and chosen seats in **Vibrant Green**.
- Live seat counter and dynamic payable fare calculation.

### 📏 4. Dynamic Distance-Based Tiered Fare Engine
- Intelligent fare structure:
  - **First 5 km**: Flat ₹5.00 base fare
  - **5 – 15 km**: +₹2.00 per km
  - **Beyond 15 km**: +₹1.00 per km
  - **Bus Multiplier**: 1.25x for Luxury Volvo Multi-Axle & AC Sleeper coaches.

---

## 🏢 Role-Based Modules Breakdown

### 🔐 Authentication & Audit Telemetry
- **Login Portal**: Secure Bcrypt password validation, session fixation protection, and demo quick sign-in buttons.
- **Employee Login Activity**: Detailed audit trail tracking employee username, login timestamp, logout timestamp, session duration, and client IP address.

### 👑 Admin Panel
- **Dashboard**: Real-time KPI summary (Total Gross Revenue, Active Buses, Total Routes, Scheduled Trips, Today's Bookings).
- **Bus Fleet Management**: Register, view, update, and delete buses (Bus No, Type, Capacity, Driver contact) with duplicate number validation.
- **Route Management**: Add and manage travel corridors (Origin, Destination, Distance in km, Duration).
- **Routine Scheduling**: Schedule buses on routes, dates, departure/arrival times, and fares with automatic conflict detection.
- **Staff & Employees**: Register staff, assign roles (`admin` / `employee`), toggle account statuses.
- **Booking & Revenue Reports**: Multi-filter report generator with date range, route, bus, and status filters with **CSV Spreadsheet** and **PDF / Print** export.

### 🎫 Employee & Counter Panel
- **Counter Dashboard**: Dynamic session time, today's counter sales metrics, and fast navigation shortcuts.
- **Customer Registry**: Register and manage passenger profiles with unique CNIC/ID and email validation.
- **Book Ticket Window**: 3-step interactive booking workflow with live seat map and instant boarding pass generation.
- **Cancel Booking**: Search booking by Ticket Number or Phone, cancel reservation, calculate refund, and immediately release seats for re-booking.
- **Issued Tickets**: View past bookings, verify QR code boarding passes, and reprint tickets.
- **Route Inquiry**: Search available buses, timings, and fares between any source and destination city.
- **Price List & Calculator**: Official fare table with interactive kilometer calculator.
- **Change Password**: Self-service secure password update.

---

## 📂 Project Architecture

```
Desire Travel/
├── config/
│   ├── config.php             # Core app settings, theme & language state
│   ├── database.php           # Resilient PDO connection with multi-port fallback
│   └── languages/
│       ├── en.php             # English dictionary
│       └── gu.php             # Gujarati dictionary (ગુજરાતી)
├── helpers/
│   ├── auth.php               # Session, role guard & employee login logger
│   ├── lang.php               # Language translation helper __()
│   ├── fare_calculator.php    # Tiered distance fare formula
│   └── ticket_generator.php   # Printable boarding pass & QR code generator
├── assets/
│   ├── css/
│   │   ├── style.css          # Master stylesheet with 5 color themes
│   │   └── seat-layout.css    # Interactive bus seat layout CSS
│   ├── js/
│   │   ├── main.js            # Theme switcher, live clock, table search
│   │   └── booking.js         # Interactive seat map & live price math
│   └── images/
│       └── logo.svg           # Vector luxury brand logo
├── database/
│   └── database.sql           # Complete schema and rich seed demo data
├── admin/
│   ├── dashboard.php          # Admin analytics & telemetry
│   ├── buses.php              # Bus fleet CRUD
│   ├── routes.php             # Travel routes CRUD
│   ├── routines.php           # Bus routines / schedules CRUD
│   ├── employees.php          # Staff directory & roles CRUD
│   ├── login_logs.php         # Employee login activity & log pruning
│   └── booking_reports.php    # Financial & booking reports with CSV/Print
├── employee/
│   ├── dashboard.php          # Employee counter desk
│   ├── customers.php          # Passenger registry CRUD
│   ├── booking.php            # Interactive seat reservation window
│   ├── cancel_booking.php     # Ticket cancellation & seat release
│   ├── tickets.php            # Issued tickets & boarding pass reprints
│   ├── inquiry.php            # Bus route & schedule lookup
│   ├── price_list.php         # Fare charts & live distance calculator
│   └── change_password.php    # Secure password change
├── index.php                  # Main entry point & login portal
├── logout.php                 # Sign out handler
├── .gitignore                 # Standard git ignore configuration
└── README.md                  # Project documentation
```

---

## 🚀 Quick Setup & Installation

### 1. Prerequisites
- PHP 8.0 or higher (with `pdo_mysql`, `session`, `mbstring` extensions enabled)
- MySQL / MariaDB (e.g. XAMPP, WAMP, Laragon, or standalone MySQL)
- Web Browser (Chrome, Firefox, Edge, Safari)

### 2. Database Import
1. Start MySQL in your XAMPP control panel.
2. Open phpMyAdmin (`http://localhost/phpmyadmin`) or MySQL CLI.
3. Import the file located at `database/database.sql`.
   *(Or the system will automatically create and migrate tables on first run!)*

### 3. Running the Application
Place the folder in your web server root (e.g. `C:/xampp/htdocs/Desire Travel`) or run PHP's built-in web server:

```bash
# From project directory
php -S localhost:8000
```
Open your browser and visit: `http://localhost:8000` or `http://localhost/Desire%20Travel`

---

## 🔑 Default Demo Credentials

| Role | Username | Password | Access Level |
|---|---|---|---|
| **Administrator** | `admin` | `admin123` | Full Fleet, Route, Employee & Financial Control |
| **Booking Staff** | `emp` | `emp123` | Ticket Issuing, Seat Map, Inquiry & Passenger Registry |
| **Ticket Clerk** | `clerk1` | `clerk123` | Ticket Counter Operations |

---

## 📄 License & Credits
Built for **Desire Travel**. Designed and developed with modern PHP, MySQL, and clean CSS architecture.
