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

        $employees = Employee::where('is_active', true)
            ->with(['timeEntries' => function ($query) use ($date) {
                $query->where('date', $date);
            }])
            ->orderBy('first_name')
            ->get();

        // Organizar los registros por empleado para fácil acceso en la vista
        $employees->each(function ($employee) {
            $employee->breakfast = $employee->timeEntries->where('type', 'breakfast')->first();
            $employee->lunch = $employee->timeEntries->where('type', 'lunch')->first();
        });

        return view('time-entries.index', compact('employees', 'date'));
    }

    public function clockOut(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'type' => 'required|in:breakfast,lunch',
        ]);

        // Verificar que no exista ya un registro para ese empleado, fecha y tipo
        $exists = TimeEntry::where('employee_id', $validated['employee_id'])
            ->where('date', $validated['date'])
            ->where('type', $validated['type'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Ya existe un registro para este empleado y tipo de comida hoy.');
        }

        TimeEntry::create([
            'employee_id' => $validated['employee_id'],
            'date' => $validated['date'],
            'type' => $validated['type'],
            'time_out' => now()->format('H:i:s'),
            'time_in' => null,
            'minutes_taken' => 0,
            'minutes_owed' => 0,
        ]);

        $employee = Employee::find($validated['employee_id']);
        $typeLabel = $validated['type'] === 'breakfast' ? 'desayuno' : 'almuerzo';

        return back()->with('success', "Salida de {$typeLabel} registrada para {$employee->full_name}.");
    }

    public function clockIn(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'type' => 'required|in:breakfast,lunch',
        ]);

        $entry = TimeEntry::where('employee_id', $validated['employee_id'])
            ->where('date', $validated['date'])
            ->where('type', $validated['type'])
            ->whereNull('time_in')
            ->first();

        if (!$entry) {
            return back()->with('error', 'No se encontró un registro de salida pendiente.');
        }

        $timeOut = Carbon::parse($entry->time_out);
        $timeIn = now();
        $minutesTaken = $timeOut->diffInMinutes($timeIn);

        $employee = Employee::find($validated['employee_id']);
        $allowedMinutes = $employee->getAllowedMinutes($validated['type']);
        $minutesOwed = max(0, $minutesTaken - $allowedMinutes);

        $entry->update([
            'time_in' => $timeIn->format('H:i:s'),
            'minutes_taken' => $minutesTaken,
            'minutes_owed' => $minutesOwed,
        ]);

        $typeLabel = $validated['type'] === 'breakfast' ? 'desayuno' : 'almuerzo';
        $message = "Regreso de {$typeLabel} registrado para {$employee->full_name}. ";
        $message .= $minutesOwed > 0 ? "Debe {$minutesOwed} minutos." : "Sin tiempo pendiente.";

        return back()->with('success', $message);
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
