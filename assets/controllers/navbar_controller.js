import { Controller } from '@hotwired/stimulus';

/*
 * En-tête : menu mobile (ouvrir/fermer) + navbar collante au scroll.
 * data-controller="navbar"
 *   targets: header, menu, logo, bar1, bar2, bar3
 *   action:  data-action="navbar#toggle" sur le bouton hamburger
 */
export default class extends Controller {
    static targets = ['header', 'menu', 'logo', 'bar1', 'bar2', 'bar3'];

    connect() {
        this.open = false;
        this.onScroll = this.handleScroll.bind(this);
        window.addEventListener('scroll', this.onScroll, { passive: true });
        this.handleScroll();
    }

    disconnect() {
        window.removeEventListener('scroll', this.onScroll);
    }

    toggle() {
        this.open = !this.open;
        if (this.hasMenuTarget) {
            this.menuTarget.classList.toggle('invisible', !this.open);
            this.menuTarget.classList.toggle('opacity-0', !this.open);
            this.menuTarget.classList.toggle('top-[120%]', !this.open);
            this.menuTarget.classList.toggle('opacity-100', this.open);
            this.menuTarget.classList.toggle('top-full', this.open);
        }
        // Animation du hamburger en croix
        if (this.hasBar1Target) this.bar1Target.classList.toggle('top-[7px]', this.open);
        if (this.hasBar1Target) this.bar1Target.classList.toggle('rotate-45', this.open);
        if (this.hasBar2Target) this.bar2Target.classList.toggle('opacity-0', this.open);
        if (this.hasBar3Target) this.bar3Target.classList.toggle('top-[-8px]', this.open);
        if (this.hasBar3Target) this.bar3Target.classList.toggle('-rotate-45', this.open);
    }

    handleScroll() {
        if (!this.hasHeaderTarget) return;
        const sticky = window.scrollY >= 80;
        const h = this.headerTarget;
        h.classList.toggle('sticky', sticky);
        h.classList.toggle('fixed', sticky);
        h.classList.toggle('z-9999', sticky);
        h.classList.toggle('bg-white/80', sticky);
        h.classList.toggle('backdrop-blur-xs', sticky);
        h.classList.toggle('shadow-sticky', sticky);
        h.classList.toggle('transition', sticky);
        h.classList.toggle('dark:bg-gray-dark', sticky);
        h.classList.toggle('dark:shadow-sticky-dark', sticky);
        h.classList.toggle('absolute', !sticky);
        h.classList.toggle('bg-transparent', !sticky);
        if (this.hasLogoTarget) {
            this.logoTarget.classList.toggle('py-8', !sticky);
            this.logoTarget.classList.toggle('py-5', sticky);
            this.logoTarget.classList.toggle('lg:py-2', sticky);
        }
    }
}
