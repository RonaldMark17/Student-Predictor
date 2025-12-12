**Student Performance Prediction System (PHP + ML)**
This project is a PHP-based web application developed as the Final Requirement for ITEP 308 - System Integration and Architecture I. It demonstrates system integration by leveraging Composer to include a Machine Learning library (php-ai/php-ml) for student performance prediction.

The system predicts three critical outcomes: Pass/Fail status, Dropout Risk, and Tutoring Need.

**Project & Submission Links**
As required by the course documentation, here are the links for the complete submission package:

Deployed System Link: [ https://studentpredict.ct.ws/index.php ]
Video Presentation Link: [ https://youtu.be/giztcvOObw0?si=_gzsnoVcutnsd9oH ]
Canva/PowerPoint Link: [ https://www.canva.com/design/DAG6-27oEJo/Sphmr0wmNGu38KjOvfA0cw/edit?utm_content=DAG6-27oEJo&utm_campaign=designshare&utm_medium=link2&utm_source=sharebutton ]

**Technology Stack & Requirements**
Core Stack
Backend: PHP (Minimum PHP 7.4)
Database: MySQL/MariaDB (Used via database.sql)
Dependency Management: Composer

**Machine Learning Integration**
Library: php-ai/php-ml
Models: Naive Bayes and Decision Tree Classifiers

**System Architecture**
The system operates on a standard three-tier model (Presentation, Application, Data) with a critical ML integration layer:
Presentation: HTML/CSS rendered by PHP scripts.
Application Logic: PHP handles authentication (Auth.php), data management (Student.php), and model execution (MLPredictor.php).
ML Integration: The MLPredictor uses php-ai/php-ml to perform training (train.php) and prediction (predict.php).
Data Layer: MySQL stores student records, admin users, and the serialized ML models in the ml_models table for persistence.

**Installation and Setup Guide**
Follow these steps to set up the project locally:

**1. Database Setup**
Create a new MySQL database named student_prediction.
Import the schema and seed data by running the database.sql file in your database management tool (e.g., phpMyAdmin, MySQL Workbench).
Default Admin User: admin / admin123

**2. Project Files and Dependencies**
Clone this repository to your local web server root (e.g., htdocs or www).
Install PHP dependencies using Composer:
Bash

composer install
Database Connection: Ensure your database credentials in the config/database.php file (not provided, but assumed based on file structure) are correct for your local environment.

**3. Training the ML Models**
The models must be trained before predictions can be made.
Log in as the admin user.
Navigate to the Train Models page (train.php).
Upload the included training dataset: sample_dataset.csv.
Click "Train Models" to run the classifier and save the results (including accuracy) to the ml_models table.

**Key Features**
Dashboard (index.php): Provides an overview of academic statistics (Avg Attendance, Avg Grade).
Secure Authentication: Admin login enforced via the Auth class.
ML Prediction (predict.php): Generates three predictions for selected students:
Pass/Fail Status (using Naive Bayes)
Dropout Risk (using Decision Tree)
Tutoring Need (using Naive Bayes)
Actionable Recommendations: Provides specific advice based on the ML output and student features (e.g., "Schedule counseling session," "Improve attendance").
Model Transparency (models.php): Displays the training history, accuracy, and algorithms used.

📊 Training Data Format
The CSV file used for training models must contain these specific columns:
