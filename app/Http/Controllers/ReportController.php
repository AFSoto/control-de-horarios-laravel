<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $employeeId = $request->get('employee_id');

        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        $query = Employee::where('is_active', true)
            ->with(['timeEntries' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate])
                    ->orderBy('date');
            }])
            ->orderBy('first_name');

        if ($employeeId) {
            $query->where('id', $employeeId);
        }

        $employees = $query->get();

        $report = $employees->map(function ($employee) {
            $totalOwed = $employee->timeEntries->sum('minutes_owed');
            $breakfastOwed = $employee->timeEntries->where('type', 'breakfast')->sum('minutes_owed');
            $lunchOwed = $employee->timeEntries->where('type', 'lunch')->sum('minutes_owed');
            $totalEntries = $employee->timeEntries->count();
            $daysWithDebt = $employee->timeEntries->where('minutes_owed', '>', 0)->groupBy(function ($entry) {
                return $entry->date->toDateString();
            })->count();

            return (object) [
                'employee' => $employee,
                'total_owed' => $totalOwed,
                'breakfast_owed' => $breakfastOwed,
                'lunch_owed' => $lunchOwed,
                'total_entries' => $totalEntries,
                'days_with_debt' => $daysWithDebt,
            ];
        });

        $allEmployees = Employee::where('is_active', true)->orderBy('first_name')->get();

        return view('reports.index', compact('report', 'month', 'employeeId', 'allEmployees'));
    }

    public function detail(Request $request, Employee $employee)
    {
        $month = $request->get('month', now()->format('Y-m'));
        $startDate = Carbon::parse($month)->startOfMonth();
        $endDate = Carbon::parse($month)->endOfMonth();

        $entries = TimeEntry::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->with('adjustments.user')
            ->orderBy('date')
            ->orderBy('type')
            ->get();

        $totalOwed = $entries->sum('minutes_owed');

        return view('reports.detail', compact('employee', 'entries', 'month', 'totalOwed'));
    }
}
