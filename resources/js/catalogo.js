// ============================================================
// CATALOGO.JS - Buscador y filtros del catálogo público
// ============================================================

document.addEventListener('DOMContentLoaded', function () {

    const buscador = document.getElementById('buscadorCatalogo');
    const filtroProducto = document.getElementById('filtroProducto');
    const filtroSabor = document.getElementById('filtroSabor');
    const filtroUnidad = document.getElementById('filtroUnidad');

    const tarjetas = Array.from(
        document.querySelectorAll('.tarjeta-producto')
    );

    const sinResultados =
        document.getElementById('sinResultados');

    const filtrosStock =
        Array.from(document.querySelectorAll('.filtro-stock'));

    const filtrosFruta =
        Array.from(document.querySelectorAll('.filtro-fruta'));

    const limpiarFiltros =
        document.getElementById('limpiarFiltros');

    const limpiarFiltros2 =
        document.getElementById('limpiarFiltros2');

    let filtroStockActual = '';
    let filtroFrutaActual = '';

    if (!buscador) {
        return; // La vista no renderizó el buscador; no hacemos nada.
    }

    function normalizar(texto) {
        return (texto || '')
            .toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    function aplicarFiltros() {
        const texto = normalizar(buscador.value);
        const producto = normalizar(filtroProducto.value);
        const sabor = normalizar(filtroSabor.value);
        const unidad = normalizar(filtroUnidad.value);

        let visibles = 0;

        tarjetas.forEach(function (tarjeta) {
            const busqueda = normalizar(tarjeta.dataset.busqueda);
            const productoTarjeta = normalizar(tarjeta.dataset.producto);
            const saborTarjeta = normalizar(tarjeta.dataset.sabor);
            const unidadTarjeta = normalizar(tarjeta.dataset.unidad);
            const stockTarjeta = tarjeta.dataset.stock;
            const frutaTarjeta = tarjeta.dataset.fruta;

            const coincideTexto = !texto || busqueda.includes(texto);
            const coincideProducto = !producto || productoTarjeta === producto;
            const coincideSabor = !sabor || saborTarjeta === sabor;
            const coincideUnidad = !unidad || unidadTarjeta === unidad;
            const coincideStock = !filtroStockActual || stockTarjeta === filtroStockActual;
            const coincideFruta = !filtroFrutaActual || frutaTarjeta === filtroFrutaActual;

            const visible =
                coincideTexto &&
                coincideProducto &&
                coincideSabor &&
                coincideUnidad &&
                coincideStock &&
                coincideFruta;

            tarjeta.classList.toggle('d-none', !visible);

            if (visible) {
                visibles++;
            }
        });

        sinResultados.classList.toggle('d-none', visibles > 0);
    }

    buscador.addEventListener('input', aplicarFiltros);
    filtroProducto.addEventListener('change', aplicarFiltros);
    filtroSabor.addEventListener('change', aplicarFiltros);
    filtroUnidad.addEventListener('change', aplicarFiltros);

    // --- Chips de disponibilidad ---
    filtrosStock.forEach(function (boton) {
        boton.addEventListener('click', function () {
            filtroStockActual = this.dataset.stock;

            filtrosStock.forEach(function (b) {
                b.classList.remove('active');
            });
            this.classList.add('active');

            aplicarFiltros();
        });
    });

    // --- Chips de "con fruta" ---
    filtrosFruta.forEach(function (boton) {
        boton.addEventListener('click', function () {
            filtroFrutaActual = this.dataset.fruta;

            filtrosFruta.forEach(function (b) {
                b.classList.remove('active-fruta');
            });
            this.classList.add('active-fruta');

            aplicarFiltros();
        });
    });

    function limpiarTodo() {
        buscador.value = '';
        filtroProducto.value = '';
        filtroSabor.value = '';
        filtroUnidad.value = '';
        filtroStockActual = '';
        filtroFrutaActual = '';

        filtrosStock.forEach(function (b) {
            b.classList.remove('active');
        });
        const stockTodos = document.querySelector('.filtro-stock[data-stock=""]');
        if (stockTodos) {
            stockTodos.classList.add('active');
        }

        filtrosFruta.forEach(function (b) {
            b.classList.remove('active-fruta');
        });
        const frutaTodos = document.querySelector('.filtro-fruta[data-fruta=""]');
        if (frutaTodos) {
            frutaTodos.classList.add('active-fruta');
        }

        aplicarFiltros();
    }

    limpiarFiltros.addEventListener('click', limpiarTodo);
    limpiarFiltros2.addEventListener('click', limpiarTodo);
});