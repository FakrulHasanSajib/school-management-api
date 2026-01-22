<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AcademicController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\Api\RoutineController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\StudentController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\HRController;
use App\Http\Controllers\Api\LibraryController;
use App\Http\Controllers\Api\NoticeController; // ✅ নতুন কন্ট্রোলার
use App\Http\Controllers\Api\ExpenseController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Sanctum Auth)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // --- User & Auth ---
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);


    // --- 1. Academic Module ---
    Route::prefix('academic')->group(function () {
        Route::get('/classes', [AcademicController::class, 'indexClass']);
        Route::post('/classes', [AcademicController::class, 'storeClass']);
        Route::post('/sections', [AcademicController::class, 'storeSection']);
        Route::post('/subjects', [AcademicController::class, 'storeSubject']);
        Route::get('/classes/{classId}/subjects', [AcademicController::class, 'getSubjects']);
    });


    // --- 2. Teacher Module ---
    Route::prefix('teachers')->group(function () {
        Route::get('/', [TeacherController::class, 'index']);
        Route::post('/', [TeacherController::class, 'store']);
    });


    // --- 3. Routine Module ---
    Route::prefix('routines')->group(function () {
        Route::post('/', [RoutineController::class, 'store']);
        Route::get('/section/{sectionId}', [RoutineController::class, 'getBySection']);
    });


    // --- 4. Attendance Module ---
    Route::prefix('attendance')->group(function () {
        Route::post('/', [AttendanceController::class, 'store']);
        Route::get('/report', [AttendanceController::class, 'report']);
    });


    // --- 5. Student Module ---
    Route::prefix('students')->group(function () {
        Route::post('/admit', [StudentController::class, 'store']);
        Route::get('/', [StudentController::class, 'index']);
        Route::get('/section/{section_id}', [StudentController::class, 'getBySection']);
        Route::get('/{id}', [StudentController::class, 'show']);
        Route::put('/{id}', [StudentController::class, 'update']);
    });


    // --- 6. Exam Module ---
    // নোট: টেস্ট ফাইলের সাথে মিল রাখতে রুটগুলো বাইরে রাখা হয়েছে
    Route::post('/exams', [ExamController::class, 'store']);
    Route::post('/marks', [ExamController::class, 'storeMarks']);
    
    Route::prefix('exams')->group(function () {
        Route::get('/{exam_id}/results/{student_id}', [ExamController::class, 'getStudentResult']);
    });


    // --- 7. Accounts Module ---
    Route::prefix('accounts')->group(function () {
        Route::post('/invoices', [AccountController::class, 'generateInvoice']);
        Route::post('/payments', [AccountController::class, 'payInvoice']);
        Route::get('/student/{student_id}/invoices', [AccountController::class, 'getStudentInvoices']);
    });


    // --- 8. HR Module ---
    Route::prefix('hr')->group(function () {
        Route::post('/designations', [HRController::class, 'storeDesignation']); // ✅ টেস্টে ব্যবহৃত
        Route::post('/payroll/pay', [HRController::class, 'paySalary']);       // ✅ টেস্টে ব্যবহৃত
        Route::post('/payroll', [HRController::class, 'storePayroll']);
        Route::post('/leave', [HRController::class, 'storeLeave']);
        Route::patch('/leave/{id}/status', [HRController::class, 'updateLeaveStatus']);
    });


    // --- 9. Library Module ---
    Route::prefix('library')->group(function () {
        Route::post('/books', [LibraryController::class, 'storeBook']); // ✅ টেস্টে ব্যবহৃত
        Route::post('/issue', [LibraryController::class, 'issue']);     // ✅ টেস্টে ব্যবহৃত
        Route::post('/return/{id}', [LibraryController::class, 'returnBook']);
    });


    // --- 10. Notice Board Module ---
    Route::prefix('notices')->group(function () {
        Route::get('/', [NoticeController::class, 'index']);  // ✅ টেস্টে ব্যবহৃত (সব নোটিশ দেখা)
        Route::post('/', [NoticeController::class, 'store']); // ✅ টেস্টে ব্যবহৃত (নোটিশ তৈরি)
    });
    // --- 11. Expense Module ---
    Route::prefix('expenses')->group(function () {
        Route::post('/categories', [ExpenseController::class, 'storeCategory']); // ক্যাটাগরি তৈরি
        Route::post('/', [ExpenseController::class, 'storeExpense']); // খরচ যোগ করা
    });
});