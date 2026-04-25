# Janeth's Business – Inventory & Sales System

A lightweight web-based system to manage daily inventory, distribution, and sales for **Janeth's branch**. Designed to be simple, fast, and expandable to multiple branches later (Aljun, Riche, etc.).

## 📁 Project Structure

Janeth's Business/
├── frontend/
│ ├── janeth-input.html # Data entry with auto-calculation
│ └── janeth-dashboard.html # Summary view and history
├── backend/
│ ├── db.php # Database connection
│ └── janeth.php # REST API (GET products, GET records, POST save)
├── database/
│ └── schema.sql # MySQL database structure + sample data
└── README.md # This file

## 🚀 Features

- **Daily entry** – Input yesterday's stock, stock‑in, distributed, sold.
- **Auto‑calculations** – Remaining stock and unsold quantity updated in real time.
- **Date‑based storage** – Each day's record is saved separately.
- **Dashboard** – View totals (distributed, sold, unsold, remaining) for any date.
- **Backend API** – Built with PHP + MySQL (PDO).
- **Future‑ready** – Easy to add more branches (just duplicate tables or add a `branch` column).

## 🛠️ Setup Instructions

### 1. Prerequisites
- XAMPP / WAMP / MAMP (Apache + MySQL + PHP)
- Web browser

### 2. Installation

1. **Copy the folder** `Janeth's Business` into your web server's document root:
   - XAMPP: `C:\xampp\htdocs\`
   - WAMP: `C:\wamp\www\`
   - MAMP: `/Applications/MAMP/htdocs/`

2. **Start Apache and MySQL** from your control panel.

3. **Create the database:**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the file `database/schema.sql`

4. **Configure database credentials** (if needed):
   - Open `backend/db.php`
   - Update `$username`, `$password` to match your MySQL setup.
   - Default XAMPP: `root` with empty password.

### 3. Access the application

- **Data entry:**  
  `http://localhost/Janeth%27s%20Business/frontend/janeth-input.html`

- **Dashboard:**  
  `http://localhost/Janeth%27s%20Business/frontend/janeth-dashboard.html`

> Note: The apostrophe and space are URL‑encoded as `%27` and `%20`.  
> To avoid encoding, rename the folder to `janeth_business`.

## 🧪 How to Use

### Daily Entry (`janeth-input.html`)
1. Select a date.
2. For each product, enter:
   - **Yesterday Qty** – remaining from previous day
   - **Stock In** – new stock received
   - **Distributed** – quantity given to Janeth (or to sales)
   - **Sold** – actual units sold
3. The table automatically shows **Unsold** (Distributed – Sold) and **Remaining** (Yesterday + Stock In – Distributed).
4. Click **Save record** – data is sent to the backend and stored in MySQL.
5. Click **Load existing data** to see previously saved entries for that date.

### Dashboard (`janeth-dashboard.html`)
- Select a date from the dropdown.
- View a summary table and total cards.
- Switch between dates to compare performance.

## 🔄 Future Expansion

To add more branches (e.g., Aljun, Riche):

**Option A – Add a `branch` column**  
Add `branch VARCHAR(50)` to `janeth_records` table, then modify the API to filter by branch.

**Option B – Duplicate tables**  
Create `aljun_records`, `riche_records` and copy the same backend endpoints.

The current architecture is modular – reuse the same patterns.

## 🐛 Troubleshooting

| Problem | Likely solution |
|---------|------------------|
| `404 Not Found` on API calls | Check that `backend/janeth.php` exists and the path `../backend/janeth.php` is correct relative to the HTML file. |
| `Database connection failed` | Verify MySQL is running and credentials in `db.php` are correct. |
| `No products loaded` | Run `schema.sql` again and ensure the `products` table has data. |
| Saved data doesn't appear | Check browser console for errors; confirm the POST response shows `"success": true`. |

## 📝 License

Free for internal business use. Open source under MIT.

## 👤 Author

Built for Janeth's Business – scalable, simple, and effective.