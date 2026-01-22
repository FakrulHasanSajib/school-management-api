<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExpenseCategory;
use App\Models\Expense;
use Illuminate\Http\JsonResponse;

class ExpenseController extends Controller
{
    // ১. ক্যাটাগরি তৈরি
    public function storeCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:expense_categories,name',
            'description' => 'nullable|string'
        ]);

        $category = ExpenseCategory::create($validated);
        return response()->json(['message' => 'Category created', 'data' => $category], 201);
    }

    // ২. খরচ যুক্ত করা
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
}