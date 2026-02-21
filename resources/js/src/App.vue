<template>
  <div>
    <BasicHeader />
    <router-view />
  </div>
</template>

<script>
import BasicHeader from './components/headerNetflick_v3.vue'

export default {
  name: 'App',
  components: {
    BasicHeader
  }
}
</script>
