<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\TimeEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Employee $employee;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();

        $this->employee = Employee::create([
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'gender' => 'male',
            'hire_date' => '2024-01-15',
        ]);
    }

    public function test_guest_cannot_access_reports(): void
    {
        $response = $this->get('/reports');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_see_reports_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/reports');
        $response->assertStatus(200);
        $response->assertSee('Reportes Mensuales');
    }

    public function test_report_shows_correct_monthly_totals(): void
    {
        TimeEntry::create([
            'employee_id' => $this->employee->id,
            'date' => '2026-06-01',
            'type' => 'breakfast',
            'time_out' => '09:00',
            'time_in' => '09:40',
            'minutes_taken' => 40,
            'minutes_owed' => 10,
        ]);

        TimeEntry::create([
            'employee_id' => $this->employee->id,
            'date' => '2026-06-02',
            'type' => 'lunch',
            'time_out' => '12:00',
            'time_in' => '13:20',
            'minutes_taken' => 80,
            'minutes_owed' => 20,
        ]);

        $response = $this->actingAs($this->admin)->get('/reports?month=2026-06');

        $response->assertStatus(200);
        $response->assertSee('Juan Pérez');
        $response->assertSee('10 min');
        $response->assertSee('20 min');
    }

    public function test_report_filters_by_employee(): void
    {
        $otherEmployee = Employee::create([
            'first_name' => 'María',
            'last_name' => 'López',
            'gender' => 'female',
            'hire_date' => '2024-01-15',
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/reports?month=2026-06&employee_id={$this->employee->id}");

        $response->assertStatus(200);
        $response->assertSee('Juan Pérez');
        $response->assertSee('Ver detalle');
    }

    public function test_detail_shows_employee_entries(): void
    {
        TimeEntry::create([
            'employee_id' => $this->employee->id,
            'date' => '2026-06-01',
            'type' => 'lunch',
            'time_out' => '12:00',
            'time_in' => '13:15',
            'minutes_taken' => 75,
            'minutes_owed' => 15,
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/reports/{$this->employee->id}?month=2026-06");

        $response->assertStatus(200);
        $response->assertSee('Juan Pérez');
        $response->assertSee('15 min');
    }

    public function test_report_excludes_other_months(): void
    {
        TimeEntry::create([
            'employee_id' => $this->employee->id,
            'date' => '2026-05-15',
            'type' => 'lunch',
            'time_out' => '12:00',
            'time_in' => '13:30',
            'minutes_taken' => 90,
            'minutes_owed' => 30,
        ]);

        $response = $this->actingAs($this->admin)->get('/reports?month=2026-06');

        $response->assertStatus(200);
        $response->assertDontSee('30 min');
    }

    public function test_employee_with_no_debt_shows_ok(): void
    {
        TimeEntry::create([
            'employee_id' => $this->employee->id,
            'date' => '2026-06-01',
            'type' => 'breakfast',
            'time_out' => '09:00',
            'time_in' => '09:20',
            'minutes_taken' => 20,
            'minutes_owed' => 0,
        ]);

        $response = $this->actingAs($this->admin)->get('/reports?month=2026-06');

        $response->assertStatus(200);
        $response->assertSee('OK');
    }
}
