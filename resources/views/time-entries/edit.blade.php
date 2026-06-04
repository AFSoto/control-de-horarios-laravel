<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Registro: {{ $timeEntry->employee->full_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">

                    <div class="mb-4 p-4 bg-gray-50 rounded">
                        <p><strong>Empleado:</strong> {{ $timeEntry->employee->full_name }}</p>
                        <p><strong>Fecha:</strong> {{ $timeEntry->date->format('d/m/Y') }}</p>
                        <p><strong>Tipo:</strong> {{ $timeEntry->type === 'breakfast' ? 'Desayuno' : 'Almuerzo' }}</p>
                        <p><strong>Tiempo permitido:</strong> {{ $timeEntry->employee->getAllowedMinutes($timeEntry->type) }} min</p>
                        <p><strong>Debe actualmente:</strong> {{ $timeEntry->minutes_owed }} min</p>
                    </div>

                    <form method="POST" action="{{ route('time-entries.update', $timeEntry) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Hora Salida</label>
                            <input type="time" name="time_out"
                                   value="{{ old('time_out', \Carbon\Carbon::parse($timeEntry->time_out)->format('H:i')) }}"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('time_out') border-red-500 @enderror">
                            @error('time_out')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Hora Regreso</label>
                            <input type="time" name="time_in"
                                   value="{{ old('time_in', \Carbon\Carbon::parse($timeEntry->time_in)->format('H:i')) }}"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('time_in') border-red-500 @enderror">
                            @error('time_in')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Motivo del cambio *</label>
                            <textarea name="reason" rows="3"
                                      class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('reason') border-red-500 @enderror"
                                      placeholder="Ej: Error de digitación, el empleado reportó hora incorrecta...">{{ old('reason') }}</textarea>
                            @error('reason')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between">
                            <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Guardar Cambios
                            </button>
                            <a href="{{ route('time-entries.index', ['date' => $timeEntry->date->toDateString()]) }}"
                               class="text-gray-600 hover:text-gray-900">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
