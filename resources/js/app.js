import './bootstrap';
import Alpine from 'alpinejs';
import EmblaCarousel from 'embla-carousel';

window.Alpine = Alpine;
Alpine.start();

const initEditorialRails = () => {
    const rails = document.querySelectorAll('[data-cc-embla]');

    rails.forEach((rail) => {
        if (rail.dataset.emblaInitialized === 'true') {
            return;
        }

        const viewport = rail.querySelector('[data-cc-embla-viewport]');
        if (!viewport) {
            return;
        }

        const embla = EmblaCarousel(viewport, {
            align: 'start',
            containScroll: 'trimSnaps',
            dragFree: true,
            loop: false,
        });

        const prevButton = rail.querySelector('[data-cc-embla-prev]');
        const nextButton = rail.querySelector('[data-cc-embla-next]');

        const updateControls = () => {
            if (prevButton) {
                prevButton.disabled = !embla.canScrollPrev();
            }

            if (nextButton) {
                nextButton.disabled = !embla.canScrollNext();
            }
        };

        prevButton?.addEventListener('click', () => embla.scrollPrev());
        nextButton?.addEventListener('click', () => embla.scrollNext());

        embla.on('select', updateControls);
        embla.on('reInit', updateControls);
        updateControls();

        rail.classList.add('cc-rail-ready');
        rail.dataset.emblaInitialized = 'true';
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initEditorialRails);
} else {
    initEditorialRails();
}
