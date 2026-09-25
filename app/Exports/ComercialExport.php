<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\{FromArray,WithHeadings,WithTitle,WithMultipleSheets,WithCustomValueBinder,ShouldAutoSize};
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class ComercialExport implements WithMultipleSheets
{
    public function __construct(private array $tablas) {}
    public function sheets(): array
    {
        return array_map(fn($t)=>new class($t) extends StringValueBinder implements FromArray,WithHeadings,WithTitle,WithCustomValueBinder,ShouldAutoSize {
            public function __construct(private array $tabla) {}
            public function array(): array { return $this->tabla['filas']; }
            public function headings(): array { return $this->tabla['columnas']; }
            public function title(): string { return $this->tabla['nombre']; }
        },$this->tablas);
    }
}

