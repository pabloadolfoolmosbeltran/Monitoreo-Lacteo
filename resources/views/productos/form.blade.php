<div class="mb-3">
    <label class="form-label">Nombre del Producto</label>
    <input
        type="text"
        name="nombre"
        id="nombre"
        class="form-control js-product-name"
        value="{{ old('nombre', $producto->nombre ?? '') }}"
        required
    >
    @error('nombre')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Imagen Referencial</label>
    <input
        type="file"
        name="imagen_referencial"
        class="form-control"
        accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
    >
    <div class="form-text">Formatos permitidos: JPG, PNG, GIF o WEBP. Tamaño máximo: 5 MB.</div>
    @if(isset($producto) && $producto->imagen_referencial)
        <div class="mt-2">
            <span class="text-muted small d-block mb-1">Imagen actual:</span>
            <img src="{{ asset('storage/' . $producto->imagen_referencial) }}" alt="Imagen actual" width="90" class="rounded shadow-sm">
        </div>
    @endif
    @error('imagen_referencial')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Cuajo por Litro (ml o g / Litro)</label>
    <input
        type="number"
        step="0.01"
        name="cuajo_por_litro"
        class="form-control"
        value="{{ old('cuajo_por_litro', $producto->cuajo_por_litro ?? '') }}"
        placeholder="Ej: 0.20"
        required
    >
    @error('cuajo_por_litro')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Tipo o Nombre del Cuajo</label>
    <input
        type="text"
        name="tipo_cuajo"
        class="form-control"
        value="{{ old('tipo_cuajo', $producto->tipo_cuajo ?? '') }}"
        placeholder="Ej: Cuajo líquido Hansen o Cuajo en polvo industrial"
    >
    @error('tipo_cuajo')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>
<div class="row">
    <div class="col-md-12 mb-3">
        <label for="unidad_cuajo" class="form-label">Unidad de Medida del Cuajo</label>
        <select name="unidad_cuajo" id="unidad_cuajo" class="form-select">
            <option value="ml" {{ old('unidad_cuajo', $producto->unidad_cuajo ?? 'ml') == 'ml' ? 'selected' : '' }}>Mililitros (ml)</option>
            <option value="g" {{ old('unidad_cuajo', $producto->unidad_cuajo ?? '') == 'g' ? 'selected' : '' }}>Gramos (g)</option>
            <option value="kg" {{ old('unidad_cuajo', $producto->unidad_cuajo ?? '') == 'kg' ? 'selected' : '' }}>Kilogramos (kg)</option>
            <option value="pastilla" {{ old('unidad_cuajo', $producto->unidad_cuajo ?? '') == 'pastilla' ? 'selected' : '' }}>Pastilla(s)</option>
            <option value="gotas" {{ old('unidad_cuajo', $producto->unidad_cuajo ?? '') == 'gotas' ? 'selected' : '' }}>Gotas</option>
        </select>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Temperatura mínima (°C)</label>
    <input
        type="number"
        step="0.01"
        name="temperatura_minima"
        class="form-control"
        value="{{ old('temperatura_minima', $producto->temperatura_minima ?? '') }}"
        required
    >
    @error('temperatura_minima')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Temperatura máxima (°C)</label>
    <input
        type="number"
        step="0.01"
        name="temperatura_maxima"
        class="form-control"
        value="{{ old('temperatura_maxima', $producto->temperatura_maxima ?? '') }}"
        required
    >
    @error('temperatura_maxima')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Temperatura de Pasteurización (°C)</label>
    <input
        type="number"
        step="0.01"
        name="temperatura_pasteurizacion"
        class="form-control"
        value="{{ old('temperatura_pasteurizacion', $producto->temperatura_pasteurizacion ?? '') }}"
        required
    >
    @error('temperatura_pasteurizacion')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label class="form-label">Descripción</label>
    <textarea
        name="descripcion"
        rows="4"
        class="form-control">{{ old('descripcion', $producto->descripcion ?? '') }}</textarea>
    @error('descripcion')
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

<div class="form-check mb-3">
    <input
        type="checkbox"
        class="form-check-input"
        name="activo"
        value="1"
        {{ old('activo', $producto->activo ?? true) ? 'checked' : '' }}>
    <label class="form-check-label">Producto Activo</label>
</div>

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif
