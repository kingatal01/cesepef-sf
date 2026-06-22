import { Controller } from '@hotwired/stimulus';

/*
 * Filtre les réalisations par secteur, côté client.
 *   data-controller="realisations-filter"
 *   boutons : data-action="realisations-filter#filter" data-realisations-filter-sector-param="Santé"
 *   cartes  : data-realisations-filter-target="item" data-sector="Santé"
 *   boutons : data-realisations-filter-target="button" data-sector="Santé"
 */
export default class extends Controller {
    static targets = ['item', 'button'];

    filter(event) {
        const sector = event.params.sector;

        this.itemTargets.forEach((el) => {
            const match = sector === 'all' || el.dataset.sector === sector;
            el.classList.toggle('hidden', !match);
        });

        this.buttonTargets.forEach((btn) => {
            const active = btn.dataset.sector === sector;
            btn.classList.toggle('bg-primary', active);
            btn.classList.toggle('text-white', active);
            btn.classList.toggle('bg-gray-100', !active);
            btn.classList.toggle('text-body-color', !active);
        });
    }
}
