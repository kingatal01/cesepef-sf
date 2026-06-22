import { Controller } from '@hotwired/stimulus';

/*
 * Modales admin. À placer sur un conteneur englobant boutons + modales.
 *   data-controller="modal"
 *   bouton d'ouverture : data-action="modal#open" data-modal-id-param="modal-add"
 *   modale : id="modal-add" data-modal-target="overlay" class="… hidden"
 *   fermeture : data-action="modal#close" (bouton/croix) ou data-action="modal#backdrop" (overlay)
 */
export default class extends Controller {
    static targets = ['overlay'];

    open(event) {
        const id = event.params.id;
        const el = this.overlayTargets.find((o) => o.id === id);
        if (el) { el.classList.remove('hidden'); document.body.classList.add('overflow-hidden'); }
    }

    close(event) {
        const overlay = event.target.closest('[data-modal-target="overlay"]');
        if (overlay) overlay.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }

    backdrop(event) {
        if (event.target === event.currentTarget) {
            event.currentTarget.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    }
}
