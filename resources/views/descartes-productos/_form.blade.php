@csrf
@if(isset($descarte)) @method('PUT') @endif
<label>
    Lote
    <select name="lote_id" required>
        <option value="">
            Seleccione
        </option>
        @foreach($lotes as $lote)
            <option value="{{ $lote->id }}" @selected((string)old('lote_id',$descarte->lote_id??'')===(string)$lote->id)>
                {{ $lote->id }} 
                · {{ $lote->presentacion?->producto?->nombre }} 
                / {{ $lote->presentacion?->nombre }} 
                · {{ $lote->ingresoProductor?->productor?->nombre_completo }} 
                · {{ $lote->cantidad_disponible }} disponibles 
                · vence {{ $lote->fecha_caducidad?->format('d/m/Y') ?? 'sin fecha' }}
            </option>
        @endforeach
    </select>
</label>
<label>
    Cantidad descartada
    <input type="number" name="cantidad" min="1" step="1" required value="{{ old('cantidad',$descarte->cantidad??'') }}">
</label>
<label>
    Motivo
    <select name="tipo_motivo" required>
            @foreach(['caducado'=>'Caducado','dañado'=>'Dañado','deterioro'=>'Deterioro','otro'=>'Otro'] as $valor=>$texto)
                <option value="{{ $valor }}" @selected(old('tipo_motivo',$descarte->tipo_motivo??'')===$valor)>
                {{ $texto }}
            </option>
            @endforeach
    </select>
</label>
<label>
    Notas
    <textarea name="notas" maxlength="2000">
    {{ old('notas',$descarte->notas??'') }}
</textarea>
</label>
<button>{{ isset($descarte) ? 'Actualizar descarte' : 'Registrar descarte pendiente' }}</button>
