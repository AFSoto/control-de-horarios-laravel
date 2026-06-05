<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Reportes Mensuales
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Filtros --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Mes</label>
                        <input type="month" name="month" value="{{ $month }}"
                               class="border rounded py-2 px-3 text-gray-700">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1">Empleado</label>
                        <select name="employee_id" class="border rounded py-2 px-3 text-gray-700">
                            <option value="">Todos</option>
                            @foreach($allEmployees as $emp)
                                <option value="{{ $emp->id }}" {{ $employeeId == $emp->id ? 'selected' : '' }}>
                                    {{ $emp->full_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                            class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Filtrar
                    </button>
                </form>
            </div>

            {{-- Resumen general --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-bold mb-2">
                    Resumen de {{ \Carbon\Carbon::parse($month)->translatedFormat('F Y') }}
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                    <div class="bg-gray-50 rounded p-4 text-center">
                        <p class="text-sm text-gray-500">Empleados con deuda</p>
                        <p class="text-3xl font-bold text-red-600">
                            {{ $report->where('total_owed', '>', 0)->count() }}
                        </p>
                    </div>
                    <div class="bg-gray-50 rounded p-4 text-center">
                        <p class="text-sm text-gray-500">Empleados al día</p>
                        <p class="text-3xl font-bold text-green-600">
                            {{ $report->where('total_owed', 0)->count() }}
                        </p>
                    </div>
                    <div class="bg-gray-50 rounded p-4 text-center">
                        <p class="text-sm text-gray-500">Total minutos debidos</p>
                        <p class="text-3xl font-bold text-gray-800">
                            {{ $report->sum('total_owed') }}
                        </p>
                    </div>
                </div>
            </div>

            {{-- Tabla de reporte --}}
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Empleado</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Género</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Registros</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Debe Desayuno</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Debe Almuerzo</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total Debe</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Días con deuda</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Detalle</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($report as $row)
                            <tr class="{{ $row->total_owed > 0 ? 'bg-red-50' : '' }}">
                                <td class="px-4 py-3">{{ $row->employee->full_name }}</td>
                                <td class="px-4 py-3">{{ $row->employee->gender === 'male' ? 'Hombre' : 'Mujer' }}</td>
                                <td class="px-4 py-3 text-center">{{ $row->total_entries }}</td>
                                <td class="px-4 py-3 text-center {{ $row->breakfast_owed > 0 ? 'text-red-600 font-bold' : '' }}">
                                    {{ $row->breakfast_owed }} min
                                </td>
                                <td class="px-4 py-3 text-center {{ $row->lunch_owed > 0 ? 'text-red-600 font-bold' : '' }}">
                                    {{ $row->lunch_owed }} min
                                </td>
                                <td class="px-4 py-3 text-center text-lg {{ $row->total_owed > 0 ? 'text-red-600 font-bold' : 'text-green-600' }}">
                                    {{ $row->total_owed > 0 ? $row->total_owed . ' min' : 'OK' }}
                                </td>
                                <td class="px-4 py-3 text-center">{{ $row->days_with_debt }}</td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('reports.detail', ['employee' => $row->employee->id, 'month' => $month]) }}"
                                       class="text-blue-600 hover:text-blue-900">
                                        Ver detalle
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-4 text-center text-gray-500">
                                    No hay datos para este período.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
