<div class="panel ingreso-item">
    <div class="form-grid">
        <label>
            Presentación
            <select name="items[{{ $index }}][presentacion_id]" data-presentation-select required>
                <option value="">
                    Seleccionar presentación
                </option>
                @foreach($presentaciones as $presentacion)
                <option value="{{ $presentacion->id }}" data-standard-price="{{ $presentacion->precio }}">
                    {{ $presentacion->producto->nombre ?? '' }}
                     ·
                     {{ $presentacion->nombre }}
                    </option>
                    @endforeach
                </select>
            </label>
            <label>
                Cantidad ingresada
                <input type="number" name="items[{{ $index }}][cantidad_ingresada]" min="1" step="1" required>
            </label>
            <label>
                Caducidad
                <input type="date" name="items[{{ $index }}][fecha_caducidad]" required>
            </label>
            <label>
                Precio de acopio por unidad (Bs.)
                <input type="number" name="items[{{ $index }}][precio_acopio_unitario]" min="0" step="0.01" placeholder="Opcional">
            </label>
            <label>
                Precio estándar de venta (Bs.)
                <input type="number" name="items[{{ $index }}][precio_venta_unitario]" data-sale-price min="0" step="0.01" readonly required>
                <small class="muted">
                    Definido en Presentaciones Comerciales; no se modifica en el ingreso.
                </small>
            </label>
            <div>
                <button type="button" class="text-button" data-remove-item>
                    Quitar lote
                </button>
            </div>
        </div>
    </div>
