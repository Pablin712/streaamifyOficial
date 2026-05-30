<?php

namespace App\Exports;

use App\Models\Transaccion;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Carbon\Carbon;

class TransaccionesExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithTitle, WithEvents
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Transaccion::with('banco')->where('anulada', false);

        if (!empty($this->filters['banco_id'])) {
            $query->where('banco_id', $this->filters['banco_id']);
        }
        if (!empty($this->filters['fecha_inicio'])) {
            $query->whereDate('fecha', '>=', $this->filters['fecha_inicio']);
        }
        if (!empty($this->filters['fecha_fin'])) {
            $query->whereDate('fecha', '<=', $this->filters['fecha_fin']);
        }
        if (!empty($this->filters['tipo']) && $this->filters['tipo'] !== 'todos') {
            $query->where('tipo', $this->filters['tipo']);
        }

        return $query->orderBy('fecha', 'desc');
    }

    public function headings(): array
    {
        return ['ID', 'Banco', 'Tipo', 'Monto Anterior ($)', 'Monto Transacción ($)', 'Monto Actualizado ($)', 'Referencia', 'Fecha'];
    }

    public function map($t): array
    {
        return [
            $t->id,
            $t->banco->nombreban ?? 'N/A',
            ucfirst($t->tipo),
            number_format($t->monto_anterior, 2),
            number_format($t->monto_transaccion, 2),
            number_format($t->monto_actualizado, 2),
            $t->referencia ?? '-',
            Carbon::parse($t->fecha)->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1e3a8a']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // Zebra striping
                for ($row = 2; $row <= $highestRow; $row++) {
                    $color = ($row % 2 === 0) ? 'EFF6FF' : 'FFFFFF';
                    $sheet->getStyle("A{$row}:H{$row}")->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()->setRGB($color);

                    // Color tipo column
                    $tipo = strtolower($sheet->getCell("C{$row}")->getValue());
                    $tipoColor = $tipo === 'ingreso' ? '166534' : '991B1B';
                    $sheet->getStyle("C{$row}")->getFont()->getColor()->setRGB($tipoColor);
                    $sheet->getStyle("C{$row}")->getFont()->setBold(true);
                }

                // Borders
                $sheet->getStyle("A1:H{$highestRow}")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setRGB('CBD5E1');

                // Center ID and Type columns
                $sheet->getStyle("A1:A{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("C1:C{$highestRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }

    public function title(): string
    {
        return 'Transacciones';
    }
}
