<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label">Producto</label>
        <select name="producto_id" class="form-select" required>
            <option value="">Seleccione un producto</option>
            @foreach($productos as $producto)
                <option value="{{ $producto->id }}" @selected(old('producto_id', $presentacion->producto_id ?? null) == $producto->id)>
                    {{ $producto->nombre }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label class="form-label">Nombre de la presentación</label>
        <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $presentacion->nombre ?? '') }}" placeholder="Ej.: Yogurt Frutilla 1 L" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Envase</label>
        <input type="text" name="envase" class="form-control" value="{{ old('envase', $presentacion->envase ?? '') }}" placeholder="Ej.: Botella, Bandeja, Pote">
    </div>

    <div class="col-md-6">
        <label class="form-label">Sabor</label>
        <input type="text" name="sabor" class="form-control" value="{{ old('sabor', $presentacion->sabor ?? '') }}" placeholder="Ej.: Frutilla, Vainilla, Natural">
    </div>

    <div class="col-md-4">
        <label class="form-label">Contenido netamente</label>
        <input type="number" step="0.01" name="contenido" class="form-control" value="{{ old('contenido', $presentacion->contenido ?? '') }}" placeholder="Ej.: 1 o 500">
    </div>

    <div class="col-md-4">
        <label class="form-label">Unidad de medida</label>
        <select name="unidad" class="form-select">
            <option value="L" @selected(old('unidad', $presentacion->unidad ?? '') == 'L')>Litros (L)</option>
            <option value="ml" @selected(old('unidad', $presentacion->unidad ?? '') == 'ml')>Mililitros (ml)</option>
            <option value="g" @selected(old('unidad', $presentacion->unidad ?? '') == 'g')>Gramos (g)</option>
            <option value="kg" @selected(old('unidad', $presentacion->unidad ?? '') == 'kg')>Kilogramos (kg)</option>
        </select>
    </div>

    <div class="col-md-4 d-flex align-items-end">
        <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" name="con_fruta" id="con_fruta" value="1" @checked(old('con_fruta', $presentacion->con_fruta ?? false))>
            <label class="form-check-label fw-bold" for="con_fruta">¿Contiene fruta agregada?</label>
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label">Precio (Bs)</label>
        <input type="number" name="precio" class="form-control" value="{{ old('precio', $presentacion->precio ?? '') }}" min="0" step="0.01" required>
    </div>

    <div class="col-md-6">
        <label class="form-label">Existencia disponible</label>
        <input type="text" class="form-control" value="{{ number_format($presentacion->stock ?? 0, 0) }}" readonly>
        <small>Se actualiza desde entradas, ventas y ajustes por lote.</small>
        <label class="form-label">Umbral de alerta de existencia</label>
        <input type="number" name="stock_minimo_alerta" class="form-control" value="{{ old('stock_minimo_alerta', $presentacion->stock_minimo_alerta ?? 5) }}" min="0" step="1">
    </div>

    <div class="col-12">
        <label class="form-label">Imagen comercial</label>
        <input type="file" name="imagen_comercial" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp">
        <div class="form-text">Formatos permitidos: JPG, PNG o WEBP. Tamaño máximo: 5 MB.</div>
        @isset($presentacion)
            @if($presentacion->imagen_comercial)
                <img class="rounded mt-2" src="{{ asset('storage/' . $presentacion->imagen_comercial) }}" alt="Imagen actual" width="110">
            @endif
        @endisset
    </div>
</div>

@if($errors->any())
    <div class="alert alert-danger mt-3 mb-0">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
