<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Registro de Tiempos
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Selector de fecha --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <form method="GET" action="{{ route('time-entries.index') }}" class="flex items-center gap-4">
                    <label class="font-bold text-gray-700">Fecha:</label>
                    <input type="date" name="date" value="{{ $date }}"
                           class="border rounded py-2 px-3 text-gray-700">
                    <button type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Ver día
                    </button>
                </form>
            </div>

            {{-- Formulario de registro rápido --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-bold mb-4">Registrar Tiempo</h3>
                <form method="POST" action="{{ route('time-entries.store') }}">
                    @csrf
                    <input type="hidden" name="date" value="{{ $date }}">

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Empleado</label>
                            <select name="employee_id"
                                    class="w-full border rounded py-2 px-3 text-gray-700 @error('employee_id') border-red-500 @enderror">
                                <option value="">Seleccionar...</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ old('employee_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->full_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('employee_id')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Tipo</label>
                            <select name="type"
                                    class="w-full border rounded py-2 px-3 text-gray-700 @error('type') border-red-500 @enderror">
                                <option value="breakfast" {{ old('type') === 'breakfast' ? 'selected' : '' }}>Desayuno</option>
                                <option value="lunch" {{ old('type') === 'lunch' ? 'selected' : '' }}>Almuerzo</option>
                            </select>
                            @error('type')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Hora Salida</label>
                            <input type="time" name="time_out" value="{{ old('time_out') }}"
                                   class="w-full border rounded py-2 px-3 text-gray-700 @error('time_out') border-red-500 @enderror">
                            @error('time_out')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1">Hora Regreso</label>
                            <input type="time" name="time_in" value="{{ old('time_in') }}"
                                   class="w-full border rounded py-2 px-3 text-gray-700 @error('time_in') border-red-500 @enderror">
                            @error('time_in')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit"
                                class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">
                            Registrar
                        </button>
                    </div>
                </form>
            </div>

            {{-- Tabla de registros del día --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4">Registros del {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h3>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Empleado</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Salida</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Regreso</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tomados</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Permitidos</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Debe</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php $hasEntries = false; @endphp
                        @foreach($employees as $employee)
                            @foreach($employee->timeEntries as $entry)
                                @php $hasEntries = true; @endphp
                                <tr class="{{ $entry->minutes_owed > 0 ? 'bg-red-50' : '' }}">
                                    <td class="px-4 py-3">{{ $employee->full_name }}</td>
                                    <td class="px-4 py-3">{{ $entry->type === 'breakfast' ? 'Desayuno' : 'Almuerzo' }}</td>
                                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($entry->time_out)->format('h:i A') }}</td>
                                    <td class="px-4 py-3">{{ \Carbon\Carbon::parse($entry->time_in)->format('h:i A') }}</td>
                                    <td class="px-4 py-3">{{ $entry->minutes_taken }} min</td>
                                    <td class="px-4 py-3">{{ $employee->getAllowedMinutes($entry->type) }} min</td>
                                    <td class="px-4 py-3 font-bold {{ $entry->minutes_owed > 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ $entry->minutes_owed > 0 ? $entry->minutes_owed . ' min' : 'OK' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <a href="{{ route('time-entries.edit', $entry) }}"
                                           class="text-blue-600 hover:text-blue-900 mr-2">Editar</a>
                                        <form action="{{ route('time-entries.destroy', $entry) }}"
                                              method="POST" class="inline"
                                              onsubmit="return confirm('¿Eliminar este registro?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                        @if(!$hasEntries)
                            <tr>
                                <td colspan="8" class="px-4 py-4 text-center text-gray-500">
                                    No hay registros para este día.
                                </td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
