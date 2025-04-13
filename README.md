
# 🐄 MooHub – Milk Sales Tracker

**Live Website:** [https://shivadiaryfarm.rf.gd/](https://shivadiaryfarm.rf.gd/)

MooHub is a milk venture web application designed to track and manage milk sales, user registrations, and financial payouts. The app allows users to log in, record daily milk sales, and view their account summaries. Admins can manage users and ensure smooth operations for the dairy business.

---

## 🧩 Features

- 👤 User Authentication (Register, Login, Password Reset)
- 🧾 Track Milk Sales Per Day
- 📊 Dashboard with Daily/Monthly Summaries
- 💸 Payout Functionality Per User
- 🛠 Admin Panel for User Management
- 📱 Responsive UI (Basic but functional)
- 🔒 Secure Password Storage (with `password_hash`)

---

## 🏗 Tech Stack

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP (Vanilla)
- **Database:** MySQL (SQL schema provided)
- **Hosting:** [000webhost](https://shivadiaryfarm.rf.gd/)

---

## 🧠 Database Setup

1. Import `dairy_farm_db.sql` into your MySQL server.
2. Create a user with access to this database.
3. Update your `database.php` with the correct credentials:

```php
$host = 'localhost';
$db = 'your_db_name';
$user = 'your_db_user';
$pass = 'your_db_pass';

$conn = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
```

---

## 💰 Payout Function (PHP Snippet)

Here’s a basic payout function you can use for calculating and displaying user earnings:

```php
function calculatePayout($user_id, $conn) {
    $stmt = $conn->prepare("SELECT SUM(amount) as total_sales FROM milk_sales WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $totalSales = $result['total_sales'] ?? 0;
    $commissionRate = 0.85; // 85% payout to user

    $payout = $totalSales * $commissionRate;

    return [
        'total_sales' => $totalSales,
        'payout' => $payout
    ];
}
```

> ⚠️ You’ll need to create a `milk_sales` table if it’s not already in the SQL file, structured like:
```sql
CREATE TABLE milk_sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    date DATE NOT NULL,
    amount DECIMAL(10,2) NOT NULL
);
```

---

## 🚀 Getting Started

1. Clone this repo:
```bash
git clone https://github.com/yourusername/MooHub.git
cd MooHub
```

2. Import the database and update your credentials.

3. Deploy it on a local or live PHP server.

---

## 🙋‍♂️ Author

- **Kavishnu**
- GitHub:(https://github.com/Kavishnu-G/MooHub)

---

## 📜 License

This project is licensed under the MIT License. Feel free to use, modify, and distribute.
