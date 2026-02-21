<template>
    <div class="container mt-4">
      <h2 class="text-center mb-4">🔍 Resultados de búsqueda</h2>
      <p class="text-center">Término buscado: <strong>{{ searchTerm }}</strong></p>
  
      <div v-if="loading" class="alert alert-info text-center">Buscando...</div>
      <div v-else-if="error" class="alert alert-danger text-center">Error: {{ error }}</div>
      <div v-else-if="movies.length === 0" class="alert alert-warning text-center">No se encontraron resultados.</div>
      <div v-else>
        <table class="table table-bordered table-striped text-center">
          <thead class="table-dark">
            <tr>
              <th>Poster</th>
              <th>Título</th>
              <th>Director</th>
              <th>Género</th>
              <th>Duración</th>
              <th>Año</th>
              <th>Rating</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="movie in movies" :key="movie.id">
              <td>
                <router-link :to="`/movieDetail/${movie.id}`">
                  <img :src="'/img/movies/' + movie.picture" alt="Poster" width="100" />
                </router-link>
              </td>
              <td>{{ movie.title }}</td>
              <td>{{ movie.director }}</td>
              <td>{{ movie.genre }}</td>
              <td>{{ movie.duration }} min</td>
              <td>{{ movie.release_date }}</td>
              <td>{{ movie.rating }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </template>
  
  <script>
  import { ref, onMounted } from 'vue'
  import { useRoute } from 'vue-router'
  import axios from 'axios'
  
  export default {
    name: 'searchResults',
    setup() {
      const route = useRoute()
      const searchTerm = route.query.query || ''
      const loading = ref(false)
      const error = ref(null)
      const movies = ref([])
  
      const fetchResults = async () => {
        if (!searchTerm) return
        loading.value = true
        try {
          const res = await axios.get(`/api/search/${encodeURIComponent(searchTerm)}`)
          movies.value = res.data.movies
        } catch (err) {
          error.value = 'Error al obtener resultados'
        } finally {
          loading.value = false
        }
      }
  
      onMounted(() => {
        fetchResults()
      })
  
      return { searchTerm, movies, loading, error }
    }
  }
  </script>
  