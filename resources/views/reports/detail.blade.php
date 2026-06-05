<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Detalle: {{ $employee->full_name }}
            </h2>
            <a href="{{ route('reports.index', ['month' => $month]) }}"
               class="text-blue-600 hover:text-blue-900">
                ← Volver al reporte
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Info del empleado --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Empleado</p>
                        <p class="font-bold">{{ $employee->full_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Género</p>
                        <p class="font-bold">{{ $employee->gender === 'male' ? 'Hombre' : 'Mujer' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Mes</p>
                        <p class="font-bold">{{ \Carbon\Carbon::parse($month)->translatedFormat('F Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total debe</p>
                        <p class="text-2xl font-bold {{ $totalOwed > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $totalOwed > 0 ? $totalOwed . ' min' : 'Al día' }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Tabla de registros --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Salida</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Regreso</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Tomados</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Permitidos</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Debe</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ajustes</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($entries as $entry)
                            <tr class="{{ $entry->minutes_owed > 0 ? 'bg-red-50' : '' }}">
                                <td class="px-4 py-3">{{ $entry->date->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">{{ $entry->type === 'breakfast' ? 'Desayuno' : 'Almuerzo' }}</td>
                                <td class="px-4 py-3">{{ \Carbon\Carbon::parse($entry->time_out)->format('h:i A') }}</td>
                                <td class="px-4 py-3">{{ \Carbon\Carbon::parse($entry->time_in)->format('h:i A') }}</td>
                                <td class="px-4 py-3 text-center">{{ $entry->minutes_taken }} min</td>
                                <td class="px-4 py-3 text-center">{{ $employee->getAllowedMinutes($entry->type) }} min</td>
                                <td class="px-4 py-3 text-center font-bold {{ $entry->minutes_owed > 0 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $entry->minutes_owed > 0 ? $entry->minutes_owed . ' min' : 'OK' }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if($entry->adjustments->count() > 0)
                                        @foreach($entry->adjustments as $adj)
                                            <div class="mb-1 text-xs text-gray-500">
                                                {{ $adj->user->name }} cambió de {{ $adj->old_minutes_owed }} a {{ $adj->new_minutes_owed }} min
                                                — "{{ $adj->reason }}"
                                                <br>{{ $adj->created_at->format('d/m/Y H:i') }}
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="text-gray-400">Sin ajustes</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-4 text-center text-gray-500">
                                    No hay registros para este mes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
