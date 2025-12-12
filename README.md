# 🚀 Student Performance Prediction System (PHP + ML)

This project is a PHP-based web application developed as the Final Requirement for **ITEP 308 - System Integration and Architecture I**. It demonstrates system integration by leveraging **Composer** to include a Machine Learning library (`php-ai/php-ml`) for student performance prediction.

The system predicts three critical outcomes: **Pass/Fail status**, **Dropout Risk**, and **Tutoring Need**.

## 🔗 Project & Submission Links

**IMPORTANT:** These links fulfill the final requirement submission criteria. Please ensure they are updated before submission.

* **Deployed System Link:** [https://studentpredict.ct.ws/index.php]
* **Video Presentation Link:** [https://youtu.be/giztcvOObw0?si=_gzsnoVcutnsd9oH]
* **Canva/PowerPoint Link:** [https://www.canva.com/design/DAG6-27oEJo/Sphmr0wmNGu38KjOvfA0cw/edit?utm_content=DAG6-27oEJo&utm_campaign=designshare&utm_medium=link2&utm_source=sharebutton]

---

## Technology Stack & Requirements

### Core Stack
* **Backend:** PHP
* **Database:** MySQL/MariaDB (Used via `database.sql`)
* **Dependency Management:** Composer

### Machine Learning Integration
* **Library:** `php-ai/php-ml`
* **Models:** Naive Bayes and Decision Tree Classifiers

## System Architecture

The system operates on a standard three-tier model (Presentation, Application, Data) with a critical ML integration layer:

1.  **Presentation:** HTML/CSS rendered by PHP scripts.
2.  **Application Logic:** PHP handles authentication, student data management, and the core prediction logic using the `MLPredictor` class.
3.  **ML Integration:** The `MLPredictor` uses `php-ai/php-ml` to perform training (`train.php`) and prediction (`predict.php`).
4.  **Data Layer:** MySQL stores student records, admin users, and the **serialized ML models** in the `ml_models` table for persistence.

---

## Installation and Setup Guide

Follow these steps to set up the project locally:

### 1. Database Setup

1.  Create a new MySQL database named `student_prediction`.
2.  Import the schema and seed data by running the `database.sql` file in your database management tool (e.g., phpMyAdmin, MySQL Workbench).
    * **Default Admin User:** `admin` / `admin123`

### 2. Project Files and Dependencies

1.  Clone this repository to your local web server root (e.g., `htdocs` or `www`).
2.  Install PHP dependencies using Composer:
    ```bash
    composer install
    ```
3.  Ensure your database connection details (in the assumed `config/database.php` file) are correct.

### 3. Training the ML Models (Crucial Step)

The models must be trained before predictions can be made.

1.  Log in as the admin user.
2.  Navigate to the **Train Models** page (`train.php`).
3.  Upload the included training dataset: `sample_dataset.csv`.
4.  Click **"Train Models"** to run the classifier and save the results (including accuracy) to the `ml_models` table.

---

## Key Features

* **Dashboard (`index.php`):** Provides an overview of academic statistics (Avg Attendance, Avg Grade).
* **Secure Authentication:** Admin login enforced via the `Auth` class.
* **ML Prediction (`predict.php`):** Generates three predictions for selected students:
    1.  **Pass/Fail Status** (using Naive Bayes)
    2.  **Dropout Risk** (using Decision Tree)
    3.  **Tutoring Need** (using Naive Bayes)
* **Actionable Recommendations:** Provides specific advice based on the ML output (e.g., "Schedule counseling session," "Improve attendance").
* **Model Transparency (`models.php`):** Displays the training history, accuracy, and algorithms used.

---
