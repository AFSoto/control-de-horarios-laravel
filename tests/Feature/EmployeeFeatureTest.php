<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    public function test_guest_cannot_access_employees(): void
    {
        $response = $this->get('/employees');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_see_employees_list(): void
    {
        $response = $this->actingAs($this->admin)->get('/employees');
        $response->assertStatus(200);
        $response->assertSee('Empleados');
    }

    public function test_admin_can_create_employee(): void
    {
        $response = $this->actingAs($this->admin)->post('/employees', [
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'gender' => 'male',
            'position' => 'Operario',
            'hire_date' => '2024-01-15',
        ]);

        $response->assertRedirect('/employees');
        $this->assertDatabaseHas('employees', [
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'gender' => 'male',
        ]);
    }

    public function test_admin_cannot_create_employee_without_required_fields(): void
    {
        $response = $this->actingAs($this->admin)->post('/employees', [
            'first_name' => '',
            'last_name' => '',
            'gender' => '',
            'hire_date' => '',
        ]);

        $response->assertSessionHasErrors(['first_name', 'last_name', 'gender', 'hire_date']);
    }

    public function test_admin_can_update_employee(): void
    {
        $employee = Employee::create([
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'gender' => 'male',
            'hire_date' => '2024-01-15',
        ]);

        $response = $this->actingAs($this->admin)->put("/employees/{$employee->id}", [
            'first_name' => 'Carlos',
            'last_name' => 'Pérez',
            'gender' => 'male',
            'hire_date' => '2024-01-15',
        ]);

        $response->assertRedirect('/employees');
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'first_name' => 'Carlos',
        ]);
    }

    public function test_destroy_deactivates_instead_of_deleting(): void
    {
        $employee = Employee::create([
            'first_name' => 'María',
            'last_name' => 'López',
            'gender' => 'female',
            'hire_date' => '2024-03-01',
        ]);

        $response = $this->actingAs($this->admin)->delete("/employees/{$employee->id}");

        $response->assertRedirect('/employees');
        $this->assertDatabaseHas('employees', [
            'id' => $employee->id,
            'is_active' => false,
        ]);
    }
}
