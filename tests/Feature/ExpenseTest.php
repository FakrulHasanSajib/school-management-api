<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ExpenseCategory;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_create_expense_category()
    {
        Role::findOrCreate('super-admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        Sanctum::actingAs($admin, ['*']);

        $response = $this->postJson('/api/expenses/categories', [
            'name' => 'Office Supplies',
            'description' => 'Pen, paper, ink etc.'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('expense_categories', ['name' => 'Office Supplies']);
    }

    #[Test]
    public function admin_can_add_expense()
    {
        Role::findOrCreate('super-admin', 'web');
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        Sanctum::actingAs($admin, ['*']);

        $category = ExpenseCategory::create(['name' => 'Internet Bill']);

        $response = $this->postJson('/api/expenses', [
            'expense_category_id' => $category->id,
            'amount' => 1500,
            'expense_date' => now()->toDateString(),
            'description' => 'Monthly broadband bill'
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('expenses', [
            'amount' => 1500,
            'expense_category_id' => $category->id
        ]);
    }
}