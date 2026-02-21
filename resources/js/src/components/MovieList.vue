<!-- resources/js/src/components/MovieList.vue -->
<template>
    <div>
      <h1>Películas</h1>
      <ul>
        <li v-for="movie in movies" :key="movie.id">
          {{ movie.title }}
        </li>
      </ul>
    </div>
  </template>
  
  <script>
  import { mapGetters } from 'vuex';
  
  export default {
    computed: {
      ...mapGetters(['allMovies']),
      movies() {
        return this.allMovies;
      }
    },
    created() {
      this.$store.dispatch('fetchMovies');
    }
  }
  </script>
  