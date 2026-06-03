<?php

namespace Tests\Unit;

use App\Models\Employee;
use PHPUnit\Framework\TestCase;

class EmployeeTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function test_allowed_minutes_breakfast_male():void
    {
        $employee = new Employee(['gender' => 'male']);
        $this->assertEquals(30, $employee->getAllowedminutes('breakfast'));
    }

    public function test_allowed_minutes_lunch_male():void
    {
        $employee = new Employee(['gender' => 'male']);
        $this->assertEquals(60, $employee->getAllowedminutes('lunch'));
    }

    public function test_allowed_minutes_breakfast_female():void
    {
        $employee = new Employee(['gender' => 'female']);
        $this->assertEquals(15, $employee->getAllowedminutes('breakfast'));
    }

    public function test_allowed_minutes_lunch_female():void
    {
        $employee = new Employee(['gender' => 'female']);
        $this->assertEquals(30, $employee->getAllowedminutes('lunch'));
    }

    public function test_full_name_attribute(): void
    {
        $employee = new Employee([
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
        ]);
        $this->assertEquals('Juan Pérez', $employee->full_name);
    }
}
