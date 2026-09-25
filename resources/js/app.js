import './bootstrap';
import { installAsyncPanelActions } from './async-panel';
import './reporte-ventas';
import './responsive-tables';

installAsyncPanelActions();

document.addEventListener('click', (event) => {
    const trigger = event.target.closest('[data-confirm-message]');
    if (trigger && !window.confirm(trigger.dataset.confirmMessage)) {
        event.preventDefault();
        event.stopPropagation();
    }
});
