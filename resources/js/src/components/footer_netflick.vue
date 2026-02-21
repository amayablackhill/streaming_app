<template>
    <footer>
        <ul>
            <li><a href="#" target="_blank">{{ web }}</a></li>
            <li><a href="#" target="_blank">{{ address }}</a></li>
            <li><a href="#" target="_blank">{{ PhoneContact }}</a></li>
            <li><a href="#" target="_blank">{{ EmailContact }}</a></li>
        </ul>
    </footer>
</template>

<script>
import { ref } from 'vue';
import { useStore } from 'vuex';

export default {
    name: 'footer_netflick',
    setup() {
        const store = useStore()
        let web = ref(store.state.footerData.web);
        console.log("web: " + web.value)
        let address = ref(store.state.footerData.address);
        let PhoneContact = ref(store.state.footerData.phone);
        let EmailContact = ref(store.state.footerData.email);
        return { web, address, PhoneContact, EmailContact }
    }
} 
</script>