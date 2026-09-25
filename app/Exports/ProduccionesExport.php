<?php

namespace App\Exports;

use App\Models\Produccion;
use App\Services\ReporteProduccionService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProduccionesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithStyles, WithTitle
{
    /**
     * Nombre de la pestaña u hoja de cálculo
     */
    public function title(): string
    {
        return 'Producciones';
    }

    /**
     * Obtención y mapeo de datos con carga optimizada
     */
    public function collection()
    {
        $reportes = app(ReporteProduccionService::class);

        return Produccion::with(['user', 'producto'])->orderByDesc('id')->get()->map(function ($produccion) use ($reportes) {
            $datosReporte = $reportes->calcularDatos($produccion);

            // Mapeo del tipo de cuajo (Formato legible)
            $tipoCuajo = match ($produccion->tipo_cuajo) {
                'liquido' => 'Líquido',
                'polvo' => 'En Polvo',
                'pastilla' => 'Pastilla',
                default => $produccion->tipo_cuajo ?? 'N/A'
            };

            $unidadCuajo = $produccion->producto?->unidad_cuajo ?? 'unid.';

            // Formateo de cantidades de cuajo
            $cantidadCuajo = $produccion->cantidad_cuajo
                ? number_format($produccion->cantidad_cuajo, 2).' '.$unidadCuajo
                : 'N/A';

            $cuajoRecomendado = $produccion->cuajo_recomendado
                ? number_format($produccion->cuajo_recomendado, 2).' '.$unidadCuajo
                : 'N/A';

            return [
                '#'.$produccion->id,
                $produccion->user?->name ?? 'N/A',
                $produccion->user?->rol ?? 'N/A',
                $produccion->user?->nombre_unidad_productiva ?? 'N/A',
                $produccion->producto?->nombre ?? 'N/A',
                $produccion->cantidad_leche ? number_format($produccion->cantidad_leche, 2).' L' : '0 L',
                $tipoCuajo,
                $cantidadCuajo,
                $cuajoRecomendado,
                number_format($produccion->temperatura_inicial ?? 0, 2).' °C',
                number_format($produccion->temperatura_minima ?? 0, 2).' °C',
                number_format($produccion->temperatura_maxima ?? 0, 2).' °C',
                number_format($produccion->temperatura_final ?? 0, 2).' °C',
                number_format($produccion->temperatura_promedio ?? 0, 2).' °C',
                number_format($produccion->temperatura_objetivo ?? 0, 2).' °C',
                strtoupper($produccion->estado),
                $produccion->fecha_inicio ? $produccion->fecha_inicio->format('d/m/Y H:i') : '-',
                $produccion->fecha_fin ? $produccion->fecha_fin->format('d/m/Y H:i') : 'Activo',
                $datosReporte['duracion'],
                $datosReporte['totalLecturas'].' lecturas',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Usuario',
            'Rol',
            'Unidad Productiva',
            'Producto',
            'Cantidad Leche',
            'Tipo Cuajo',
            'Cuajo Utilizado',
            'Cuajo Recomendado',
            'Temp. Inicial',
            'Temp. Mínima',
            'Temp. Máxima',
            'Temp. Final',
            'Temp. Promedio',
            'Temp. Objetivo',
            'Estado',
            'Fecha Inicio',
            'Fecha Fin',
            'Duración',
            'Total Lecturas',
        ];
    }

    /**
     * Estilos visuales de la hoja de cálculo
     */
    public function styles(Worksheet $sheet)
    {
        // 1. Aplicar estilo elegante al encabezado (Fila 1, de A a T)
        $sheet->getStyle('A1:T1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '198754'], // Verde Bootstrap
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Altura de la fila de cabecera para que respire
        $sheet->getRowDimension(1)->setRowHeight(28);

        // 2. Centrar columnas de datos técnicos y fechas para mejorar la legibilidad
        $totalFilas = $sheet->getHighestRow();
        if ($totalFilas > 1) {
            // Centramos ID (Columna A)
            $sheet->getStyle('A2:A'.$totalFilas)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Centramos desde Cantidad Leche hasta Total Lecturas (Columnas F a T)
            $sheet->getStyle('F2:T'.$totalFilas)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return [];
    }
}
