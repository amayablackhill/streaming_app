import { createRouter, createWebHashHistory } from 'vue-router'
import listMovies from '../views/listMovies.vue'
import movieDetail from '../views/movieDetail.vue'
import searchResults from '../views/searchResults.vue'
import editMovie from '../views/editMovie.vue'

const routes = [
  {
    path: '/',
    redirect: '/listMovies'
  },
  {
    path: '/listMovies',
    name: 'listMovies',
    component: listMovies
  },
  {
    path: '/movieDetail/:id',
    name: 'movieDetail',
    component: movieDetail,
    props: true
  },
  {
    path: '/search',
    name: 'searchResults',
    component: searchResults
  },
  {
    path: '/editMovie/:id',
    name: 'editMovie',
    component: editMovie,
    props: true
  }
]

const router = createRouter({
  history: createWebHashHistory(),
  routes
})

console.log('✅ Vue Router cargado')

export default router
