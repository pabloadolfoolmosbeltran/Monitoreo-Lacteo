<?php

namespace App\Exports;

use App\Models\Produccion;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProduccionesExport implements FromCollection, WithHeadings, ShouldAutoSize, WithStyles, WithTitle
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
        return Produccion::with(['user', 'producto'])->orderByDesc('id')->get()->map(function ($produccion) {
            
            // Calculamos la duración de forma idéntica a tus reportes
            $duracion = 'N/A';
            if ($produccion->fecha_inicio) {
                $fin = $produccion->fecha_fin ?? now();
                $diff = $produccion->fecha_inicio->diff($fin);

                $partes = [];
                if ($diff->d > 0) $partes[] = $diff->d . ' ' . ($diff->d == 1 ? 'día' : 'días');
                if ($diff->h > 0) $partes[] = $diff->h . ' ' . ($diff->h == 1 ? 'hora' : 'horas');
                if ($diff->i > 0) $partes[] = $diff->i . ' ' . ($diff->i == 1 ? 'minuto' : 'minutos');

                $duracion = count($partes) > 0 ? implode(', ', $partes) : 'Menos de un minuto';
                if (!$produccion->fecha_fin) {
                    $duracion .= ' (En curso)';
                }
            }

            // Total de lecturas con fallback seguro
            $totalLecturas = $produccion->total_lecturas ?? $produccion->lecturas()->count();

            return [
                '#' . $produccion->id,
                $produccion->user?->name ?? 'N/A',
                $produccion->user?->rol ?? 'N/A',
                $produccion->user?->nombre_unidad_productiva ?? 'N/A',
                $produccion->producto?->nombre ?? 'N/A',
                $produccion->cantidad_leche ? $produccion->cantidad_leche . ' L' : '0 L',
                number_format($produccion->temperatura_inicial ?? 0, 2) . ' °C',
                number_format($produccion->temperatura_final ?? 0, 2) . ' °C',
                number_format($produccion->temperatura_promedio ?? 0, 2) . ' °C',
                number_format($produccion->temperatura_objetivo ?? 0, 2) . ' °C',
                strtoupper($produccion->estado),
                $produccion->fecha_inicio ? $produccion->fecha_inicio->format('d/m/Y H:i') : '-',
                $produccion->fecha_fin ? $produccion->fecha_fin->format('d/m/Y H:i') : 'Activo',
                $duracion,
                $totalLecturas . ' lecturas'
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
            'Temp. Inicial',
            'Temp. Final',
            'Temp. Promedio',
            'Temp. Objetivo',
            'Estado',
            'Fecha Inicio',
            'Fecha Fin',
            'Duración',
            'Total Lecturas'
        ];
    }
    /**
     * Estilos visuales de la hoja de cálculo
     */
    public function styles(Worksheet $sheet)
    {
        // 1. Aplicar estilo elegante al encabezado (Fila 1)
        $sheet->getStyle('A1:O1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '198754'], // Verde Bootstrap elegante
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
            // Centramos ID, Cantidad, Temperaturas, Estado, Fechas y Duración
            $sheet->getStyle('A2:A' . $totalFilas)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('F2:O' . $totalFilas)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return [];
    }
}