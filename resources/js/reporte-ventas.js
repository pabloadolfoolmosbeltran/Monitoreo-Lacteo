let rankingChart;

export function initializeSalesRanking() {
  const canvas = document.querySelector('#top-products-chart');
  const source = document.querySelector('#top-products-data');
  if (!canvas || !source || typeof window.Chart === 'undefined') return;
  const rows = JSON.parse(source.textContent || '[]');
  rankingChart?.destroy();
  rankingChart = new window.Chart(canvas, {
    type: 'bar',
    data: {
      labels: rows.map(row => row.producto),
      datasets: [{
        label: 'Unidades vendidas',
        data: rows.map(row => row.unidades),
        backgroundColor: rows.map((_, index) => ['#d97706', '#64748b', '#b45309'][index] ?? '#0f766e'),
      }],
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        tooltip: { callbacks: { afterLabel: context => {
          const row = rows[context.dataIndex];
          return `Monto: Bs. ${row.monto} · ${row.porcentaje}% del total`;
        } } },
      },
      scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
    },
  });
}

document.addEventListener('DOMContentLoaded', initializeSalesRanking);
document.addEventListener('app:content-loaded', initializeSalesRanking);
