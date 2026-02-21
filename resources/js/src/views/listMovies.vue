<template>
  <div class="container mt-4">
    <h2 class="text-center mb-4">🎬 Lista de Películas</h2>

    <!-- Buscador -->
    <div class="mb-3 d-flex justify-content-center gap-2">
      <input
        v-model="searchQuery"
        class="form-control w-50"
        type="text"
        placeholder="Buscar por título..."
      />
      <button class="btn btn-primary" @click="handleSearch">Buscar</button>
    </div>

    <div v-if="loading" class="alert alert-info text-center">Cargando...</div>
    <div v-else-if="error" class="alert alert-danger text-center">Error: {{ error }}</div>
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
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="movie in paginatedMovies" :key="movie.id">
            <td>
              <router-link :to="`/movieDetail/${movie.id}?page=${currentPage}`">
                <img :src="'/img/movies/' + movie.picture" alt="Poster" width="100" />
              </router-link>
            </td>
            <td>{{ movie.title }}</td>
            <td>{{ movie.director }}</td>
            <td>Terror</td>
            <td>{{ movie.duration }} min</td>
            <td>{{ movie.release_date }}</td>
            <td>{{ movie.rating }}</td>
            <td>
              <router-link :to="`/editMovie/${movie.id}`" class="btn btn-warning btn-sm">
                Editar
              </router-link>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Paginación -->
      <nav aria-label="Paginación de películas">
        <ul class="pagination justify-content-center">
          <li class="page-item" :class="{ disabled: currentPage === 1 }">
            <button class="page-link" @click="currentPage--" :disabled="currentPage === 1">Anterior</button>
          </li>
          <li class="page-item disabled">
            <span class="page-link">Página {{ currentPage }} de {{ totalPages }}</span>
          </li>
          <li class="page-item" :class="{ disabled: currentPage === totalPages }">
            <button class="page-link" @click="currentPage++" :disabled="currentPage === totalPages">Siguiente</button>
          </li>
        </ul>
      </nav>
    </div>
  </div>
</template>

<script>
import { onMounted, ref, computed, watch } from 'vue'
import { useStore } from 'vuex'
import { useRoute, useRouter } from 'vue-router'
import { getMovies } from '../utils/indexedDB'

export default {
  name: 'listMovies',
  setup() {
    const store = useStore()
    const route = useRoute()
    const router = useRouter()

    const currentPage = ref(parseInt(route.query.page) || 1)
    const itemsPerPage = 3
    const searchQuery = ref('')
    const loading = ref(true)
    const error = ref(null)

    const movies = computed(() => store.getters.allMovies)

    const totalPages = computed(() => Math.ceil(movies.value.length / itemsPerPage))

    const paginatedMovies = computed(() => {
      const start = (currentPage.value - 1) * itemsPerPage
      return movies.value.slice(start, start + itemsPerPage)
    })

    const handleSearch = () => {
      if (!searchQuery.value) return
      router.push({ name: 'searchResults', query: { query: searchQuery.value } })
    }

    watch(currentPage, (newPage) => {
      router.replace({ query: { page: newPage } })
    })

    onMounted(async () => {
      try {
        const cached = await getMovies()
        store.commit('setMovies', cached)
      } catch (err) {
        error.value = 'Error al cargar desde IndexedDB'
      } finally {
        loading.value = false
      }
    })

    return {
      searchQuery,
      loading,
      error,
      currentPage,
      paginatedMovies,
      totalPages,
      handleSearch
    }
  }
}
</script>
