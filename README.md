# 🏫 School Management System API

![Laravel](https://img.shields.io/badge/Laravel-10%2F11-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-00000F?style=for-the-badge&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

A comprehensive and robust **School Management System** backend built with **Laravel**. This API serves as the backbone for managing school operations, including students, teachers, attendance, exams, routines, and accounts. It is designed to work seamlessly with a **Vue.js** frontend.

---

## 🚀 Features

### 👤 Role-Based Access Control (RBAC)
- **Super Admin / Admin:** Full control over the system.
- **Teacher:** Can take attendance, enter marks, view routine, and manage assigned sections.
- **Student:** Can view results, routine, and attendance reports.

### 📚 Academic Management
- **Classes & Sections:** Manage classes and sections dynamically.
- **Subject Management:** Assign subjects to classes.
- **Class Teacher Assignment:** Assign specific teachers to sections for administrative tasks.
- **Routine System:** Dynamic class routine management for students and teachers.

### 👨‍🏫 Teacher Module
- **Teacher Profile:** Manage teacher details, designation, and qualifications.
- **My Routine:** Teachers can view their individual class schedules.
- **Class Teacher Access:** Special permissions for assigned class teachers.

### 🎓 Student Module
- **Admission System:** Admit new students with auto-generated Admission No & Roll No.
- **Student Promotion:** Promote students to the next class.
- **Profile Management:** View and update student details.

### 📅 Attendance System
- **Daily Attendance:** Teachers can take attendance for their sections.
- **Date-wise Reports:** Generate attendance reports for specific dates.
- **Student Reports:** View individual attendance history.

### 📝 Exam & Result Management
- **Exam Creation:** Schedule exams and manage dates.
- **Marks Entry:** Teachers can input marks for their subjects.
- **Tabulation Sheet:** Auto-generate result sheets and grade calculations.

### 💰 Accounts & HR (Bonus)
- **Fee Management:** Collect fees and generate invoices.
- **Payroll:** Manage teacher and staff salaries.
- **Expense Tracking:** Track school expenses.

---

## 🛠 Tech Stack

- **Backend Framework:** Laravel 10/11
- **Database:** MySQL
- **Authentication:** Laravel Sanctum
- **Language:** PHP 8.1+

---

## ⚙️ Installation Guide

Follow these steps to set up the project locally:

### 1. Clone the Repository
```bash
git clone [https://github.com/FakrulHasanSajib/school-management-api.git](https://github.com/FakrulHasanSajib/school-management-api.git)
cd school-management-api
2. Install DependenciesBash
composer install
3. Environment SetupRename the .env.example file to .env:
cp .env.example .env
Open .env file and configure your database credentials:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=school_db
DB_USERNAME=root
DB_PASSWORD=
(Make sure to create a database named school_db in your MySQL server)
4. Generate App Key
php artisan key:generate
5. Link Storage (For Images)
php artisan storage:link
6. Migrate & Seed DatabaseRun migrations to create tables and seed default data (Admin user, etc.):
php artisan migrate --seed
7. Run the Server
php artisan serve
The API will be available at: http://127.0.0.1:8000🔗
API Endpoints Overview🔐 Authentication
Method Endpoint Description
POST  /api/login  User login (Admin/Teacher/Student)
POST  /api/logout  User logout

👨‍🏫 Teacher Module
Method Endpoint Description
GET     /api/teachers  Get all teachers listPOST/api/teachers
Create a new teacher
GET  /api/teacher/my-routine  Get logged-in teacher's routine
📅 Attendance
 Method Endpoint Description
POST   /api/attendance/get-studentsGet   student list for attendance
POST   /api/attendance/storeSubmit       daily attendance
🎓 Academic
Method Endpoint Description
GET   /api/academic/classes  Get all classes
POST  /api/academic/assign-teacher Assign a class teacher to a section
🤝 Contribution
Contributions are welcome!
Fork the repository.
Create a new feature branch (git checkout -b feature-name).
Commit your changes (git commit -m 'Add some feature').
Push to the branch (git push origin feature-name).
Open a Pull Request.
📝 LicenseThis project is open-sourced software licensed under the MIT license.
Developed with ❤️ by Fakrul Hasan Sajib
