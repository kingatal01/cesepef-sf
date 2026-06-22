import { Controller } from '@hotwired/stimulus';

/*
 * Bascule clair/sombre — équivalent de next-themes.
 * Ajoute/retire la classe `dark` sur <html> et persiste le choix dans localStorage.
 * data-controller="theme-toggle"  (sur le bouton)
 */
export default class extends Controller {
    toggle() {
        const isDark = document.documentElement.classList.toggle('dark');
        try {
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        } catch (e) { /* localStorage indisponible */ }
    }
}
