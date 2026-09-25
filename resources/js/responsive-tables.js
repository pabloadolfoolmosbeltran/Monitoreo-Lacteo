const normalizeHeader = value => String(value || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .trim()
    .toLowerCase();

const isStatusColumn = header => /^(estado|atendida|activo|situacion)/.test(header);
const isActionColumn = header => /(accion|opcion)/.test(header);
const isPrimaryNameColumn = header => /(nombre|operador|producto|presentacion|proveedor|cliente|referencia|lote|tipo de alerta)/.test(header);

export function makeTablesResponsive(root = document) {
    root.querySelectorAll('table').forEach((table) => {
        if (table.closest('.printable-ticket') || table.dataset.responsiveReady === 'true') return;

        const headers = [...table.querySelectorAll('thead tr:first-child th')];
        if (headers.length <= 4) return;

        const labels = headers.map(header => normalizeHeader(header.textContent));
        const primaryName = labels.findIndex((header, index) => index > 0 && isPrimaryNameColumn(header));
        const visible = new Set([0, primaryName]);
        labels.forEach((header, index) => {
            if (isStatusColumn(header) || isActionColumn(header)) visible.add(index);
        });

        headers.forEach((header, index) => {
            if (visible.has(index)) return;
            header.classList.add('hidden', 'md:table-cell');
            table.querySelectorAll(`tbody tr > *:nth-child(${index + 1})`).forEach(cell => {
                cell.classList.add('hidden', 'md:table-cell');
            });
        });

        table.dataset.responsiveReady = 'true';
    });
}

makeTablesResponsive();
document.addEventListener('app:content-loaded', () => makeTablesResponsive(document.querySelector('#app-main') || document));
