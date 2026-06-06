<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight flex items-center gap-2">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Control de Tiempos
            </h2>
            <span class="text-sm text-gray-500">
                {{ now()->translatedFormat('l, d \d\e F Y') }}
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Alertas --}}
            @if(session('success'))
                <div class="mb-4 flex items-center gap-2 bg-emerald-50 border-l-4 border-emerald-500 text-emerald-700 p-4 rounded-r-lg" id="alert-success">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 flex items-center gap-2 bg-red-50 border-l-4 border-red-500 text-red-700 p-4 rounded-r-lg">
                    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            {{-- Selector de fecha --}}
            <div class="bg-white shadow-sm rounded-xl p-5 mb-6 border border-gray-100">
                <form method="GET" action="{{ route('time-entries.index') }}" class="flex items-center gap-4">
                    <label class="font-semibold text-gray-700 flex items-center gap-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        Fecha:
                    </label>
                    <input type="date" name="date" value="{{ $date }}"
                           class="border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-5 rounded-lg transition duration-200 shadow-sm">
                        Ver día
                    </button>
                    @if($date !== now()->toDateString())
                        <a href="{{ route('time-entries.index') }}"
                           class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                            ← Volver a hoy
                        </a>
                    @endif
                </form>
            </div>

            {{-- Tabla principal de empleados --}}
            <div class="bg-white shadow-sm rounded-xl border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 bg-gradient-to-r from-indigo-600 to-indigo-700">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Registro del {{ \Carbon\Carbon::parse($date)->translatedFormat('d \d\e F, Y') }}
                    </h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Empleado
                                </th>
                                <th colspan="3" class="px-4 py-3 text-center text-xs font-semibold text-amber-600 uppercase tracking-wider bg-amber-50/50">
                                    ☕ Desayuno
                                </th>
                                <th colspan="3" class="px-4 py-3 text-center text-xs font-semibold text-orange-600 uppercase tracking-wider bg-orange-50/50">
                                    🍽️ Almuerzo
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Deuda Hoy
                                </th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($employees as $employee)
                                @php
                                    $b = $employee->breakfast;
                                    $l = $employee->lunch;
                                    $dailyOwed = ($b->minutes_owed ?? 0) + ($l->minutes_owed ?? 0);
                                @endphp
                                <tr class="hover:bg-gray-50/50 transition duration-150">
                                    {{-- Empleado --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold text-sm
                                                {{ $employee->gender === 'male' ? 'bg-blue-500' : 'bg-pink-500' }}">
                                                {{ strtoupper(substr($employee->first_name, 0, 1)) }}{{ strtoupper(substr($employee->last_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-semibold text-gray-800">{{ $employee->full_name }}</p>
                                                <p class="text-xs text-gray-400">
                                                    {{ $employee->gender === 'male' ? '♂ Hombre' : '♀ Mujer' }}
                                                    · Desayuno {{ $employee->getAllowedMinutes('breakfast') }}min
                                                    · Almuerzo {{ $employee->getAllowedMinutes('lunch') }}min
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- DESAYUNO --}}
                                    {{-- Salida desayuno --}}
                                    <td class="px-2 py-4 text-center bg-amber-50/30">
                                        @if($b && $b->time_out)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                ↗ {{ \Carbon\Carbon::parse($b->time_out)->format('h:i A') }}
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('time-entries.clock-out') }}">
                                                @csrf
                                                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                                <input type="hidden" name="date" value="{{ $date }}">
                                                <input type="hidden" name="type" value="breakfast">
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium rounded-lg transition duration-200 shadow-sm"
                                                        onclick="return confirm('¿Registrar salida de desayuno para {{ $employee->full_name }}?')">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/>
                                                    </svg>
                                                    Salida
                                                </button>
                                            </form>
                                        @endif
                                    </td>

                                    {{-- Entrada desayuno --}}
                                    <td class="px-2 py-4 text-center bg-amber-50/30">
                                        @if($b && $b->time_in)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                ↙ {{ \Carbon\Carbon::parse($b->time_in)->format('h:i A') }}
                                            </span>
                                        @elseif($b && $b->time_out && !$b->time_in)
                                            <form method="POST" action="{{ route('time-entries.clock-in') }}">
                                                @csrf
                                                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                                <input type="hidden" name="date" value="{{ $date }}">
                                                <input type="hidden" name="type" value="breakfast">
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded-lg transition duration-200 shadow-sm animate-pulse"
                                                        onclick="return confirm('¿Registrar regreso de desayuno para {{ $employee->full_name }}?')">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
                                                    </svg>
                                                    Regreso
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>

                                    {{-- Resultado desayuno --}}
                                    <td class="px-2 py-4 text-center bg-amber-50/30">
                                        @if($b && $b->time_in)
                                            <span class="text-xs font-medium {{ $b->minutes_owed > 0 ? 'text-red-600' : 'text-green-600' }}">
                                                {{ $b->minutes_taken }}min
                                                @if($b->minutes_owed > 0)
                                                    <br><span class="text-red-500 font-bold">debe {{ $b->minutes_owed }}</span>
                                                @else
                                                    <br><span class="text-green-500">✓ OK</span>
                                                @endif
                                            </span>
                                        @elseif($b && $b->time_out)
                                            <span class="inline-flex items-center gap-1 text-xs text-amber-600">
                                                <span class="w-2 h-2 bg-amber-500 rounded-full animate-pulse"></span>
                                                En curso
                                            </span>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>

                                    {{-- ALMUERZO --}}
                                    {{-- Salida almuerzo --}}
                                    <td class="px-2 py-4 text-center bg-orange-50/30">
                                        @if($l && $l->time_out)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                ↗ {{ \Carbon\Carbon::parse($l->time_out)->format('h:i A') }}
                                            </span>
                                        @else
                                            <form method="POST" action="{{ route('time-entries.clock-out') }}">
                                                @csrf
                                                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                                <input type="hidden" name="date" value="{{ $date }}">
                                                <input type="hidden" name="type" value="lunch">
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-orange-500 hover:bg-orange-600 text-white text-xs font-medium rounded-lg transition duration-200 shadow-sm"
                                                        onclick="return confirm('¿Registrar salida de almuerzo para {{ $employee->full_name }}?')">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7"/>
                                                    </svg>
                                                    Salida
                                                </button>
                                            </form>
                                        @endif
                                    </td>

                                    {{-- Entrada almuerzo --}}
                                    <td class="px-2 py-4 text-center bg-orange-50/30">
                                        @if($l && $l->time_in)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                ↙ {{ \Carbon\Carbon::parse($l->time_in)->format('h:i A') }}
                                            </span>
                                        @elseif($l && $l->time_out && !$l->time_in)
                                            <form method="POST" action="{{ route('time-entries.clock-in') }}">
                                                @csrf
                                                <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                                                <input type="hidden" name="date" value="{{ $date }}">
                                                <input type="hidden" name="type" value="lunch">
                                                <button type="submit"
                                                        class="inline-flex items-center gap-1 px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded-lg transition duration-200 shadow-sm animate-pulse"
                                                        onclick="return confirm('¿Registrar regreso de almuerzo para {{ $employee->full_name }}?')">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
                                                    </svg>
                                                    Regreso
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>

                                    {{-- Resultado almuerzo --}}
                                    <td class="px-2 py-4 text-center bg-orange-50/30">
                                        @if($l && $l->time_in)
                                            <span class="text-xs font-medium {{ $l->minutes_owed > 0 ? 'text-red-600' : 'text-green-600' }}">
                                                {{ $l->minutes_taken }}min
                                                @if($l->minutes_owed > 0)
                                                    <br><span class="text-red-500 font-bold">debe {{ $l->minutes_owed }}</span>
                                                @else
                                                    <br><span class="text-green-500">✓ OK</span>
                                                @endif
                                            </span>
                                        @elseif($l && $l->time_out)
                                            <span class="inline-flex items-center gap-1 text-xs text-orange-600">
                                                <span class="w-2 h-2 bg-orange-500 rounded-full animate-pulse"></span>
                                                En curso
                                            </span>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>

                                    {{-- Deuda total del día --}}
                                    <td class="px-4 py-4 text-center">
                                        @if($dailyOwed > 0)
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                                {{ $dailyOwed }} min
                                            </span>
                                        @elseif(($b && $b->time_in) || ($l && $l->time_in))
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                                ✓ OK
                                            </span>
                                        @else
                                            <span class="text-gray-300 text-xs">—</span>
                                        @endif
                                    </td>

                                    {{-- Acciones --}}
                                    <td class="px-4 py-4 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            @if($b)
                                                <a href="{{ route('time-entries.edit', $b) }}"
                                                   class="p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition"
                                                   title="Editar desayuno">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </a>
                                            @endif
                                            @if($l)
                                                <a href="{{ route('time-entries.edit', $l) }}"
                                                   class="p-1.5 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition"
                                                   title="Editar almuerzo">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                    </svg>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center text-gray-400">
                                        <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        No hay empleados registrados. <a href="{{ route('employees.create') }}" class="text-indigo-600 hover:underline">Crear uno</a>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Auto-ocultar alertas --}}
    <script>
        setTimeout(() => {
            const alert = document.getElementById('alert-success');
            if (alert) alert.style.display = 'none';
        }, 4000);
    </script>
</x-app-layout>
