This plan is designed for a **Code Generation AI (Codex/LLM)** to follow. It uses a "No-Build" architecture: **ZeroPHP** for the backend, **MySQL** for data, and **Tailwind CDN** for the UI.

---

## **Project Title: Simple Invoice App (Akaunting Lite)**

### **1. Environment Setup**

* **Framework:** ZeroPHP (read docs folder on this project for docs).
* **Styling:** Tailwind CSS via Play CDN: `<script src="https://cdn.tailwindcss.com"></script>`.

### **2. Database Blueprint**

Create four tables with these specific fields:

1. **`customers`**: `id`, `name`, `email`, `address`.
2. **`invoices`**: `id`, `customer_id`, `invoice_no`, `date`, `due_date`, `status` (Draft/Sent/Paid), `total`.
3. **`invoice_items`**: `id`, `invoice_id`, `description`, `qty`, `unit_price`, `subtotal`.
4. **`admin`**: `id`, `name`, `email`, `password_hash`, `last_login`.


### **3. Core Development Modules**

#### **Module A: The Global Header (Layout)**

* Implement a standard HTML5 boilerplate.
* Include Tailwind CDN.
* Add a simple Sidebar Navigation: Dashboard, Invoices, Customers, Settings.

#### **Module B: Invoice Creation Logic (The Form)**

* Create a form that selects a **Customer** from a dropdown.
* **Dynamic Line Items:** Use Vanilla JavaScript to allow users to "Add Row" for items (Description, Qty, Price).
* **Auto-Calculations:** JavaScript to update the "Total" live as Qty/Price inputs change.

#### **Module C: The Backend Processor**

* **Logic:** When the form is submitted, use a **MySQL Transaction**.
* Step 1: Insert the `invoices` header.
* Step 2: Get the `lastInsertId`.
* Step 3: Loop through line items and insert into `invoice_items`.



#### **Module D: Dashboard & Views**

* **Dashboard:** A Tailwind table showing recent invoices with colored status badges (`bg-green-100` for Paid, `bg-yellow-100` for Draft).
* **Printable Invoice View:** A clean, minimalist invoice page. Use `@media print` CSS via Tailwind (`print:hidden`) to ensure buttons don't show when saving as PDF.
* the primary button color is bg-stone-800

### **4. Technical Instructions for Codex**

* **Security:** Use prepared statements for all SQL queries to prevent injection.
* **UX:** Use `type="number"` and `step="0.01"` for currency inputs.
* **Simplicity:** Do not use Composer, NPM, or Webpack. Keep all logic in clean PHP files.

---

### **Next Step**

I am ready. Should I generate the **MySQL Schema SQL** first, or would you like the **`index.php` boilerplate code** to get the ZeroPHP routing started?
