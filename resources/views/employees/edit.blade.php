<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Editar Empleado: {{ $employee->full_name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('employees.update', $employee) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nombre</label>
                            <input type="text" name="first_name" value="{{ old('first_name', $employee->first_name) }}"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('first_name') border-red-500 @enderror">
                            @error('first_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Apellido</label>
                            <input type="text" name="last_name" value="{{ old('last_name', $employee->last_name) }}"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('last_name') border-red-500 @enderror">
                            @error('last_name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Género</label>
                            <select name="gender"
                                    class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('gender') border-red-500 @enderror">
                                <option value="male" {{ old('gender', $employee->gender) === 'male' ? 'selected' : '' }}>Hombre</option>
                                <option value="female" {{ old('gender', $employee->gender) === 'female' ? 'selected' : '' }}>Mujer</option>
                            </select>
                            @error('gender')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Cargo</label>
                            <input type="text" name="position" value="{{ old('position', $employee->position) }}"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700">
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Fecha de Ingreso</label>
                            <input type="date" name="hire_date" value="{{ old('hire_date', $employee->hire_date->format('Y-m-d')) }}"
                                   class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 @error('hire_date') border-red-500 @enderror">
                            @error('hire_date')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-between">
                            <button type="submit"
                                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Actualizar
                            </button>
                            <a href="{{ route('employees.index') }}" class="text-gray-600 hover:text-gray-900">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
