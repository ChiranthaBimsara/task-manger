# Task Manager

## Overview

Task Manager is a simple web application built with **PHP and MySQL** that allows users to manage their daily tasks.
Users can register, log in, create tasks, update task status, and delete tasks.

The application uses a **token-based authentication system** and follows a basic **MVC-style structure** for better organization of controllers, models, and configuration files.

---

# Features

* User registration
* User login with token authentication
* Create new tasks
* View tasks with pagination
* Update task title, description, or status
* Delete tasks (soft delete)
* Filter tasks by status
* Secure database interaction using PDO
* Environment configuration using `.env`

---

# Tech Stack

Backend

* PHP
* MySQL
* PDO
* Composer

Frontend

* HTML
* CSS
* JavaScript (Fetch API)

Libraries

* vlucas/phpdotenv

---

# Project Structure

```
task-manager
│
├── public
│   ├── index.php
│   ├── index.html
│   └── .htaccess
│
├── src
│   ├── Config
│   │   └── Database.php
│   │
│   ├── Controllers
│   │   ├── AuthController.php
│   │   └── TaskController.php
│   │
│   └── Models
│       ├── User.php
│       └── Task.php
│
├── vendor
├── composer.json
├── composer.lock
├── database.sql
├── .env.example
└── README.md
```

---

# Setup Instructions

## 1. Clone the repository

```bash
git clone https://github.com/your-username/task-manager.git
```

Navigate to the project folder:

```bash
cd task-manager
```

---

## 2. Install dependencies

Run Composer:

```bash
composer install
```

If composer is not installed globally, run:

```bash
php composer.phar install
```

---

## 3. Configure environment variables

Copy the example environment file:

```bash
cp .env.example .env
```

Edit `.env` with your database credentials.

Example:

```
DB_HOST=127.0.0.1
DB_NAME=task_manager
DB_USER=root
DB_PASS=
```

---

# Database Setup

Create a MySQL database:

```
task_manager
```

Then import the SQL file:

```
database.sql
```

Example using MySQL:

```sql
CREATE DATABASE task_manager;
USE task_manager;
```

Then run the SQL commands inside `database.sql`.

---

# Running the Project Locally

1. Install **WAMP**, **XAMPP**, or another local PHP server.
2. Place the project inside the web server directory.

Example for WAMP:

```
C:\wamp64\www\task-manager
```

3. ## Database Setup

Create the database and tables using the following SQL.

```sql
CREATE DATABASE IF NOT EXISTS task_manager;
USE task_manager;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    api_token VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tasks table
CREATE TABLE tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('pending','in_progress','completed') DEFAULT 'pending',
    is_deleted TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### How to Import

1. Open **phpMyAdmin**.
2. Create a new database called:

```
task_manager
```

3. Go to the **SQL tab**.
4. Paste the SQL code above.
5. Click **Run**.

After running this script, two tables will be created:

* `users`
* `tasks`


4. Open the application in your browser:

```
http://localhost/task-manger/public/index.html
```

---

# API Routes

## Authentication

### Register User

POST

```
/auth/register
```

Example request body:

```json
{
  "username": "admin",
  "password": "123456"
}
```

---

### Login

POST

```
/auth/login
```

Example response:

```json
{
  "token": "user_api_token"
}
```

---

### Logout

POST

```
/auth/logout
```

Header required:

```
Authorization: Bearer TOKEN
```

---

# Task Routes

## Get Tasks

GET

```
/tasks
```

Optional parameters:

```
?page=1
?limit=5
?status=pending
```

---

## Get Single Task

```
GET /tasks/{id}
```

---

## Create Task

```
POST /tasks
```

Body:

```json
{
  "title": "Finish project",
  "description": "Complete the task manager project"
}
```

---

## Update Task

```
PUT /tasks/{id}
```

Body example:

```json
{
  "status": "completed"
}
```

---

## Delete Task

```
DELETE /tasks/{id}
```

This performs a **soft delete** by marking the task as deleted.

---

# Environment Variables

| Variable | Description       |
| -------- | ----------------- |
| DB_HOST  | Database host     |
| DB_NAME  | Database name     |
| DB_USER  | Database username |
| DB_PASS  | Database password |

Example `.env`:

```
DB_HOST=127.0.0.1
DB_NAME=task_manager
DB_USER=root
DB_PASS=
```

---

# Assumptions

* The application runs in a **local development environment** using WAMP or XAMPP.
* Authentication is implemented using **API tokens stored in the database** instead of JWT.
* Tasks belong to a specific authenticated user.
* Deleting a task performs a **soft delete** instead of permanently removing the record.
* The frontend interacts with the backend API using **JavaScript Fetch requests**.

---

# Demo Video

Include a short demo video showing:

1. Project running locally
2. User registration
3. User login
4. Creating a task
5. Updating task status
6. Deleting a task
7. Viewing tasks in the dashboard

---

# License

This project is for educational and demonstration purposes...
