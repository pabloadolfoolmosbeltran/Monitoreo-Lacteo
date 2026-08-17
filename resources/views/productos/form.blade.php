<div class="mb-3">

    <label class="form-label">

        Nombre

    </label>

    <input
        type="text"
        name="nombre"
        class="form-control"
        value="{{ old('nombre', $producto->nombre ?? '') }}">

</div>

<div class="mb-3">

    <label class="form-label">

        Temperatura mínima (°C)

    </label>

    <input
        type="number"
        step="0.01"
        name="temperatura_minima"
        class="form-control"
        value="{{ old('temperatura_minima', $producto->temperatura_minima ?? '') }}">

</div>

<div class="mb-3">

    <label class="form-label">

        Temperatura máxima (°C)

    </label>

    <input
        type="number"
        step="0.01"
        name="temperatura_maxima"
        class="form-control"
        value="{{ old('temperatura_maxima', $producto->temperatura_maxima ?? '') }}">

</div>

<div class="mb-3">
    <label class="form-label">Temperatura de Pasteurización (°C)</label>
    <input type="number"
           step="0.1"
           name="temperatura_pasteurizacion"
           class="form-control"
           value="{{ old('temperatura_pasteurizacion', $producto->temperatura_pasteurizacion ?? '') }}"
           required>
</div>


<div class="mb-3">

    <label class="form-label">

        Descripción

    </label>

    <textarea
        name="descripcion"
        rows="4"
        class="form-control">{{ old('descripcion', $producto->descripcion ?? '') }}</textarea>

</div>

<div class="form-check mb-3">

    <input
        type="checkbox"
        class="form-check-input"
        name="activo"
        value="1"
        {{ old('activo', $producto->activo ?? true) ? 'checked' : '' }}>

    <label class="form-check-label">

        Producto Activo

    </label>

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
