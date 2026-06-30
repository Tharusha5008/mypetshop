# Mealtime Pet Food Shop — Setup Guide (XAMPP/WAMP)

This is a PHP + MySQL website. Pages query a real database — products, logins,
registrations, contact messages, and complaints are all read from and written
to MySQL via `includes/db.php`.

## 1. Folder placement

Copy this entire `petshop` folder into your XAMPP/WAMP web root:

- XAMPP (Windows): `C:\xampp\htdocs\petshop`
- XAMPP (Mac): `/Applications/XAMPP/htdocs/petshop`
- WAMP: `C:\wamp64\www\petshop`

## 2. Start Apache and MySQL

Open the XAMPP/WAMP control panel and start both **Apache** and **MySQL**.

## 3. Import the database

1. Go to `http://localhost/phpmyadmin`
2. Click **Import** in the top menu
3. Choose the file `schema.sql` from this folder
4. Click **Go**

This creates a database called `mealtime_shop` with all tables (`products`,
`categories`, `customers`, `addresses`, `orders`, `order_items`, `complaints`,
`contact_messages`) and some sample data.

## 4. Check the database connection settings

Open `includes/db.php`. The defaults match a stock XAMPP/WAMP install:

```php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'mealtime_shop';
```

If your MySQL root user has a password set, update `$DB_PASS` accordingly.

## 5. Open the site

Visit: `http://localhost/petshop/index.php`

## What's connected to the database

| Page             | What it does with MySQL                                              |
|------------------|------------------------------------------------------------------------|
| `index.php`      | Reads all active products from the `products`/`categories` tables     |
| `register.php`   | Inserts a new row into `customers` (password is hashed with bcrypt)   |
| `login.php`      | Looks up `customers` by email, verifies password hash, starts session |
| `logout.php`     | Destroys the session                                                   |
| `contact.php`    | Inserts a row into `contact_messages`                                  |
| `complaint.php`  | Inserts a row into `complaints`, and lists the 5 most recent live      |
| `cart.php`       | On checkout: re-verifies prices against `products`, inserts an `order`, `order_items`, and `addresses` row |

The cart itself (adding/removing items, quantities) is still handled in the
browser via `localStorage` (see `assets/cart-store.js`) — that's normal for a
shopping cart, since it shouldn't need a database write every time someone
clicks "+1". The cart only touches the database at the final checkout step.

## Known limitations (worth mentioning in your assignment writeup)

- No CSRF protection on forms (fine for a class assignment, not for production).
- Login sessions are basic PHP sessions; no "remember me" persistence beyond the session.
- Guest checkout creates a placeholder customer row with a random password hash
  if the email isn't already registered — a real system might instead make
  `customer_id` nullable on `orders` for true guest checkout.
- No image uploads — product art is just emoji placeholders.
