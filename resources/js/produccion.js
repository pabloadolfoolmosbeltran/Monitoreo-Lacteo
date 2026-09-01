// ============================================================
// PRODUCCION.JS - Cálculo dinámico de cuajo/stock
// Espera window.produccionData (Array de productos) definido
// por la vista antes de cargar este script.
// ============================================================

document.addEventListener('DOMContentLoaded', function () {
    const selectProducto = document.getElementById('select-producto');
    const inputLitros = document.getElementById('input-cantidad-leche');
    const inputTempObj = document.getElementById('input-temperatura-objetivo');
    const tempHelp = document.getElementById('temp-sugerida-help');

    const contenedorInfo = document.getElementById('info-lote-dinamico');
    const alertaProducto = document.getElementById('alerta-producto');
    const alertaStock = document.getElementById('alerta-stock-insuficiente');
    const btnSubmit = document.getElementById('btn-iniciar-produccion');

    const productosData = window.produccionData || [];

    function calcularInsumos() {
        if (!selectProducto) return;

        const productoId = selectProducto.value;
        const litros = parseFloat(inputLitros.value) || 0;

        if (productoId) {
            const producto = productosData.find(p => p.id == productoId);

            if (producto) {
                contenedorInfo.style.display = 'block';

                // 1. Imagen referencial
                const imgRuta = producto.imagen_referencial
                    ? '/storage/' + producto.imagen_referencial
                    : 'https://cdn-icons-png.flaticon.com/512/3050/3050158.png';
                document.getElementById('img-producto-referencial').src = imgRuta;

                // 2. Título y Litros
                document.getElementById('nombre-lote').innerText = 'Lote de: ' + producto.nombre;
                document.getElementById('litros-ingresados').innerText = litros;

                // 3. Autocompletar / Sugerir Temperatura Objetivo
                if (producto.temperatura_pasteurizacion && (!inputTempObj.value || inputTempObj.dataset.autofilled === 'true')) {
                    inputTempObj.value = producto.temperatura_pasteurizacion;
                    inputTempObj.dataset.autofilled = 'true';
                }

                if (producto.temperatura_minima && producto.temperatura_maxima) {
                    tempHelp.style.display = 'block';
                    tempHelp.innerHTML = `<i class="bi bi-info-circle me-1"></i> Rango recomendado: <strong>${producto.temperatura_minima}°C - ${producto.temperatura_maxima}°C</strong> (Pasteurización: <strong>${producto.temperatura_pasteurizacion ?? 'N/A'}°C</strong>)`;
                    document.getElementById('rango-temp-val').innerText = `${producto.temperatura_minima} - ${producto.temperatura_maxima} °C`;
                } else {
                    tempHelp.style.display = 'none';
                    document.getElementById('rango-temp-val').innerText = 'N/A';
                }

                // 4. Alerta de Instrucciones
                if (producto.instrucciones && producto.instrucciones.trim() !== '') {
                    alertaProducto.style.display = 'block';
                    document.getElementById('texto-alerta').innerText = producto.instrucciones;
                } else {
                    alertaProducto.style.display = 'none';
                }

                // 5. Variables de cálculo de cuajo
                const factorCuajo = parseFloat(producto.cuajo_por_litro) || 0;
                const stockActual = parseFloat(producto.stock_cuajo) || 0;
                const unidad = producto.unidad_cuajo || 'ml';
                const tipoCuajo = producto.tipo_cuajo || 'No especificado';

                const totalAGastar = litros * factorCuajo;
                const stockRestante = stockActual - totalAGastar;

                // 6. Inyección de valores en la vista
                document.getElementById('tipo-cuajo-val').innerText = tipoCuajo;
                document.getElementById('dosis-cuajo-val').innerText = `${factorCuajo} ${unidad}/L`;
                document.getElementById('resultado-cuajo').innerText = totalAGastar.toFixed(2);
                document.getElementById('stock-actual-val').innerText = stockActual.toFixed(2);
                // Actualizar inputs ocultos que van hacia la base de datos
                document.getElementById('input-hidden-tipo-cuajo').value = tipoCuajo;
                document.getElementById('input-hidden-cantidad-cuajo').value = totalAGastar.toFixed(2);
                const elStockRestante = document.getElementById('stock-restante-val');
                elStockRestante.innerText = stockRestante.toFixed(2);

                document.querySelectorAll('.unidad-cuajo-val').forEach(el => el.innerText = unidad);

                // 7. Validación de Stock e Indicador de Alerta
                if (totalAGastar > stockActual && litros > 0) {
                    alertaStock.style.display = 'block';
                    elStockRestante.className = 'fw-bold fs-5 text-danger';
                    if (btnSubmit) btnSubmit.classList.add('disabled');
                } else {
                    alertaStock.style.display = 'none';
                    elStockRestante.className = 'fw-bold fs-5 text-success';
                    if (btnSubmit) btnSubmit.classList.remove('disabled');
                }
            }
        } else {
            contenedorInfo.style.display = 'none';
            tempHelp.style.display = 'none';
        }
    }

    if (inputTempObj) {
        inputTempObj.addEventListener('input', function () {
            delete inputTempObj.dataset.autofilled;
        });
    }

    if (selectProducto && inputLitros) {
        selectProducto.addEventListener('change', calcularInsumos);
        inputLitros.addEventListener('input', calcularInsumos);
        if (selectProducto.value) {
            calcularInsumos();
        }
    }
});
