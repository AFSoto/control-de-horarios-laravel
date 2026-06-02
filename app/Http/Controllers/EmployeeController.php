<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $employees = Employee::where('is_active',true)
        ->orderBy('first_name')
        ->get();

        return view('employees.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('employees.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'postion' => 'nullable|string|max:255',
            'hire_date' => 'required|date',
        ]);

        Employee::create($validated);

        return redirect()->route('employees.index')
        ->with('succes', 'empleado crreado correctamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Employee $employee)
    {
        return view('employees.edit',compact('employee'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Employee $employee)
    {
        $Validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'position' => 'nullable|string|max:255',
            'hire_date' => 'required|date',
        ]);

        $employee->update($Validated);

        return redirect()->route('employees.index')
        ->with('succes', 'empleado actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Employee $employee)
    {
        $employee->update(['is_active' => false]);

        return redirect()->route('employees.index')
        ->with('succes', 'empleado desactivado  exitosamente ');
    }
}
