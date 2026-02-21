<template>
    <header>
        <a :href=linkUrl>
            <img :src=linkImg>
        </a>
    </header>
</template>

<script>

import { ref } from 'vue';
import { useStore } from 'vuex';

export default {
    name: 'headerNetflick_v3', 
    setup() {
        const store = useStore();
        let linkUrl = ref(store.state.hrefHeader_v3);
        let linkImg = ref(store.state.srcHeader_v3);
        return { linkUrl, linkImg }
    }
}
</script>