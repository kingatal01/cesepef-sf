import { Controller } from '@hotwired/stimulus';

/*
 * Bouton « retour en haut » qui apparaît après 300px de scroll.
 * data-controller="scroll-top"
 *   target: button   (le bouton lui-même, masqué par défaut)
 *   action: data-action="scroll-top#top" sur le bouton
 */
export default class extends Controller {
    static targets = ['button'];

    connect() {
        this.onScroll = this.toggleVisibility.bind(this);
        window.addEventListener('scroll', this.onScroll, { passive: true });
        this.toggleVisibility();
    }

    disconnect() {
        window.removeEventListener('scroll', this.onScroll);
    }

    toggleVisibility() {
        if (!this.hasButtonTarget) return;
        this.buttonTarget.classList.toggle('hidden', window.scrollY <= 300);
    }

    top() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}
