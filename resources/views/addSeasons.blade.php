<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @csrf
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <x-app-layout>
        <div class="max-w-7xl mx-auto px-4 py-12">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-3xl font-bold">Series</h2>
                <a href="{{ route('content.add') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Añadir Nueva Serie
                </a>
            </div>
            
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Nombre</th>
                        <th scope="col" class="px-6 py-3">Año</th>
                        <th scope="col" class="px-6 py-3">Género</th>
                        <th scope="col" class="px-6 py-3">Rating</th>
                        <th scope="col" class="px-6 py-3">Temporadas</th>
                        <th scope="col" class="px-6 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($series as $serie)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4">
                                <a href="{{ route('content.edit', $serie->id) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">
                                    {{ $serie->title }}
                                </a>
                            </td>
                            <td class="px-6 py-4">{{ $serie->release_date }}</td>
                            <td class="px-6 py-4">{{ $serie->genre->name }}</td>
                            <td class="px-6 py-4">{{ $serie->rating }}</td>
                            <td class="px-6 py-4">
                                {{ $serie->seasons->count() }} temporadas
                            </td>
                            <td class="px-6 py-4 flex space-x-2">
                                <a href="{{ route('content.edit', $serie->id) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Editar</a>
                                <a href="{{ route('seasons.manage', $serie->id) }}" class="font-medium text-green-600 dark:text-green-500 hover:underline">Temporadas</a>
                                <form action="{{ route('content.destroy', $serie->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-red-600 dark:text-red-500 hover:underline">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-app-layout>
</body>
</html>