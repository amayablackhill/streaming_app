<template>
    <div class="container mt-5" v-if="movie">
      <h2 class="mb-4">✏️ Editar Película</h2>
      <form @submit.prevent="updateMovie">
        <div class="mb-3">
          <label class="form-label">Título</label>
          <input v-model="movie.title" type="text" class="form-control" required />
        </div>
        <div class="mb-3">
          <label class="form-label">Director</label>
          <input v-model="movie.director" type="text" class="form-control" required />
        </div>
        <div class="mb-3">
          <label class="form-label">Género</label>
          <input v-model="movie.genre" type="text" class="form-control" />
        </div>
        <div class="mb-3">
          <label class="form-label">Duración (min)</label>
          <input v-model="movie.duration" type="number" class="form-control" />
        </div>
        <div class="mb-3">
          <label class="form-label">Fecha de estreno</label>
          <input v-model="movie.release_date" type="date" class="form-control" />
        </div>
        <div class="mb-3">
          <label class="form-label">Rating</label>
          <input v-model="movie.rating" type="number" class="form-control" />
        </div>
  
        <button type="submit" class="btn btn-success">Guardar cambios</button>
        <router-link to="/listMovies" class="btn btn-secondary ms-2">Cancelar</router-link>
      </form>
    </div>
  </template>
  
  <script>
  import { ref, onMounted } from 'vue'
  import { useRoute, useRouter } from 'vue-router'
  import axios from 'axios'
  
  export default {
    name: 'editMovie',
    setup() {
      const route = useRoute()
      const router = useRouter()
      const movie = ref(null)
  
      const fetchMovie = async () => {
        try {
          const res = await axios.get(`/api/movies/${route.params.id}`)
          movie.value = res.data.movie
        } catch (err) {
          console.error('Error al cargar la película', err)
        }
      }
  
      const updateMovie = async () => {
        try {
          await axios.put(`/api/movies/${route.params.id}`, movie.value)
          router.push('/listMovies')
        } catch (err) {
          console.error('Error al actualizar', err)
        }
      }
  
      onMounted(() => {
        fetchMovie()
      })
  
      return { movie, updateMovie }
    }
  }
  </script>
  