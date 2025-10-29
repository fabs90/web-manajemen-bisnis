# 💰 Corporate Financial Management System | SIM Keuangan Perusahaan

[![Project Status: Active](https://img.shields.io/badge/Status-Active-brightgreen.svg)](https://github.com/your-repo-link)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)

---

## 🌟 Overview

The **SIM Keuangan (Financial Management Information System)** is a comprehensive web application designed to streamline the management of a company's financial records. It provides essential tools for structured data entry, real-time reporting, and monitoring of core financial activities, specifically **Expenditures (Pengeluaran)** and **Debt (Hutang)**.

This project is a proud testament to inter-institutional cooperation, developed through a collaborative effort between **Gunadarma University (UG)** and **Politeknik Negeri Manado (PNM)**.

---

## 🤝 Collaborative Development

This system is the embodiment of academic synergy, showcasing the technical expertise from two leading Indonesian institutions:

| Institution | Primary Focus |
| :--- | :--- |
| **Universitas Gunadarma** | Backend Development, Database Structure, and API Logic. |
| **Politeknik Negeri Manado** | Frontend Implementation, User Interface (UI/UX) Design, and Responsiveness. |

---

## ✨ Key Features & Functionality

* **Expense Recording (Pengeluaran):** Dedicated module to log, detail, and track all corporate expenditures.
* **Total Expense Summary:** Automatic calculation and display of total spending for quick financial oversight.
* **Debt Management (Hutang):** Detailed ledger tracking debt transactions, showing **Debit**, **Kredit**, and the running **Saldo** (Balance).
* **Data Deletion Security:** Integrated SweetAlert prompts for secure, confirmed data removal, preventing accidental losses.
* **Interactive Tables:** Utilizes DataTables for efficient data filtering, searching, and pagination.
* **Responsive Design:** Built with Bootstrap and a clean Blade structure for accessibility on desktop and mobile devices.

---

## 🛠️ Technology Stack

The project is built on the robust **Laravel Framework** ecosystem.

| Category | Technology | Version / Tool |
| :--- | :--- | :--- |
| **Backend** | PHP Framework | Laravel 8/9/10 (Requires check) |
| **Frontend** | Styling & UI | Bootstrap 5 |
| **Templating** | Core Logic | Laravel Blade |
| **Libraries** | Data Management | DataTables (jQuery) |
| **Libraries** | Alerts & UX | SweetAlert2 |

---

## ⚙️ Getting Started (Installation Guide)

Follow these steps to get your local development environment up and running:

1.  **Clone the repository:**
    ```bash
    git clone [https://github.com/your-username/repo-name.git](https://github.com/your-username/repo-name.git)
    cd repo-name
    ```
2.  **Install PHP dependencies:**
    ```bash
    composer install
    ```
3.  **Configure Environment:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
4.  **Database Setup:**
    * Configure your database connection in the `.env` file.
    * Run migrations to create tables:
        ```bash
        php artisan migrate
        ```
5.  **Serve the Application:**
    ```bash
    php artisan serve
    ```
    The application should now be accessible at `http://127.0.0.1:8000`.

---

## 🧑‍💻 Contributing

We welcome contributions! As a collaborative project, contributions from both institutions (and external developers) are valued. Please review our `CONTRIBUTING.md` (if available) before submitting pull requests.

---

## 📧 Contact

For support, inquiries, or further collaboration proposals, please contact:

* **Universitas Gunadarma Team:** [fabianjuliansyah89@gmail.com]
* **Politeknik Negeri Manado Team:** [fabianjuliansyah89@gmail.com]
