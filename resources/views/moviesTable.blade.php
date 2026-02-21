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
            <h2 class="text-3xl font-bold mb-6">Movies</h2>
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                    <tr>
                        <th scope="col" class="px-6 py-3">Name</th>
                        <th scope="col" class="px-6 py-3">Year</th>
                        <th scope="col" class="px-6 py-3">Genre</th>
                        <th scope="col" class="px-6 py-3">Rating</th>
                        <th scope="col" class="px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($movies as $movie)
                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <td class="px-6 py-4">{{ $movie->title }}</td>
                            <td class="px-6 py-4">{{ $movie->release_date }}</td>
                            <td class="px-6 py-4">{{ $movie->genre->name }}</td>
                            <td class="px-6 py-4">{{ $movie->rating }}</td>
                            <td class="px-6 py-4">
                                <a href="{{ route('content.edit', $movie->id) }}" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</a>
                                <form action="{{ route('content.destroy', $movie->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-red-600 dark:text-red-500 hover:underline">Delete</button>
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
