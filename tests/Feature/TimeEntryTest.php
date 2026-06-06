<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Override;
use Tests\TestCase;

class TimeEntryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Employee $maleEmployee;
    private Employee $femaleEmployee;


    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();

        $this->maleEmployee =   Employee::create([
            'first_name' => 'juan',
            'last_name' => 'perez',
            'gender' => 'male',
            'hire_date' => '2024-01-15',
        ]);

        $this->femaleEmployee = Employee::create([
            'first_name' => 'María',
            'last_name' => 'López',
            'gender' => 'female',
            'hire_date' => '2024-01-15',
        ]);
    }

    public function test_admin_cannot_see_time_entries_page(): void
        {
            $response = $this->get('/time-entries');
            $response->assertRedirect('/login');
        }

    public function test_admin_can_see_time_entries_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/time-entries');
        $response->assertStatus(200);
        $response->assertSee('registro de tiempos');
    }

    public function test_admin_can_register_breakfast_for_male(): void
    {
        $response = $this->actingAs($this->admin)->post('/time-entries', [
            'employee_id' => $this->maleEmployee->id,
            'date' => '2026-06-05',
            'type' => 'breakfast',
            'time_out' => '09:00',
            'time_in' => '09:30',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('time_entries', [
            'employee_id' => $this->maleEmployee->id,
            'type' => 'breakfast',
            'minutes_taken' => 30,
            'minutes_owed' => 0,
        ]);
    }

    public function test_male_owes_minutes_when_exceeds_breakfast_limit(): void
    {
        $this->actingAs($this->admin)->post('/time-entries', [
            'employee_id' => $this->maleEmployee->id,
            'date' => '2026-06-05',
            'type' => 'breakfast',
            'time_out' => '09:00',
            'time_in' => '09:45',
        ]);

        $this->assertDatabaseHas('time_entries', [
            'employee_id' => $this->maleEmployee->id,
            'type' => 'breakfast',
            'minutes_taken' => 45,
            'minutes_owed' => 15,
        ]);
    }

    public function test_male_owes_minutes_when_exceeds_lunch_limit(): void
    {
        $this->actingAs($this->admin)->post('/time-entries', [
            'employee_id' => $this->maleEmployee->id,
            'date' => '2026-06-05',
            'type' => 'lunch',
            'time_out' => '12:00',
            'time_in' => '13:15',
        ]);

        $this->assertDatabaseHas('time_entries', [
            'employee_id' => $this->maleEmployee->id,
            'type' => 'lunch',
            'minutes_taken' => 75,
            'minutes_owed' => 15,
        ]);
    }

    public function test_female_owes_minutes_when_exceeds_breakfast_limit(): void
    {
        $this->actingAs($this->admin)->post('/time-entries', [
            'employee_id' => $this->femaleEmployee->id,
            'date' => '2026-06-05',
            'type' => 'breakfast',
            'time_out' => '09:00',
            'time_in' => '09:25',
        ]);

        $this->assertDatabaseHas('time_entries', [
            'employee_id' => $this->femaleEmployee->id,
            'type' => 'breakfast',
            'minutes_taken' => 25,
            'minutes_owed' => 10,
        ]);
    }

    public function test_female_owes_minutes_when_exceeds_lunch_limit(): void
    {
        $this->actingAs($this->admin)->post('/time-entries', [
            'employee_id' => $this->femaleEmployee->id,
            'date' => '2026-06-05',
            'type' => 'lunch',
            'time_out' => '12:00',
            'time_in' => '12:45',
        ]);

        $this->assertDatabaseHas('time_entries', [
            'employee_id' => $this->femaleEmployee->id,
            'type' => 'lunch',
            'minutes_taken' => 45,
            'minutes_owed' => 15,
        ]);
    }

    public function test_no_debt_when_within_allowed_time(): void
    {
        $this->actingAs($this->admin)->post('/time-entries', [
            'employee_id' => $this->femaleEmployee->id,
            'date' => '2026-06-05',
            'type' => 'breakfast',
            'time_out' => '09:00',
            'time_in' => '09:10',
        ]);

        $this->assertDatabaseHas('time_entries', [
            'employee_id' => $this->femaleEmployee->id,
            'minutes_taken' => 10,
            'minutes_owed' => 0,
        ]);
    }

    public function test_no_debt_when_exact_allowed_time(): void
    {
        $this->actingAs($this->admin)->post('/time-entries', [
            'employee_id' => $this->maleEmployee->id,
            'date' => '2026-06-05',
            'type' => 'lunch',
            'time_out' => '12:00',
            'time_in' => '13:00',
        ]);

        $this->assertDatabaseHas('time_entries', [
            'employee_id' => $this->maleEmployee->id,
            'minutes_taken' => 60,
            'minutes_owed' => 0,
        ]);
    }

    public function test_validation_rejects_missing_fields(): void
    {
        $response = $this->actingAs($this->admin)->post('/time-entries', []);
        $response->assertSessionHasErrors(['employee_id', 'date', 'type', 'time_out', 'time_in']);
    }

    public function test_validation_rejects_time_in_before_time_out(): void
    {
        $response = $this->actingAs($this->admin)->post('/time-entries', [
            'employee_id' => $this->maleEmployee->id,
            'date' => '2026-06-05',
            'type' => 'breakfast',
            'time_out' => '09:30',
            'time_in' => '09:00',
        ]);

        $response->assertSessionHasErrors(['time_in']);
    }

    public function test_admin_can_edit_time_entry_with_reason(): void
    {
        $entry = TimeEntry::create([
            'employee_id' => $this->maleEmployee->id,
            'date' => '2026-06-05',
            'type' => 'lunch',
            'time_out' => '12:00',
            'time_in' => '13:15',
            'minutes_taken' => 75,
            'minutes_owed' => 15,
        ]);

        $response = $this->actingAs($this->admin)->put("/time-entries/{$entry->id}", [
            'time_out' => '12:00',
            'time_in' => '13:00',
            'reason' => 'Error de digitación',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('time_entries', [
            'id' => $entry->id,
            'minutes_taken' => 60,
            'minutes_owed' => 0,
        ]);

        $this->assertDatabaseHas('time_adjustments', [
            'time_entry_id' => $entry->id,
            'user_id' => $this->admin->id,
            'old_minutes_owed' => 15,
            'new_minutes_owed' => 0,
            'reason' => 'Error de digitación',
        ]);
    }

    public function test_edit_requires_reason(): void
    {
        $entry = TimeEntry::create([
            'employee_id' => $this->maleEmployee->id,
            'date' => '2026-06-05',
            'type' => 'lunch',
            'time_out' => '12:00',
            'time_in' => '13:15',
            'minutes_taken' => 75,
            'minutes_owed' => 15,
        ]);

        $response = $this->actingAs($this->admin)->put("/time-entries/{$entry->id}", [
            'time_out' => '12:00',
            'time_in' => '13:00',
            'reason' => '',
        ]);

        $response->assertSessionHasErrors(['reason']);
    }

    public function test_admin_can_delete_time_entry(): void
    {
        $entry = TimeEntry::create([
            'employee_id' => $this->maleEmployee->id,
            'date' => '2026-06-05',
            'type' => 'breakfast',
            'time_out' => '09:00',
            'time_in' => '09:30',
            'minutes_taken' => 30,
            'minutes_owed' => 0,
        ]);

        $response = $this->actingAs($this->admin)->delete("/time-entries/{$entry->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('time_entries', ['id' => $entry->id]);
    }
}
