# MewMoney

MewMoney is a personal finance management web application developed as part of a qualification project. It helps users manage their finances, track income and expenses, and monitor their savings goals through a simple and user-friendly interface.

## Features

- **User Authentication** – Register, log in, and manage a personal account.
- **Dashboard** – View total balance, monthly income, and monthly expenses.
- **Transaction Management** – Create, edit, delete, and manage financial transactions.
- **Categories** – Organize income and expenses into categories.
- **Transaction Filtering** – Filter transactions by type and time period.
- **Savings Goals** – Create savings goals, track progress, and manage deposits.
- **Statistics and Charts** – Visualize financial data and expenses by category.
- **Admin Panel** – Manage users and view user statistics.

## Technologies Used

- PHP 8.3
- Laravel 13
- MySQL
- Blade
- HTML
- CSS
- JavaScript
- Chart.js
- Vite

## Installation

### Prerequisites

Make sure you have the following installed:

- PHP
- Composer
- Node.js and npm
- MySQL
- A local development environment, such as Laragon

### Setup

1. Clone the repository:

   ```bash
   git clone <https://github.com/Marta2852/MewMoney>
   ```

2. Navigate to the project directory:

   ```bash
   cd MewMoney
   ```

3. Install PHP dependencies:

   ```bash
   composer install
   ```

4. Install JavaScript dependencies:

   ```bash
   npm install
   ```

5. Create your environment file by copying `.env.example` and renaming the copy to `.env`.

6. Generate the application key:

   ```bash
   php artisan key:generate
   ```

7. Configure your database credentials in the `.env` file and create a MySQL database named `MewMoney`.

8. Run the database migrations:

   ```bash
   php artisan migrate
   ```

9. Start the development server:

   ```bash
   php artisan serve
   ```

10. In a separate terminal, run:

    ```bash
    npm run dev
    ```

11. Open the application in your browser at http://127.0.0.1:8000.

## Database

MewMoney uses a MySQL database to store user information, financial accounts, categories, transactions, and savings goals.

## Project Purpose

The purpose of MewMoney is to provide a straightforward way to manage personal finances, understand spending habits, track transactions, and work towards financial goals.

