<template>
  <div class="container mt-4">
    <div v-if="!movie">
      <p class="text-center text-danger">Película no encontrada</p>
    </div>
    <div v-else class="card mx-auto" style="max-width: 600px;">
      <img :src="'/img/movies/' + movie.picture" class="card-img-top" alt="Poster">
      <div class="card-body">
        <h3 class="card-title">{{ movie.title }}</h3>
        <p><strong>Director:</strong> {{ movie.director }}</p>
        <p><strong>Género:</strong> {{ movie.genre }}</p>
        <p><strong>Duración:</strong> {{ movie.duration }} min</p>
        <p><strong>Año de estreno:</strong> {{ movie.release_date }}</p>
        <p><strong>Rating:</strong> {{ movie.rating }}</p>
        <router-link :to="`/listMovies?page=${page}`" class="btn btn-primary mt-3">
          ← Volver a la lista
        </router-link>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue'
import { useStore } from 'vuex'
import { useRoute } from 'vue-router'

export default {
  name: 'movieDetail',
  setup() {
    const store = useStore()
    const route = useRoute()

    const movieId = parseInt(route.params.id)
    const page = route.query.page || 1

    const movie = computed(() => store.state.movieList.find(m => m.id === movieId))

    return { movie, page }
  }
}
</script>
