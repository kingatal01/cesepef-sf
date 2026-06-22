import { Controller } from '@hotwired/stimulus';

/*
 * Affiche/masque la sidebar admin sur mobile.
 *   data-controller="admin-sidebar"
 *   bouton burger : data-action="admin-sidebar#toggle"
 *   sidebar : data-admin-sidebar-target="panel"
 *   overlay : data-admin-sidebar-target="backdrop"
 */
export default class extends Controller {
    static targets = ['panel', 'backdrop'];

    toggle() {
        const hidden = this.panelTarget.classList.contains('-translate-x-full');
        this.panelTarget.classList.toggle('-translate-x-full', !hidden);
        if (this.hasBackdropTarget) this.backdropTarget.classList.toggle('hidden', !hidden);
    }

    close() {
        this.panelTarget.classList.add('-translate-x-full');
        if (this.hasBackdropTarget) this.backdropTarget.classList.add('hidden');
    }
}
