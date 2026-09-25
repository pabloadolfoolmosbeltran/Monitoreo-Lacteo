<div class="form-grid">
    <label>Nombres
        <input name="nombres" value="{{ old('nombres', $productor?->nombres) }}" maxlength="255" required autofocus>
    </label>
    <label>Primer apellido
        <input name="primer_apellido" value="{{ old('primer_apellido', $productor?->primer_apellido) }}" maxlength="255" required>
    </label>
    <label>Segundo apellido
        <input name="segundo_apellido" value="{{ old('segundo_apellido', $productor?->segundo_apellido) }}" maxlength="255">
    </label>
    <label>Teléfono
        <input name="telefono" type="tel" value="{{ old('telefono', $productor?->telefono) }}" maxlength="20" placeholder="Ej.: 70000000">
    </label>
    <label>Unidad productiva
        <input name="nombre_unidad_productiva" value="{{ old('nombre_unidad_productiva', $productor?->nombre_unidad_productiva) }}" maxlength="255" placeholder="Finca, granja o asociación">
    </label>
    <label>Estado
        <select name="activo">
            <option value="1" @selected((string) old('activo', $productor?->activo ?? true) === '1')>Activo</option>
            <option value="0" @selected((string) old('activo', $productor?->activo ?? true) === '0')>Inactivo</option>
        </select>
    </label>
</div>
<label class="mt-3">Dirección
    <textarea name="direccion" maxlength="1000" rows="3" placeholder="Dirección o referencia de la unidad productiva">{{ old('direccion', $productor?->direccion) }}</textarea>
</label>

@if($errors->any())
    <div class="error mt-3" role="alert">{{ $errors->first() }}</div>
@endif
