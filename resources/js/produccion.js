import { requiredRennet, rennetReferences } from './rennet-recommendation';

// Cálculo dinámico de la recomendación de cuajo.

function initializeProductionForm() {
    const selectProducto = document.getElementById('select-producto');
    const inputLitros = document.getElementById('input-cantidad-leche');
    const inputTempObj = document.getElementById('input-temperatura-objetivo');
    const tempHelp = document.getElementById('temp-sugerida-help');

    const contenedorInfo = document.getElementById('info-lote-dinamico');
    const alertaProducto = document.getElementById('alerta-producto');
    const productosData = obtenerProductosDesdeVista();

    // APUNTE:
    // El cálculo en pantalla ayuda al usuario antes de enviar el formulario.
    // La validación definitiva se repite en ProduccionController::iniciar().
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

                const factorCuajo = parseFloat(producto.cuajo_por_litro) || 0;
                const unidad = producto.unidad_cuajo || 'ml';
                const tipoCuajo = producto.tipo_cuajo || 'No especificado';
                const totalRecomendado = requiredRennet(litros, factorCuajo);

                document.getElementById('tipo-cuajo-val').innerText = tipoCuajo;
                document.getElementById('dosis-cuajo-val').innerText = `${factorCuajo} ${unidad}/L`;
                document.getElementById('resultado-cuajo').innerText = totalRecomendado.toFixed(2);
                document.getElementById('referencias-cuajo').replaceChildren(
                    ...rennetReferences(factorCuajo).map(reference => {
                        const item = document.createElement('span');
                        item.className = 'badge text-bg-light border';
                        item.innerText = `${reference.liters} L: ${reference.amount.toFixed(2)} ${unidad}`;
                        return item;
                    }),
                );

                document.querySelectorAll('.unidad-cuajo-val').forEach(el => el.innerText = unidad);
            }
        } else {
            contenedorInfo.style.display = 'none';
            tempHelp.style.display = 'none';
        }
    }

    function obtenerProductosDesdeVista() {
        const dataNode = document.getElementById('produccion-data');

        if (!dataNode?.dataset.productos) {
            return [];
        }

        try {
            return JSON.parse(dataNode.dataset.productos);
        } catch (error) {
            console.error('No se pudieron leer los productos de la vista:', error);
            return [];
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
}

document.addEventListener('DOMContentLoaded', initializeProductionForm);
document.addEventListener('app:content-loaded', initializeProductionForm);
