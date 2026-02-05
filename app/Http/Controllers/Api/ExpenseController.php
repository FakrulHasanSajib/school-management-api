<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExpenseCategory;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;

class ExpenseController extends Controller
{
    // ১. সব ক্যাটাগরি দেখা (ড্রপডাউনের জন্য) ✅ এটা মিসিং ছিল
    public function getCategories()
    {
        $categories = ExpenseCategory::all();
        return response()->json(['status' => true, 'data' => $categories]);
    }

    // ২. নতুন ক্যাটাগরি তৈরি করা
    public function storeCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:expense_categories,name',
            'description' => 'nullable|string'
        ]);

        $category = ExpenseCategory::create($validated);
        return response()->json(['message' => 'Category created', 'data' => $category], 201);
    }

    // ৩. খরচের লিস্ট দেখা (Expense List) ✅ এটা মিসিং ছিল
    public function index()
    {
        // created_at বা expense_date অনুযায়ী লেটেস্ট আগে দেখাবে
        $expenses = Expense::with('category')->latest('expense_date')->get();
        return response()->json(['status' => true, 'data' => $expenses]);
    }

    // ৪. খরচ যুক্ত করা
    public function storeExpense(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:1',
            'expense_date' => 'required|date',
            'description' => 'nullable|string'
        ]);

        $expense = Expense::create($validated);
        return response()->json(['message' => 'Expense added', 'data' => $expense], 201);
    }

    // ৫. খরচ ডিলিট করা ✅ এটা মিসিং ছিল
    public function destroy($id)
    {
        $expense = Expense::find($id);
        if ($expense) {
            $expense->delete();
            return response()->json(['status' => true, 'message' => 'Expense deleted successfully']);
        }
        return response()->json(['status' => false, 'message' => 'Expense not found'], 404);
    }
}
