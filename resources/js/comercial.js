(() => {
  'use strict';

  let posKeyHandler = null;

  function initializeCommercial() {
    // 1. Helpers & Funciones de utilidad
    const $ = s => document.querySelector(s);
    const money = n => 'Bs. ' + Number(n || 0).toLocaleString('es-BO', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    
    const node = (tag, text, cls) => {
      const el = document.createElement(tag);
      if (text !== undefined) el.textContent = text;
      if (cls) el.className = cls;
      return el;
    };

    const feedback = (message, error = false) => {
      const el = $('#feedback');
      el.className = error ? 'error' : 'notice';
      el.textContent = message;
    };

    async function api(url, data, method) {
      const response = await fetch(url, {
        method: method || (data ? 'POST' : 'GET'),
        credentials: 'same-origin',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').content
        },
        ...(data ? { body: JSON.stringify(data) } : {})
      });

      const body = await response.json().catch(() => ({ message: 'Respuesta inesperada del servidor.' }));

      if (!response.ok) {
        throw new Error(Object.values(body.errors || {}).flat().join(' ') || body.message || 'No se pudo completar la operación.');
      }

      return body;
    }

    // 2. Módulo de Ingreso de Productos
    if ($('#ingreso-form')) {
      let index = 1;

      const applyStandardPrice = (select) => {
        const item = select.closest('.ingreso-item');
        const price = item?.querySelector('[data-sale-price]');
        const standard = select.selectedOptions[0]?.dataset.standardPrice;
        if (price && standard !== undefined) price.value = Number(standard).toFixed(2);
      };

      $('#add-ingreso-item').onclick = () => {
        const copy = $('#ingreso-item-template').content.cloneNode(true);
        copy.querySelectorAll('[name]').forEach(el => el.name = el.name.replace('__INDEX__', index));
        index++;
        $('#ingreso-items').append(copy);
      };

      $('#ingreso-items').addEventListener('change', e => {
        if (e.target.matches('[data-presentation-select]')) applyStandardPrice(e.target);
      });

      $('#ingreso-items').addEventListener('click', e => {
        if (e.target.matches('[data-remove-item]') && $('#ingreso-items').children.length > 1) {
          e.target.closest('.ingreso-item').remove();
        }
      });
    }

    // 3. Previsualización de Ingreso
    document.querySelectorAll('[data-view-ingreso]').forEach(button => button.addEventListener('click', async () => {
      button.disabled = true;
      try {
        const data = await api('/ingresos-productores/' + button.dataset.viewIngreso + '/detalle-json');
        const host = $('#ingreso-preview-content');

        host.replaceChildren(
          node('h3', 'Entrada #' + data.id),
          node('p', ((data.productor?.nombres || 'Histórico') + ' ' + (data.productor?.primer_apellido || '')).trim()),
          node('p', 'Estado: ' + data.estado)
        );

        (data.items || []).forEach(item => {
          const box = node('section', undefined, 'panel');
          box.append(node('strong', 'Lote #' + item.id + ' · ' + (item.presentacion?.nombre || '')));
          box.append(
            node('p', 'Entrada: ' + Number(item.cantidad_ingresada).toFixed(0) + ' · Disponible: ' + Number(item.cantidad_disponible).toFixed(0)),
            node('p', 'Acopio: ' + (item.precio_acopio_unitario === null ? 'Pendiente' : money(item.precio_acopio_unitario)) + ' · Venta: ' + money(item.precio_venta_unitario)),
            node('p', 'Caducidad: ' + (item.fecha_caducidad?.slice(0, 10) || 'Sin fecha'))
          );
          (item.ventas || []).forEach(v => box.append(node('p', 'Venta #' + v.id + ' · ' + Number(v.cantidad_vendida).toFixed(0) + ' · ' + money(v.total_venta))));
          host.append(box);
        });

        const link = node('a', 'Abrir detalle completo →', 'button secondary');
        link.href = '/ingresos-productores/' + data.id;
        host.append(link);

        $('#ingreso-preview').showModal();
      } catch (err) {
        feedback(err.message, true);
      } finally {
        button.disabled = false;
      }
    }));

    if ($('[data-close-preview]')) {
      $('[data-close-preview]').onclick = () => $('#ingreso-preview').close();
    }

    // 4. Previsualización de Ventas
    document.querySelectorAll('[data-view-sale]').forEach(button => button.addEventListener('click', async () => {
      button.disabled = true;
      try {
        const data = await api('/reportes-comerciales/ventas/' + button.dataset.viewSale);
        const host = $('#sale-preview-content');

        host.replaceChildren(
          node('h3', 'Comprobante #' + data.id),
          node('p', 'Pago: ' + data.metodo_pago + (data.cliente ? ' · Cliente: ' + data.cliente : '')),
          node('p', 'Total: ' + money(data.total))
        );

        (data.ventas || []).forEach(v => {
          const box = node('section', undefined, 'panel');
          const producer = v.lote?.ingreso_productor?.productor;

          box.append(
            node('strong', (v.lote?.presentacion?.producto?.nombre || 'Producto') + ' · ' + (v.lote?.presentacion?.nombre || '')),
            node('p', 'Proveedor: ' + (producer?.nombre_completo || 'Histórico') + (producer?.nombre_unidad_productiva ? ' · ' + producer.nombre_unidad_productiva : '')),
            node('p', Number(v.cantidad_vendida).toFixed(0) + ' unidad(es) × ' + money(v.precio_unitario_venta) + ' = ' + money(v.total_venta)),
            node('small', 'Lote #' + v.ingreso_productor_item_id, 'muted')
          );
          host.append(box);
        });

        $('#sale-preview').showModal();
      } catch (err) {
        feedback(err.message, true);
      } finally {
        button.disabled = false;
      }
    }));

    if ($('[data-close-sale]')) {
      $('[data-close-sale]').onclick = () => $('#sale-preview').close();
    }

    // Limpieza de eventos anteriores de POS
    if (posKeyHandler) {
      document.removeEventListener('keydown', posKeyHandler);
      posKeyHandler = null;
    }

    if (!$('#pos')) return;

    // 5. Módulo POS (Punto de Venta) - Estado local
    let catalog = [];
    let cart = new Map();
    let busy = false;
    let key = crypto.randomUUID();
    let searchSequence = 0;

    // Cálculos auxiliares del carrito
    function allocations(p, qty) {
      let remaining = qty;
      const lots = (p.proveedores || []).flatMap(supplier =>
        (supplier.lotes || []).map(lot => ({ ...lot, productor: supplier.nombre }))
      ).sort((a, b) => String(a.caducidad || '').localeCompare(String(b.caducidad || '')) || Number(a.id) - Number(b.id));

      return lots.map(l => {
        const take = Math.min(remaining, Number(l.disponible));
        remaining = Math.max(0, remaining - take);
        return { ...l, take };
      }).filter(l => l.take > 0);
    }

    function subtotal(p, qty) {
      return allocations(p, qty).reduce((sum, l) => sum + l.take * Number(l.precio), 0);
    }

    function setQty(p, qty) {
      if (busy) return;
      qty = Math.round(qty);
      if (!Number.isFinite(qty) || qty < 0) return;

      const maximum = Number(p.stock);

      if (qty > Math.floor(maximum)) {
        feedback('La cantidad supera las unidades enteras disponibles.', true);
        return;
      }

      if (qty === 0) {
        cart.delete(p.id);
      } else {
        cart.set(p.id, { p, qty });
      }

      key = crypto.randomUUID();
      render();
    }

    // Renderizado del Catálogo y Carrito
    function render() {
      const host = $('#catalog');
      host.replaceChildren();

      const term = $('#catalog-search').value.toLocaleLowerCase();
      const category = $('#catalog-category').value;

      const visible = catalog.filter(p => 
        (!term || (p.nombre + ' ' + p.producto).toLocaleLowerCase().includes(term)) && 
        (!category || String(p.producto_id) === category)
      );

      visible.forEach(p => {
        const card = node('article', undefined, 'product');
        const art = node('div', undefined, 'product-art');

        if (p.imagen_comercial) {
          try {
            const u = new URL(p.imagen_comercial, location.origin);
            if (['https:', 'http:'].includes(u.protocol)) {
              const img = node('img');
              img.src = u.href;
              img.alt = p.nombre;
              img.loading = 'lazy';
              art.append(img);
            }
          } catch {}
        }

        if (!art.childNodes.length) art.textContent = '◒';

        card.append(
          art,
          node('small', p.producto, 'muted'),
          node('h3', p.nombre),
          node('div', money(p.precio), 'price')
        );

        const totalAvailable = Number(p.stock);
        const available = totalAvailable - (cart.get(p.id)?.qty || 0);
        const soldOut = totalAvailable < 1;

        card.classList.toggle('is-sold-out', soldOut);
        card.append(node('small', soldOut ? 'AGOTADO' : available + ' ' + (p.unidad || 'unid.') + ' enteras disponibles', soldOut ? 'sold-out-badge' : 'muted'));

        const add = node('button', soldOut ? 'AGOTADO' : available < 1 ? 'Máximo en carrito' : '+ Agregar');
        add.type = 'button';
        add.disabled = busy || available < 1;
        add.onclick = () => setQty(p, (cart.get(p.id)?.qty || 0) + 1);

        card.append(add);
        host.append(card);
      });

      $('#catalog-status').textContent = visible.length + ' presentaciones encontradas';

      const items = $('#cart-items');
      items.replaceChildren();

      let total = 0;
      if (!cart.size) {
        items.append(node('div', 'Tu carrito está vacío. Agrega un producto para comenzar.', 'empty'));
      }

      cart.forEach(entry => {
        const { p, qty } = entry;
        total += subtotal(p, qty);

        const el = node('div', undefined, 'cart-row');
        el.append(node('strong', p.nombre));

        const maximum = Number(p.stock);
        const row = node('div', undefined, 'quantity-control');

        const minus = node('button', '−', 'secondary');
        minus.type = 'button';
        minus.onclick = () => setQty(p, qty - 1);
        minus.disabled = busy;

        const amount = node('input');
        amount.type = 'number';
        amount.min = '0';
        amount.step = '1';
        amount.max = maximum;
        amount.value = String(qty);
        amount.setAttribute('aria-label', 'Cantidad de ' + p.nombre);
        amount.disabled = busy;
        amount.onchange = () => setQty(p, Number(amount.value));

        const plus = node('button', '+', 'secondary');
        plus.type = 'button';
        plus.onclick = () => setQty(p, qty + 1);
        plus.disabled = busy || qty >= maximum;

        row.append(
          minus,
          amount,
          plus
        );

        el.append(row);
        el.append(node('small', 'Subtotal: ' + money(subtotal(p, qty)), 'cart-subtotal'));

        items.append(el);
      });

      $('#cart-total').textContent = money(total);

      document.querySelectorAll('[data-pay]').forEach(b => b.disabled = busy || !cart.size);
      $('#clear-cart').disabled = busy;
    }

    // Cargar datos
    async function load() {
      const seq = ++searchSequence;
      try {
        const data = await api('/comercial/catalogo');
        if (seq !== searchSequence) return;

        catalog = data;
        const categories = new Map(data.map(p => [String(p.producto_id), p.producto]));

        $('#catalog-category').replaceChildren(new Option('Todas las categorías', ''));
        categories.forEach((name, id) => $('#catalog-category').append(new Option(name, id)));

        cart.forEach(entry => {
          const fresh = data.find(p => p.id === entry.p.id);
          if (fresh) {
            entry.p = fresh;
            entry.qty = Math.min(entry.qty, Number(fresh.stock));
          }
        });

        render();
      } catch (err) {
        $('#catalog-status').textContent = 'No se pudo cargar el catálogo.';
        feedback(err.message, true);
      }
    }

    // Procesar pago
    async function pay(method) {
      if (busy || !cart.size) return;

      const client = $('#sale-client').value.trim();

      if (method === 'credito' && !client) {
        feedback('Indica el cliente para registrar una venta a crédito.', true);
        $('#sale-client').focus();
        return;
      }

      busy = true;
      render();

      try {
        const result = await api('/comercial/ventas', {
          clave: key,
          metodo_pago: method,
          cliente: client || null,
          items: [...cart.values()].map(({ p, qty }) => ({
            presentacion_id: p.id,
            cantidad: qty
          }))
        });

        const ticket = $('#ticket-content');
        ticket.replaceChildren(
          node('p', 'Comprobante #' + result.ticket_id),
          node('h2', money(result.total)),
          node('p', 'Método: ' + method + ' · ' + (client || 'Consumidor Final'))
        );

        buildPrintableTicket(result);

        cart.clear();
        key = crypto.randomUUID();
        $('#sale-client').value = '';
        $('#ticket').showModal();
        feedback('Venta registrada y stock actualizado.');

        await load();
      } catch (err) {
        feedback(err.message + ' Tu carrito se conserva; puedes reintentar.', true);
      } finally {
        busy = false;
        render();
      }
    }

    // Event listeners finales
    $('#catalog-search').oninput = render;
    $('#catalog-category').onchange = render;
    
    $('#clear-cart').onclick = () => {
      cart.clear();
      key = crypto.randomUUID();
      render();
    };

    document.querySelectorAll('[data-pay]').forEach(b => b.onclick = () => pay(b.dataset.pay));
    $('#close-ticket').onclick = () => $('#ticket').close();
    $('#view-ticket').onclick = () => {
      $('#ticket').close();
      $('#ticket-preview').showModal();
    };
    $('#close-ticket-preview').onclick = () => $('#ticket-preview').close();
    $('#print-ticket-preview').onclick = () => window.print();
    $('#print-ticket').onclick = () => {
      $('#ticket').close();
      if (!$('#ticket-preview').open) $('#ticket-preview').showModal();
      requestAnimationFrame(() => window.print());
    };

    function buildPrintableTicket(result) {
      const printable = $('#printable-ticket');
      const grouped = new Map();

      (result.ventas || []).forEach(v => {
        const name = v.producto || 'Producto';
        const price = Number(v.precio);
        const key = name + '|' + price;
        const current = grouped.get(key) || { name, price, quantity: 0, total: 0 };
        current.quantity += Number(v.cantidad);
        current.total += Number(v.subtotal);
        grouped.set(key, current);
      });

      const heading = node('header');
      heading.append(
        node('h2', 'SISTEMA INTELIGENTE DE PRODUCCIÓN LÁCTEA'),
        node('p', 'Comprobante #' + result.ticket_id + '    Fecha: ' + new Date(result.fecha || Date.now()).toLocaleString('es-BO')),
        node('p', 'Cliente: ' + (result.cliente || 'Consumidor Final'))
      );

      const table = node('table');
      const head = node('thead');
      const headRow = node('tr');
      ['Producto', 'Cant.', 'Precio', 'Total'].forEach(label => headRow.append(node('th', label)));
      head.append(headRow);
      const body = node('tbody');
      grouped.forEach(line => {
        const row = node('tr');
        row.append(node('td', line.name), node('td', line.quantity.toFixed(0)), node('td', money(line.price)), node('td', money(line.total)));
        body.append(row);
      });
      table.append(head, body);

      const footer = node('footer');
      footer.append(
        node('strong', 'TOTAL: ' + money(result.total)),
        node('p', 'Método de pago: ' + result.metodo_pago),
        node('p', 'Gracias por su compra.')
      );
      printable.replaceChildren(heading, table, footer);
    }

    // Eventos de teclado
    posKeyHandler = e => {
      if (e.key === 'F2') {
        e.preventDefault();
        $('#catalog-search').focus();
      }
      if (e.key === 'F1') {
        e.preventDefault();
        if (!$('#ticket').open) pay('efectivo');
      }
      if (e.key === 'Escape' && !busy && !$('#ticket').open) {
        cart.clear();
        key = crypto.randomUUID();
        render();
      }
    };

    document.addEventListener('keydown', posKeyHandler);

    // Carga inicial
    load();
  }

  initializeCommercial();
  document.addEventListener('app:content-loaded', initializeCommercial);
})();
