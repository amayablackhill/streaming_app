<template>
    <!--
    <div>
        1: El nom del component és = {{this.$options.name}} <br>
    </div>
    -->
    <nav>
    <ul>
        <li>
    <router-link to="/">Home</router-link> | 
    <router-link to="/about">About</router-link> |
    <router-link to="/listMovies">Pelicules</router-link>
  
         </li>
    </ul>
</nav>    
    <!--
    2: El nom del component és = {{this.$options.name}} <br>
    -->
</template>


<script>
    export default {
        name: "navMovies",
        setup() {
            
        },
    }
</script>


<style scoped>
    nav {
        padding: 5px;
        float: center;
        max-width: 100%;
        margin: 0;
        text-align: center;
				
    }

    nav ul {
        list-style-type: none;
        padding: 0;
    }

    nav ul li {
        display: inline;
        padding: 1em;
    }

    nav a {
        font-weight: bold;
        color: #2c3e50;
    }

    nav a.router-link-exact-active {
        color: #42b983;
    }
</style>