<template>
    <header>
        <a href="https://www.google.com/" target="blank">
            <img :src="src">
        </a>
    </header>
</template>

<script>
    import { ref } from 'vue';

    export default {
        name: "headerNetflick_v1",
        setup() {
            let src = ref('img/netflick.png');
            return { src };
        },
    }
</script>
