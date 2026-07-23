# Disaster Management System (DBMS Project)

## Overview

The Disaster Management System is a web-based application developed as a Database Management Systems (DBMS) project. It is designed to facilitate disaster response and management by connecting administrators, volunteers, and donors through a centralized platform. The system helps streamline volunteer registration, donation management, and administrative operations during emergency situations.

## Features

* User Registration and Login System
* Volunteer Management Portal
* Admin Dashboard
* Donation Management
* Secure Database Connectivity
* Responsive User Interface
* Session Management and Authentication
* Centralized Disaster Information Handling

## Technologies Used

* **Frontend:** HTML, CSS
* **Backend:** PHP
* **Database:** MySQL
* **Server Environment:** XAMPP / Apache
* **Development Tools:** VS Code, phpMyAdmin

## Project Structure

```text
dmspro/
│
├── index.php
├── login.php
├── register.php
├── adminportal.php
├── volunteerportal.php
├── donateus.php
├── aboutus.php
├── logout.php
├── dbcon.php
├── styles.css
│
├── database/
│   └── dms.sql
│
└── pics/
    ├── disaster.jpg
    ├── donate.jpg
    └── volunteer.gif
```

## Modules

### 1. User Module

* Register an account.
* Login securely.
* Access disaster-related services.

### 2. Volunteer Module

* Register as a volunteer.
* View volunteer-related information.
* Participate in disaster relief activities.

### 3. Donation Module

* Support disaster victims through donations.
* Maintain donation-related records.

### 4. Admin Module

* Manage users and volunteers.
* Monitor system activities.
* Maintain database records.

## Database

The project uses a MySQL database named `dms`.

Main tables include:

* `admins`
* Volunteer records
* User information
* Donation-related data

To import the database:

1. Open phpMyAdmin.
2. Create a database named `dms`.
3. Import the `dms.sql` file located in the `database` folder.

## Installation Guide

1. Clone the repository:

```bash
git clone https://github.com/your-username/disaster-management-system.git
```

2. Move the project folder to the `htdocs` directory of XAMPP:

```text
C:\xampp\htdocs\
```

3. Start:

* Apache
* MySQL

4. Import the database:

* Open `http://localhost/phpmyadmin`
* Create a database named `dms`
* Import `database/dms.sql`

5. Run the application:

```text
http://localhost/dmspro
```

## Screenshots

You can add screenshots of:

* Home Page
* Login Page
* Volunteer Portal
* Donation Page
* Admin Dashboard

## Learning Outcomes

This project provided hands-on experience in:

* Database Management Systems
* PHP and MySQL Integration
* CRUD Operations
* Session Handling
* Web Application Development
* Software Documentation

## Contributors

* **Sabih Asad**
* Team Members (if applicable)

## License

This project is developed for educational purposes as part of a university DBMS course project at Sir Syed University of Engineering & Technology (SSUET).

---

> *"Building technology-driven solutions to improve disaster response and community support."*
