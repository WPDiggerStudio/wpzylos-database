# WPZylos Database

[![PHP Version](https://img.shields.io/badge/php-%5E8.0-blue)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![GitHub](https://img.shields.io/badge/GitHub-WPDiggerStudio-181717?logo=github)](https://github.com/WPDiggerStudio/wpzylos-database)

Safe wpdb wrapper with query builder for WPZylos framework.

📖 **[Full Documentation](https://wpzylos.com)** | 🐛 **[Report Issues](https://github.com/WPDiggerStudio/wpzylos-database/issues)**

---

## ✨ Features

- **Query Builder** — Fluent interface for SQL queries
- **Safe Queries** — Automatic $wpdb->prepare() integration
- **CRUD Operations** — insert, update, delete, select
- **Table Prefixing** — Automatic table prefix handling
- **Transactions** — Database transaction support

---

## 📋 Requirements

| Requirement | Version |
| ----------- | ------- |
| PHP         | ^8.0    |
| WordPress   | 6.0+    |

---

## 🚀 Installation

```bash
composer require wpdiggerstudio/wpzylos-database
```

---

## 📖 Quick Start

```php
use WPZylos\Framework\Database\DB;

// Simple queries
$users = DB::table('users')->get();
$user = DB::table('users')->find(1);

// Query builder
$products = DB::table('products')
    ->where('status', 'active')
    ->where('price', '>', 100)
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();
```

---

## 🏗️ Core Features

### Select Queries

```php
// Get all records
$users = DB::table('users')->get();

// Get first record
$user = DB::table('users')->where('email', $email)->first();

// Select specific columns
$names = DB::table('users')->select('id', 'name')->get();

// With conditions
$active = DB::table('users')
    ->where('status', 'active')
    ->where('role', 'admin')
    ->get();
```

### Insert

```php
DB::table('users')->insert([
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);

// Get insert ID
$id = DB::table('users')->insertGetId([
    'name' => 'Jane Doe',
    'email' => 'jane@example.com',
]);
```

### Update

```php
DB::table('users')
    ->where('id', 1)
    ->update(['status' => 'inactive']);
```

### Delete

```php
DB::table('users')
    ->where('id', 1)
    ->delete();
```

### Transactions

```php
DB::transaction(function () {
    DB::table('orders')->insert($order);
    DB::table('inventory')->decrement('stock', 1);
});
```

---

## 📦 Related Packages

| Package                                                                    | Description            |
| -------------------------------------------------------------------------- | ---------------------- |
| [wpzylos-core](https://github.com/WPDiggerStudio/wpzylos-core)             | Application foundation |
| [wpzylos-migrations](https://github.com/WPDiggerStudio/wpzylos-migrations) | Database migrations    |
| [wpzylos-scaffold](https://github.com/WPDiggerStudio/wpzylos-scaffold)     | Plugin template        |

---

## 📖 Documentation

For comprehensive documentation, tutorials, and API reference, visit **[wpzylos.com](https://wpzylos.com)**.

---

## ☕ Support the Project

If you find this package helpful, consider buying me a coffee! Your support helps maintain and improve the WPZylos ecosystem.

<a href="https://www.paypal.com/donate/?hosted_button_id=66U4L3HG4TLCC" target="_blank">
  <img src="https://img.shields.io/badge/Donate-PayPal-blue.svg?style=for-the-badge&logo=paypal" alt="Donate with PayPal" />
</a>

---

## 📄 License

MIT License. See [LICENSE](LICENSE) for details.

---

## 🤝 Contributing

Contributions are welcome! Please see [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

---

**Made with ❤️ by [WPDiggerStudio](https://github.com/WPDiggerStudio)**
