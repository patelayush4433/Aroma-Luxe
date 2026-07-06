# 🧴 AromaLuxe — Luxury Perfume E-Commerce Platform

A full-featured, premium luxury perfume e-commerce web application built with **PHP**, **MySQL**, **Bootstrap 5**, and vanilla **JavaScript**. AromaLuxe delivers a dark-themed, gold-accented shopping experience with a complete admin panel, AI-powered features, and simulated payment processing.

---

## ✨ Features

### 🛍️ Customer Storefront
- **Product Catalog** — Browse perfumes by category (Men's, Women's, Unisex) with filtering and search
- **Product Details** — View fragrance notes (top, middle, base), multiple size options (30ml / 50ml / 100ml), ratings & reviews
- **Shopping Cart** — Add/remove items, adjust quantities, apply coupon codes
- **Wishlist** — Save favourite products for later
- **Checkout & Payment** — Multi-step checkout with simulated payment gateway (UPI, Card, Net Banking, COD)
- **Order Tracking** — Track order status in real-time (Pending → Packed → Shipped → Delivered)
- **Invoice Generation** — Downloadable invoice for completed orders
- **Custom Perfume Builder** — Create a bespoke fragrance by selecting notes, concentration, and bottle style
- **AI Fragrance Chat** — Simulated AI assistant for perfume recommendations
- **User Profile** — Manage account, view order history, loyalty points, and referral codes
- **Newsletter Subscription** — Subscribe for exclusive offers with simulated email notifications
- **Blog** — Fragrance articles and guides

### 🔐 Authentication
- Customer registration with **OTP verification** (simulated)
- Secure login/logout with session management
- Forgot password flow
- Referral code system with loyalty points

### 🛠️ Admin Panel
- **Dashboard** — Sales analytics, revenue charts, recent orders overview
- **Product Management** — Full CRUD for products with multi-size pricing, discounts, and stock control
- **Order Management** — View, update status, and manage all customer orders
- **Customer Management** — View registered customers and their activity
- **Category & Brand Management** — Organize products by categories and brands
- **Coupon System** — Create percentage or fixed-value discount coupons with expiry dates
- **Review Moderation** — Approve or reject customer product reviews
- **Reports & Analytics** — Sales reports with export to **PDF** and **Excel**
- **User Management** — Admin user roles and access control
- **Website Settings** — Configure store name, contact info, shipping fees, GST %, and loyalty multiplier

### 🌍 Multi-Language & Currency
- **3 Languages** — English, French, Arabic
- **4 Currencies** — INR (₹), USD ($), EUR (€), GBP (£) with simulated conversion rates

---

## 🏗️ Tech Stack

| Layer        | Technology                          |
|--------------|--------------------------------------|
| **Backend**  | PHP 8.x (vanilla, no framework)     |
| **Database** | MySQL 8.x with PDO                  |
| **Frontend** | HTML5, CSS3, Bootstrap 5, JavaScript |
| **Icons**    | Bootstrap Icons                      |
| **Styling**  | Custom CSS with glassmorphism & dark theme |

---

## 📁 Project Structure

```
Aroma-Luxe/
├── admin/                  # Admin panel pages
│   ├── dashboard.php       # Analytics dashboard
│   ├── products.php        # Product CRUD management
│   ├── orders.php          # Order management
│   ├── customers.php       # Customer management
│   ├── categories.php      # Category management
│   ├── brands.php          # Brand management
│   ├── coupons.php         # Coupon management
│   ├── reviews.php         # Review moderation
│   ├── reports.php         # Sales reports
│   ├── settings.php        # Website configuration
│   ├── users.php           # Admin user management
│   ├── export_pdf.php      # PDF report export
│   └── export_excel.php    # Excel report export
├── api/                    # AJAX API endpoints
│   ├── ai-chat.php         # AI fragrance assistant
│   ├── cart.php             # Cart operations
│   ├── customize-perfume.php # Bespoke perfume builder
│   ├── recommend.php        # Product recommendations
│   ├── search.php           # Product search
│   └── wishlist.php         # Wishlist operations
├── assets/
│   ├── css/
│   │   ├── style.css        # Main storefront styles
│   │   └── admin.css        # Admin panel styles
│   ├── js/
│   │   ├── main.js          # Storefront JavaScript
│   │   └── admin.js         # Admin panel JavaScript
│   └── images/              # Product & UI images
├── auth/                   # Authentication pages
│   ├── login.php            # Customer login
│   ├── register.php         # Customer registration
│   ├── logout.php           # Logout handler
│   ├── forgot-password.php  # Password recovery
│   └── otp-verify.php       # OTP verification
├── config/
│   ├── config.php           # App config, translations, utilities
│   └── db.php               # Database connection (PDO)
├── includes/
│   ├── header.php           # Storefront header/navbar
│   └── footer.php           # Storefront footer
├── index.php               # Homepage
├── shop.php                # Product listing page
├── product.php             # Single product page
├── cart.php                # Shopping cart page
├── checkout.php            # Checkout page
├── payment-gateway.php     # Simulated payment processing
├── payment-success.php     # Payment success page
├── payment-failed.php      # Payment failure page
├── invoice.php             # Order invoice
├── profile.php             # Customer profile/dashboard
├── customize.php           # Bespoke perfume builder page
├── track-order.php         # Order tracking page
├── about.php               # About us page
├── contact.php             # Contact page
├── blog.php                # Blog page
├── setup.php               # Database setup wizard
├── database.sql            # Full database schema
└── README.md
```

---

## 🚀 Getting Started

### Prerequisites
- **PHP** 8.0 or higher
- **MySQL** 8.0 or higher
- A local server environment (XAMPP, MAMP, Laragon, or PHP built-in server)

### Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/patelayush4433/Aroma-Luxe.git
   cd Aroma-Luxe
   ```

2. **Configure the database connection**
   
   Edit `config/db.php` and update credentials if needed:
   ```php
   $host   = 'localhost';
   $user   = 'root';
   $pass   = '';
   $dbname = 'perfume_store';
   ```

3. **Set up the database** (choose one method)
   
   **Option A** — Open `setup.php` in your browser:
   ```
   http://localhost/Aroma-Luxe/setup.php
   ```
   
   **Option B** — Import the SQL file manually:
   ```bash
   mysql -u root -p < database.sql
   ```

4. **Start the development server**
   
   Using PHP's built-in server:
   ```bash
   php -S localhost:8000
   ```
   
   Or place the project in your XAMPP/MAMP `htdocs` directory and access via:
   ```
   http://localhost/Aroma-Luxe/
   ```

5. **Access the admin panel**
   ```
   http://localhost:8000/admin/login.php
   ```

---

## 🗄️ Database Schema

The application uses **16 tables**:

| Table          | Purpose                              |
|----------------|--------------------------------------|
| `admin`        | Admin user accounts & roles          |
| `customers`    | Customer accounts, loyalty & referrals |
| `categories`   | Product categories                   |
| `brands`       | Product brands                       |
| `products`     | Product catalog with multi-size pricing |
| `cart`         | Shopping cart items                  |
| `wishlist`     | Customer wishlists                   |
| `orders`       | Order records with status tracking   |
| `order_items`  | Individual items within orders       |
| `payments`     | Payment transaction records          |
| `reviews`      | Product ratings & reviews            |
| `coupons`      | Discount coupon codes                |
| `inventory`    | Stock change audit log               |
| `newsletter`   | Newsletter email subscriptions       |
| `contact`      | Contact form submissions             |
| `settings`     | Website configuration key-value pairs |

---

## 📸 Screenshots

> _Coming soon — Run the project locally to explore the premium dark-themed UI with gold accents._

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📄 License

This project is open-source and available for educational and personal use.

---

## 👤 Author

**Ayush Patel** — [@patelayush4433](https://github.com/patelayush4433)

---

<p align="center">Made with ❤️ and a love for luxury fragrances</p>
