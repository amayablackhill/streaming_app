import './bootstrap';
import Alpine from 'alpinejs';

// Importa Swiper correctamente (versión 8+)
import Swiper from 'swiper';

// Importa los módulos necesarios
import { Navigation, Pagination } from 'swiper/modules';

// Importa los estilos básicos de Swiper
import 'swiper/css';

// Importa estilos de los módulos
import 'swiper/css/navigation';
import 'swiper/css/pagination';

import { createApp } from 'vue';
import App from './src/App.vue';
import store from './src/store';

const app = createApp(App);
app.use(store);
app.mount('#app')

// Configura Alpine
window.Alpine = Alpine;
Alpine.start();
// Inicialización de Swiper
document.addEventListener('DOMContentLoaded', function() {
    const swiperConfig = {
        slidesPerView: 'auto',
        spaceBetween: 15,
        modules: [Navigation, Pagination],
        centeredSlides: false,
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        breakpoints: {
            320: { slidesPerView: 2 },
            480: { slidesPerView: 3 },
            640: { slidesPerView: 4 },
            768: { slidesPerView: 5 },
            1024: { slidesPerView: 6 },
            1280: { slidesPerView: 7 }
        }
    };

    // Inicializar carruseles
    const popularSwiper = new Swiper('.popular-swiper', swiperConfig);
    const trendingSwiper = new Swiper('.trending-swiper', swiperConfig);
    

});
