<x-app-layout>
    <div class="max-w-7xl mx-auto px-4 py-12">
        <h2 class="text-3xl font-bold mb-6">Gestión de Temporadas: {{ $content->title }}</h2>
        
        <!-- Formulario para añadir temporada -->
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h3 class="text-xl font-semibold mb-4">Añadir Nueva Temporada</h3>
            <form action="{{ route('seasons.store', $content->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-gray-700">Número de Temporada</label>
                        <input type="number" name="season_number" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" >
                    </div>
                    <div>
                        <label class="block text-gray-700">Fecha de lanzamiento</label>
                        <input type="date" name="release_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <div class="mb-6">
                        <label for="picture" class="block text-sm font-medium text-gray-700">Content Poster</label>
                        <input type="file" name="picture" id="picture" accept="image/*"
                            class="mt-1 block w-full text-gray-900 border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                        @error('picture')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>


                    <div class="flex items-end">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Añadir Temporada
                        </button>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-gray-700">Descripción</label>
                    <textarea name="overview" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"></textarea>
                </div>
            </form>
        </div>

        <!-- Listado de temporadas -->
        @if($content->seasons->count() > 0)
            @foreach($content->seasons as $season)
                <div class="bg-white p-6 rounded-lg shadow-md mb-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold">
                            Temporada {{ $season->season_number }}
                        </h3>
                        <div class="flex space-x-2">
                            <a href="{{ route('episodes.create', $season->id) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Añadir Episodio
                            </a>
                            <form action="{{ route('seasons.destroy', $season->id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                                    Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    @if($season->poster_path)
                        <img src="{{ $season->poster_path }}" alt="Poster temporada {{ $season->season_number }}" class="h-40 mb-4">
                    @endif
                    
                    <p class="text-gray-600 mb-4">{{ $season->overview ?? 'Sin descripción' }}</p>
                    
                    <div class="bg-gray-100 p-4 rounded-lg">
                        <h4 class="font-medium mb-2">Episodios ({{ $season->episodes->count() }})</h4>
                        @if($season->episodes->count() > 0)
                            <ul class="divide-y divide-gray-200">
                                @foreach($season->episodes as $episode)
                                    <li class="py-2 flex justify-between items-center">
                                        <div>
                                            <span class="font-medium">Episodio {{ $episode->episode_number }}:</span> 
                                            {{ $episode->title }} 
                                            <span class="text-sm text-gray-500">({{ $episode->duration }} min)</span>
                                        </div>
                                        <div>
                                            <a href="{{ route('episodes.edit', [$season->id, $episode->id]) }}" class="text-blue-600 hover:text-blue-900 mr-2">Editar</a>
                                            <form action="#" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                            </form>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-500">No hay episodios añadidos</p>
                        @endif
                    </div>
                </div>
            @endforeach
        @else
            <div class="bg-white p-6 rounded-lg shadow-md">
                <p class="text-gray-500">No hay temporadas añadidas a esta serie</p>
            </div>
        @endif
    </div>
</x-app-layout>