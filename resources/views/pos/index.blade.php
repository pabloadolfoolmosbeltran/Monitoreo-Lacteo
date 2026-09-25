@extends('layouts.app')

@section('commercial-panel', '1')

@section('title', 'Ventas')

@section('subtitle', 'Punto de venta rápido con existencia por lote y trazabilidad del proveedor.')

@section('actions')
    <a class="button secondary" href="/ingresos-productores/create">+ Nueva entrada</a>
@endsection

@section('content')
    <div id="pos" class="pos-grid">
        <section>
            <div class="hero">
                <span class="pill">ACOPIO LOCAL</span>
                <h2>Frescura que puedes rastrear.</h2>
                <p>Cada venta conserva su proveedor, lote y caducidad.</p>
                <div class="hero-note">
                    Asignación automática por próxima caducidad &middot; FEFO
                </div>
            </div>

            <div class="toolbar">
                <label class="search">
                    Buscar producto
                    <input id="catalog-search"
                           type="search"
                           placeholder="Nombre o presentación… (F2)">
                </label>

                <label>
                    Categoría
                    <select id="catalog-category">
                        <option value="">Todas las categorías</option>
                    </select>
                </label>
            </div>

            <div id="catalog-status"
                 class="muted"
                 aria-live="polite">
                Cargando catálogo…
            </div>

            <div id="catalog" class="catalog"></div>
        </section>

        <aside class="cart panel">
            <div class="section-heading">
                <h2>Carrito de venta</h2>
                <button type="button"
                        id="clear-cart"
                        class="text-button">
                    Vaciar
                </button>
            </div>

            <div id="cart-items" aria-live="polite"></div>

            <div class="cart-footer">
                <div class="total">
                    <span>Total estimado</span>
                    <strong id="cart-total">Bs. 0,00</strong>
                </div>

                <p class="muted small">
                    Las ventas se registran únicamente por unidades enteras.
                    El servidor confirma precios y disponibilidad al cobrar.
                </p>

                <label>
                    Cliente
                    <input id="sale-client"
                           placeholder="Obligatorio para crédito"
                           maxlength="200">
                </label>

                <div class="payment-buttons">
                    <button type="button" data-pay="efectivo">
                        Efectivo <small>F1</small>
                    </button>
                    <button type="button" data-pay="qr" class="secondary">
                        QR <small>Registrar</small>
                    </button>
                </div>
            </div>
        </aside>
    </div>

    <dialog id="ticket">
        <div class="section-heading">
            <h2>Venta registrada</h2>
            <button type="button"
                    id="close-ticket"
                    class="secondary">
                Cerrar
            </button>
        </div>

        <div id="ticket-content"></div>

        <div class="ticket-actions">
            <button type="button" id="view-ticket" class="secondary">
                Ver PDF
            </button>
            <button type="button" id="print-ticket" class="secondary">
                Imprimir comprobante
            </button>
        </div>
    </dialog>

    <dialog id="ticket-preview" aria-labelledby="ticket-preview-title">
        <div class="ticket-preview-toolbar">
            <strong id="ticket-preview-title">Vista previa del comprobante</strong>
            <button type="button" id="close-ticket-preview" class="secondary">Cerrar</button>
        </div>
        <article id="printable-ticket" class="printable-ticket"></article>
        <div class="ticket-actions">
            <button type="button" id="print-ticket-preview">Imprimir</button>
        </div>
    </dialog>
@endsection
