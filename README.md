
<h1 align="center"> 🛒 Evocart – Smart & Simple E-Commerce Website ⚡ </h1>

<p align="center">
  <img src="https://img.shields.io/badge/Frontend-HTML%20%7C%20CSS%20%7C%20Bootstrap-blue?style=for-the-badge&logo=html5" alt="Frontend">
  <img src="https://img.shields.io/badge/Backend-PHP-orange?style=for-the-badge&logo=php" alt="PHP">
  <img src="https://img.shields.io/badge/Database-MySQL-blue?style=for-the-badge&logo=mysql" alt="MySQL">
  <img src="https://img.shields.io/badge/Hosting-InfinityFree-black?style=for-the-badge&logo=apache" alt="InfinityFree">
  <img src="https://img.shields.io/badge/VersionControl-GitHub-lightgrey?style=for-the-badge&logo=github" alt="GitHub">
  <img src="https://img.shields.io/badge/IDE-VS%20Code-007ACC?style=for-the-badge&logo=visualstudiocode" alt="VSCode">
</p>

---

## 🧠 Abstract

**Evocart** is a complete **e-commerce web application** that demonstrates the core functionalities of an online shopping platform — from product browsing and cart management to order processing and administrative control.  

Developed with **PHP**, **MySQL**, and **Bootstrap**, it aims to provide a lightweight yet production-style example for students and freelance developers learning full-stack web development using procedural PHP.  

The project includes:
- 🔐 Secure login/signup using **password hashing**
- 🧩 CRUD modules for products & orders
- 💬 Admin dashboard for business operations
- 🌐 Full **deployment on InfinityFree**
- 💾 Persistent data storage with MySQL  

> 💬 **Tagline:** “Shop Smart, Manage Fast!”

---

## 📘 Table of Contents

1. [🎯 Objectives](#-objectives)  
2. [🏗️ System Overview](#️-system-overview)  
3. [🧩 Modules](#-modules)  
4. [🗄️ Database Schema & Relationships](#️-database-schema--relationships)  
5. [⚙️ Tech Stack](#️-tech-stack)  
6. [🧠 Backend Logic Flow](#-backend-logic-flow)  
7. [💻 File Structure](#-file-structure)  
8. [🔐 Authentication & Security](#-authentication--security)  
9. [⚡ Deployment Guide (InfinityFree)](#-deployment-guide-infinityfree-)  
10. [🧪 Testing Plan](#-testing-plan)  
11. [🚀 Performance Optimization](#-performance-optimization)  
12. [🧩 Scalability & Maintenance Plan](#-scalability--maintenance-plan)  
13. [🎨 UI & UX Enhancements](#-ui--ux-enhancements)  
14. [📈 Learning Outcomes](#-learning-outcomes)  
15. [🧩 Future Enhancements](#-future-enhancements)  
16. [📜 Acknowledgments](#-acknowledgments)  
17. [👨‍💻 Developer](#-developer)  
18. [🏁 Conclusion](#-conclusion)  

---

## 🎯 Objectives

| Goal | Description |
|------|--------------|
| ✅ End-to-End Flow | Build complete shopping workflow with authentication, cart, and order system. |
| 🔒 Security | Implement password hashing & prepared statements for safe authentication. |
| 🧠 Learning | Understand backend logic, database relations, and deployment process. |
| 💻 Admin Features | Include full CRUD for products and order management dashboard. |
| 🌍 Deployment | Deploy live using InfinityFree with remote MySQL DB connection. |
| 🧰 Documentation | Provide detailed documentation for educational or portfolio purposes. |

---

## 🏗️ System Overview

**Evocart** is divided into two major environments:

1. **🛍️ User Side** — customers register, browse, add to cart, checkout, and track orders.  
2. **🧑‍💼 Admin Side** — admin manages products, orders, and users via dashboard.

### 📊 System Type
> Web-based e-commerce platform for product sales and order management.

### 👥 Users
| User Role | Features |
|------------|-----------|
| Customer | Register, login, browse, add to cart, checkout, track orders |
| Admin | Manage products, orders, users, and monitor system analytics |

---

## 🧩 Modules

### 👨‍💻 User Side Modules

| Module | Description | Key Files | DB Tables |
|---------|--------------|------------|------------|
| Signup / Login | Handles secure authentication using bcrypt. | `signup.php`, `login.php`, `api/process_signup.php` | `users` |
| Product Listing | Displays dynamic products fetched from MySQL. | `index.php`, `all.php` | `products` |
| Cart | Add, update, or remove cart items linked to session. | `carts.php` | `cart` |
| Checkout | Creates orders and calculates totals (subtotal, tax, etc.). | `checkout.php`, `success.php` | `orders`, `order_items` |
| Order Tracking | Shows live order status (Pending → Delivered). | `order_status.php` | `orders`, `order_items` |

---

### 🧑‍💼 Admin Side Modules

| Module | Description | Key Files | DB Tables |
|---------|--------------|-----------|------------|
| Admin Login | Authenticates hard-coded admin credentials. | `admin_login.php` | — |
| Dashboard | Shows quick metrics (products, orders, users). | `dashboard.php` | all |
| Product CRUD | Add/Edit/Delete products and upload images. | `product_crud_api.php` | `products` |
| Order Management | View and update order status. | `order_status_api.php` | `orders` |
| User Management | Display all registered users. | `dashboard_users.php` | `users` |

---

## 🗄️ Database Schema & Relationships

### 🔹 Tables Overview
- `users` — customer data  
- `products` — catalog data  
- `cart` — temporary cart items per session  
- `orders` — order records  
- `order_items` — order details (linked to products)

### 🧬 Entity Relationship Diagram

```mermaid
erDiagram
  USERS ||--o{ CART : "has"
  USERS ||--o{ ORDERS : "places"
  ORDERS ||--|{ ORDER_ITEMS : "contains"
  PRODUCTS ||--o{ CART : "added to"
  PRODUCTS ||--o{ ORDER_ITEMS : "included in"
````

### 🗃️ Example: `users` Table

| Field         | Type         | Description           |
| ------------- | ------------ | --------------------- |
| id            | int (AI)     | Primary key           |
| name          | varchar(100) | Full name             |
| email         | varchar(100) | Unique email          |
| password_hash | varchar(255) | Hashed password       |
| created_at    | timestamp    | Account creation time |

---

## ⚙️ Tech Stack

| Layer      | Technology       | Purpose                         | Version |
| ---------- | ---------------- | ------------------------------- | ------- |
| Frontend   | HTML5            | Page structure                  | —       |
| Styling    | CSS3 + Bootstrap | Layout & responsiveness         | 4 / 5   |
| Scripting  | JavaScript       | Form validation & interactivity | —       |
| Backend    | PHP              | Server-side logic               | 7.2+    |
| Database   | MySQL / MariaDB  | Persistent data storage         | 10.6+   |
| Web Server | Apache 2         | Hosts PHP                       | —       |
| IDE        | VS Code          | Development                     | —       |
| Hosting    | InfinityFree     | Live deployment                 | —       |

---

## 🧠 Backend Logic Flow

**PHP scripts** handle CRUD operations, user authentication, and order processing using **prepared statements** for security.

### Example: Signup Flow

```php
include("../includes/config.php");
$name = $_POST['name'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO users (name,email,password_hash) VALUES (?,?,?)");
$stmt->bind_param("sss", $name, $email, $password);
$stmt->execute();
```

### Example: Order Creation Flow

```php
// checkout.php
$conn->begin_transaction();
$conn->query("INSERT INTO orders (user_id, subtotal, grand_total, status) VALUES (...)");
$order_id = $conn->insert_id;
foreach($cart_items as $item){
   $conn->query("INSERT INTO order_items (order_id, product_id, quantity) VALUES (...)");
}
$conn->commit();
```

---

## 💻 File Structure

```
htdocs/
│
├── admin/
│   ├── admin_login.php
│   ├── dashboard.php
│   ├── dashboard_users.php
│   └── api/
│        ├── product_crud_api.php
│        ├── order_status_api.php
│
├── api/
│   ├── process_signup.php
│   ├── process_login.php
│
├── includes/
│   └── config.php
│
├── assets/           → Styles, JS, images
├── uploads/          → Product images
│
├── index.php         → Homepage
├── all.php           → Category listings
├── carts.php         → Cart page
├── checkout.php      → Checkout flow
├── success.php       → Order success page
├── order_status.php  → Track orders
├── login.php / signup.php / logout.php
└── README.md
```

---

## 🔐 Authentication & Security

### 🛡️ Implemented Security Measures

* Password hashing with `password_hash()`
* SQL injection prevention via prepared statements
* Session-based authentication
* Unique email constraint
* File upload validation
* `logout.php` destroys sessions securely

### 🚧 Known Weaknesses

| Issue                        | Risk   | Solution                           |
| ---------------------------- | ------ | ---------------------------------- |
| Hard-coded admin credentials | High   | Store in DB with role-based access |
| Missing CSRF protection      | Medium | Add random token validation        |
| No HTTPS enforcement         | Medium | Redirect via `.htaccess`           |
| MyISAM engine in `cart`      | Medium | Convert to InnoDB                  |
| Display errors               | Low    | Disable in production              |

---

## ⚡ Deployment Guide (InfinityFree)

### ☁️ Step-by-Step Instructions

#### **1️⃣ Create Account**

Visit [InfinityFree.net](https://infinityfree.net) → Sign up → Access Control Panel.

#### **2️⃣ Create Database**

* In cPanel → click **MySQL Databases**
* Create new DB and note details:

```
DB Host: sql100.byetcluster.com
DB Name: if0_40348717_evocart
DB User: if0_40348717
Password: <yourpassword>
```

#### **3️⃣ Import Database**

* Open phpMyAdmin
* Click Import → Upload `evocart.sql` → Execute ✅

#### **4️⃣ Configure DB Connection**

Edit `/includes/config.php`:

```php
$servername = "sql100.byetcluster.com";
$username = "if0_40348717";
$password = "<yourpassword>";
$database = "if0_40348717_evocart";
$conn = new mysqli($servername, $username, $password, $database);
```

#### **5️⃣ Upload Files**

* Open File Manager → `/htdocs/`
* Upload all Evocart files
* Ensure `/uploads/` has write permission

#### **6️⃣ Test URLs**

| Page        | URL                                                                                            |
| ----------- | ---------------------------------------------------------------------------------------------- |
| 🌍 Homepage | [https://evocart.free.nf/](https://evocart.free.nf/)                                           |
| 🔑 Login    | [https://evocart.free.nf/login.php](https://evocart.free.nf/login.php)                         |
| 🛍️ Cart    | [https://evocart.free.nf/carts.php](https://evocart.free.nf/carts.php)                         |
| 🧑‍💼 Admin | [https://evocart.free.nf/admin/admin_login.php](https://evocart.free.nf/admin/admin_login.php) |

---

## 🧪 Testing Plan

| Test Case    | Action                 | Expected Result           |
| ------------ | ---------------------- | ------------------------- |
| User Signup  | Register new account   | Redirect to login         |
| Login        | Enter credentials      | Redirect to homepage      |
| Add to Cart  | Click add button       | Cart item count increases |
| Checkout     | Confirm order          | New record in `orders`    |
| Admin Login  | Enter `admin/admin123` | Redirect to dashboard     |
| Update Order | Change status          | Status updated in DB      |

### 🧰 Performance Checks

* Page load under 3 seconds
* MySQL query optimization via indexes
* Images compressed before upload

---

## 🚀 Performance Optimization

| Area                      | Technique                                     |
| ------------------------- | --------------------------------------------- |
| **Code Optimization**     | Combine & minify CSS/JS, use `require_once()` |
| **Database Optimization** | Index foreign keys, use LIMIT for pagination  |
| **Image Optimization**    | Use WebP, compress before upload              |
| **Caching**               | Add browser caching via `.htaccess`           |
| **File Compression**      | Gzip text files, optimize assets              |

---

## 🧩 Scalability & Maintenance Plan

| Aspect       | Strategy                         |
| ------------ | -------------------------------- |
| Architecture | Convert to MVC structure         |
| Frontend     | Separate with REST APIs          |
| Hosting      | Migrate to VPS or Cloud          |
| CDN          | Use Cloudflare for static assets |
| Monitoring   | Enable PHP logs & alerts         |
| Backup       | Weekly DB export                 |
| PHP Upgrades | Maintain 7.4 → 8.x versions      |

---

## 🎨 UI & UX Enhancements

Current UI is minimal — future UI should include:

* 🧭 Navbar with category filters
* ✨ Animated cards using AOS / GSAP
* 💬 Toast notifications for cart actions
* 📱 Fully responsive layout
* 🪞 Modern typography (Poppins / Inter)

---

## 🧠 Learning Outcomes

By developing **Evocart**, learners understand:

* 🔗 PHP-MySQL integration (PDO / MySQLi)
* 🔒 Secure authentication with hashing
* 🧱 Relational database normalization
* 🧮 Session & cookie handling
* 🧰 Admin dashboard creation
* 🌍 Live deployment using free hosting

---

## 🧩 Future Enhancements

| Feature                | Description                            |
| ---------------------- | -------------------------------------- |
| 💳 Payment Gateway     | Integrate Razorpay / Stripe            |
| 📩 Email Notifications | Order confirmation & delivery updates  |
| 👥 Multi-Role Access   | Separate dashboards for admins & staff |
| ⚛️ React Frontend      | Modern SPA interface                   |
| ☁️ Cloud Database      | PlanetScale / AWS RDS                  |
| 🐳 Dockerization       | Containerized environment              |
| 🔔 Push Notifications  | Real-time status alerts                |

---

## 📈 Learning & Documentation Sources

* [PHP Documentation](https://www.php.net/docs.php)
* [MySQL Docs](https://dev.mysql.com/doc/)
* [Bootstrap Framework](https://getbootstrap.com/)
* [InfinityFree Knowledge Base](https://support.infinityfree.net/)

---

## 📜 Acknowledgments

Special thanks to:

* 💻 Open-source PHP community
* ☁️ InfinityFree Hosting for free deployment
* 🎨 Bootstrap for styling framework
* 🧑‍🏫 Mentors and testers for feedback

---

## 👨‍💻 Developer

**Name:** Jeymurugan Nadar
**Location:** Mumbai, India 🇮🇳
**Role:** Full-stack Developer (PHP, MySQL, JS)
**Year:** 2025
**Website:** [https://evocart.free.nf/](https://evocart.free.nf/)
**GitHub:** [JeymuruganNadar](https://github.com/JeymuruganNadar)

---

## 🏁 Conclusion

**Evocart** demonstrates the **end-to-end workflow** of a real-world e-commerce system:

* 🛍️ Product Management
* 🧾 Cart & Checkout
* 📦 Order Processing
* 🧑‍💼 Admin Dashboard
* ☁️ Live Deployment

With added enhancements like payments, CSRF security, and modern UI, it can evolve into a **production-ready, scalable e-commerce platform**.

> 💡 *Evocart — Shop Smart, Manage Fast!*

```

