# WarrantyTrack

A warranty management website for a hypothetical company ("Company X"). Customers can see their product warranties and submit claims, while admins can look up customers, add warranties, and review or update claims.

Built as a course project (2026) using PHP and MySQL.

## Features

**Customers**
- Register with name, email and phone number, then log in with email and phone
- "My Warranties" dashboard with search, sorting, and status badges (Active, Expiring Soon within 30 days, Expired)
- Warranty detail page showing purchase date, warranty length, expiry date and price
- Submit a claim against an active warranty (expired warranties are blocked)
- "My Claims" page to track claim status, sort by date, and cancel a claim that is still pending

**Admins**
- Separate admin login with email and password
- View every warranty across all customers, with search by customer email, phone or product, plus sorting
- Add a warranty: look up a customer by email (or create a new one), pick a product, and enter the purchase date and length. The expiry date is calculated automatically
- Manage claims: filter by status, change status (pending, approved, rejected, completed), add admin notes, or delete a claim

## Technologies

- PHP (mysqli with prepared statements)
- MySQL
- HTML and plain CSS (no framework)
- PHP sessions for login and role-based access (customer vs admin)

## Database

Four tables, created by `schema.sql`:

| Table | Purpose |
| --- | --- |
| `users` | Customers and admins (role column) |
| `products` | Catalog of products the company sells |
| `warranties` | Links a customer to a product, with purchase and expiry dates |
| `claims` | A customer's claim against a warranty, with status and admin notes |

## Run it locally

1. Install [XAMPP](https://www.apachefriends.org/) and start **Apache** and **MySQL**.
2. Put this project folder in XAMPP's `htdocs` folder and name the folder `warrantytrack` (the page header and stylesheet links use `/warrantytrack/`).
3. Open phpMyAdmin (`http://localhost/phpmyadmin`), go to **Import**, and import `schema.sql`. This creates the `warrantytrack_db` database with sample products, customers and warranties.
4. If your MySQL settings differ from the XAMPP defaults, update `includes/db_connect.php`.
5. Open `http://localhost/warrantytrack/`.

**Demo customer login**
- Email: `jane.doe@example.com`
- Phone: `2505551234`

**Creating an admin account**

Admin passwords are stored hashed, so the admin is not in `schema.sql`. To create one:

1. Generate a hash in a terminal: `php -r "echo password_hash('choose-a-password', PASSWORD_DEFAULT);"`
2. In phpMyAdmin, open the `users` table and insert a row with `role` set to `admin`, your email, and the hash in the `password` column.
3. Log in at `/admin_login.php`.

## Project structure

```
warrantytrack/
├── index.php              Landing page
├── register.php           Customer registration
├── login.php              Customer login
├── admin_login.php        Admin login
├── logout.php
├── dashboard.php          Customer: my warranties
├── warranty_detail.php    Customer: one warranty
├── submit_claim.php       Customer: submit a claim
├── my_claims.php          Customer: track or cancel claims
├── admin_dashboard.php    Admin: all warranties
├── add_warranty.php       Admin: add a warranty
├── manage_claims.php      Admin: review and update claims
├── style.css
├── schema.sql
└── includes/
    ├── auth_check.php     Login and role check for protected pages
    ├── db_connect.php
    ├── header.php
    └── footer.php
```

## What I practiced

- Designing a relational database with foreign keys and joining several tables in queries
- Writing create, read, update and delete features for two kinds of users
- Protecting pages by role, and using prepared statements and output escaping to guard against SQL injection and cross-site scripting
- Business rules in code, such as blocking claims on expired warranties and only letting customers cancel their own pending claims

## Known limitations

This was a course project, so some things are simplified on purpose. Customers log in with email and phone number only (no password), and there is no email verification. A production system would need stronger customer authentication.


