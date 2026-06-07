<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\TimeEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TimeEntryController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', now()->toDateString());

        $employees= Employee::where('is_active', true)
        ->with(['timeEntries' => function ($query) use ($date) {
            $query->where('date' , $date);
        }])
        ->orderBy('first_name')
        ->get();

        return view('time-entries.index', compact('employees' , 'date'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'type' => 'required|in:breakfast,lunch',
            'time_out' => 'required|date_format:H:i',
            'time_in' => 'required|date_format:H:i|after:time_out',
        ]);

        $employee = Employee::findOrFail($validated['employee_id']);

        $timeOut = Carbon::createFromFormat('H:i', $validated['time_out']);
        $timeIn = Carbon::createFromFormat('H:i', $validated['time_in']);
        $minutesTaken = $timeIn->diffInMinutes($timeOut);

        $allowedMinutes = $employee->getAllowedMinutes($validated['type']);
        $minutesOwed = max(0,$minutesTaken - $allowedMinutes);

        TimeEntry::create([
            'employee_id' => $employee->id,
            'date' => $validated['date'],
            'type' => $validated['type'],
            'time_out' => $validated['time_out'],
            'time_in' => $validated['time_in'],
            'minutes_taken' => $minutesTaken,
            'minutes_owed' => $minutesOwed,
        ]);

        return redirect()->route('time-entries.index', ['date' => $validated['date']])
        ->with('succes', "tiempo registrado para  {$employee->full_name}. " .
        ($minutesOwed > 0 ? "debe {$minutesOwed} minutos . " : "sin tiempo pendiente"));
    }

    public function edit(TimeEntry $timeEntry)
    {
        $timeEntry->load('employee');
        return view('time-entries.edit', compact('timeEntry'));
    }

    public function update(Request $request, TimeEntry $timeEntry)
    {
        $validated = $request->validate([
            'time_out' => 'required|date_format:H:i',
            'time_in' => 'required|date_format:H:i|after:time_out',
            'reason' => 'required|string|max:255',
        ]);

        $timeOut = Carbon::createFromFormat('H:i', $validated['time_out']);
        $timeIn = Carbon::createFromFormat('H:i', $validated['time_in']);
        $minutesTaken = $timeIn->diffInMinutes($timeOut);

        $allowedMinutes = $timeEntry->employee->getAllowedMinutes($timeEntry->type);
        $newMinutesOwed = max(0, $minutesTaken - $allowedMinutes);

        // Guardar el ajuste en el log
        $timeEntry->adjustments()->create([
            'user_id' => auth()->id(),
            'old_minutes_owed' => $timeEntry->minutes_owed,
            'new_minutes_owed' => $newMinutesOwed,
            'reason' => $validated['reason'],
        ]);

        $timeEntry->update([
            'time_out' => $validated['time_out'],
            'time_in' => $validated['time_in'],
            'minutes_taken' => $minutesTaken,
            'minutes_owed' => $newMinutesOwed,
        ]);

        return redirect()->route('time-entries.index', ['date' => $timeEntry->date->toDateString()])
            ->with('success', 'Registro actualizado correctamente.');
    }

    public function destroy(TimeEntry $timeEntry)
    {
        $date = $timeEntry->date->toDateString();
        $timeEntry->delete();

        return redirect()->route('time-entries.index', ['date' => $date])
            ->with('success', 'Registro eliminado.');
    }


}
