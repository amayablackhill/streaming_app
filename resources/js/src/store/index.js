import { createStore } from 'vuex'
import axios from 'axios'
import { getMovies, saveMovies } from '../utils/indexedDB'

export default createStore({
  state: {
    movieList: [],
    loading: false,
    error: null
  },
  mutations: {
    setMovies(state, movies) {
      state.movieList = movies
    },
    setLoading(state, value) {
      state.loading = value
    },
    setError(state, error) {
      state.error = error
    }
  },
  actions: {
    async fetchMovies({ commit }) {
      commit('setLoading', true)

      // 1. Intenta obtener de IndexedDB
      const cachedMovies = await getMovies()
      if (cachedMovies.length > 0) {
        commit('setMovies', cachedMovies)
        commit('setLoading', false)
        return
      }

      // 2. Si no hay cache, obtener de API y guardar en IndexedDB
      try {
        const res = await axios.get('/api/movies')
        commit('setMovies', res.data.movies)
        await saveMovies(res.data.movies) // Guardar en IndexedDB
      } catch (err) {
        commit('setError', err.message)
      } finally {
        commit('setLoading', false)
      }
    }
  },
  getters: {
    allMovies: (state) => state.movieList
  }
})
