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
use App\Http\Controllers\Api\NoticeController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\Dashboard\DashboardController;
use App\Http\Controllers\Api\GeneralSettingController;
use App\Http\Controllers\Api\ResultController;
use App\Http\Controllers\Api\SslCommerzController;

/*
|--------------------------------------------------------------------------
| Public Routes (Login & Helpers)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);

// হেল্পার রাউট (লগইন ছাড়াই এক্সেসযোগ্য, প্রয়োজনে অথ গ্রুপের ভেতরে নিতে পারেন)
Route::get('academic/classes/{classId}/sections', [AcademicController::class, 'getSectionsByClass']);
Route::get('/students/next-numbers', [StudentController::class, 'getNextNumbers']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Sanctum Auth) - Login Required
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // --- User & Auth ---
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    // --- 1. Academic Module ---
    Route::prefix('academic')->group(function () {
        Route::get('/classes', [AcademicController::class, 'indexClass']);
        Route::post('/classes', [AcademicController::class, 'storeClass']);
        Route::post('/sections', [AcademicController::class, 'storeSection']);
        Route::post('/subjects', [AcademicController::class, 'storeSubject']);
        Route::get('/classes/{classId}/subjects', [AcademicController::class, 'getSubjects']);
        Route::get('/sections', [AcademicController::class, 'indexSection']);
    });

    // --- 2. Teacher Module ---
    Route::prefix('teachers')->group(function () {
        Route::get('/', [TeacherController::class, 'index']);
        Route::post('/', [TeacherController::class, 'store']);
        Route::get('/{id}', [TeacherController::class, 'show']);
        Route::put('/{id}', [TeacherController::class, 'update']);
        Route::delete('/{id}', [TeacherController::class, 'destroy']);
    });

    // --- 3. Routine Module ---
    Route::prefix('routines')->group(function () {
        Route::post('/', [RoutineController::class, 'store']);
        Route::get('/section/{sectionId}', [RoutineController::class, 'getBySection']);
        Route::get('/', [RoutineController::class, 'index']);
        Route::delete('/{id}', [RoutineController::class, 'destroy']);
        Route::get('/{id}', [RoutineController::class, 'show']);
        Route::put('/{id}', [RoutineController::class, 'update']);
    });

    // --- 4. Attendance Module ---
    Route::prefix('attendance')->group(function () {
        Route::post('/store', [AttendanceController::class, 'store']);
        Route::get('/report', [AttendanceController::class, 'report']);
        Route::get('/student/{studentId}/report-card', [AttendanceController::class, 'studentReportCard']);
    });

    // --- 5. Student Module ---
    Route::prefix('students')->group(function () {
        Route::post('/admit', [StudentController::class, 'store']);
        Route::get('/', [StudentController::class, 'index']);
        Route::get('/section/{section_id}', [StudentController::class, 'getBySection']);
        Route::get('/{id}', [StudentController::class, 'show']);
        Route::put('/{id}', [StudentController::class, 'update']);
        Route::delete('/{id}', [StudentController::class, 'destroy']);
    });

    // --- 6. Exam Module ---
    Route::get('/exams', [ExamController::class, 'index']);
    Route::post('/exams', [ExamController::class, 'store']);
    Route::post('/marks', [ExamController::class, 'storeMarks']);
    Route::get('/exams/{id}', [ExamController::class, 'show']);
    Route::put('/exams/{id}', [ExamController::class, 'update']);

    // --- 7. Result Module ---
    Route::get('/results/exam/{exam_id}/student/{student_id}', [ResultController::class, 'getStudentResult']);
    Route::get('/results/tabulation/exam/{exam_id}/section/{section_id}', [ResultController::class, 'getTabulationSheet']);


    // --- 8. Accounts Module (FIXED) ---
    Route::prefix('accounts')->group(function () {
        // Fee Types
        Route::get('/fee-types', [AccountController::class, 'getFeeTypes']);
        Route::post('/fee-types', [AccountController::class, 'storeFeeType']);

        // ✅ ফিক্স: এখানে '/accounts' বাদ দেওয়া হয়েছে কারণ উপরে prefix('accounts') আছে
        Route::put('/fee-types/{id}', [AccountController::class, 'updateFeeType']);
        Route::delete('/fee-types/{id}', [AccountController::class, 'deleteFeeType']);

        // Invoices & Payments
        Route::post('/invoices', [AccountController::class, 'generateInvoice']);
        Route::get('/student/{student_id}/invoices', [AccountController::class, 'getStudentInvoices']);
        Route::post('/payments', [AccountController::class, 'payInvoice']);
        Route::get('/history', [AccountController::class, 'getAllInvoices']);
    });

    // --- 9. HR Module ---
    Route::prefix('hr')->group(function () {
        Route::post('/designations', [HRController::class, 'storeDesignation']);
        Route::post('/payroll/pay', [HRController::class, 'paySalary']);
        Route::post('/payroll', [HRController::class, 'storePayroll']);
        Route::post('/leave', [HRController::class, 'storeLeave']);
        Route::patch('/leave/{id}/status', [HRController::class, 'updateLeaveStatus']);
    });

    // --- 10. Library Module ---
    Route::prefix('library')->group(function () {
        Route::post('/books', [LibraryController::class, 'storeBook']);
        Route::post('/issue', [LibraryController::class, 'issue']);
        Route::post('/return/{id}', [LibraryController::class, 'returnBook']);
    });

    // --- 11. Notice Board Module ---
    Route::prefix('notices')->group(function () {
        Route::get('/', [NoticeController::class, 'index']);
        Route::post('/', [NoticeController::class, 'store']);
    });

    // --- 12. Expense Module ---
    Route::prefix('expenses')->group(function () {
        Route::post('/categories', [ExpenseController::class, 'storeCategory']);
        Route::post('/', [ExpenseController::class, 'storeExpense']);
    });

    // --- 13. Dashboard Module ---
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'stats']);
    });

    // --- 14. General Settings ---
    Route::prefix('general-settings')->group(function () {
        Route::get('/', [GeneralSettingController::class, 'index']);
        Route::post('/update', [GeneralSettingController::class, 'update']);
    });

});
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/payment/init', [SslCommerzController::class, 'index']);
});

// ২. কলব্যাক রাউটস (এগুলো Auth এর বাইরে থাকবে)
Route::post('/payment/success', [SslCommerzController::class, 'success']);
Route::post('/payment/fail', [SslCommerzController::class, 'fail']);
Route::post('/payment/cancel', [SslCommerzController::class, 'cancel']);
