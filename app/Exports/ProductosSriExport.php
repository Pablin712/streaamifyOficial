<?php

namespace App\Exports;

use App\Models\Producto;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ProductosSriExport
{
    // Inicial de facturación por servicio (1 letra para componer códigos únicos)
    private const INICIALES = [
        'NETFLIX'   => 'N',
        'DISNEYP'   => 'Y',
        'DISNEYS'   => 'D',
        'MAX'       => 'M',
        'PRIME'     => 'P',
        'PARAMOUNT' => 'T',
        'CRUNCHY'   => 'C',
        'SPOTIFY'   => 'S',
        'MAGIS'     => 'G',
    ];

    // Mapeo: idser → nombre genérico + descripción para facturar en el SRI
    private const MAPA_SRI = [
        'NETFLIX'   => [
            'nombre' => 'Membresía digital de entretenimiento audiovisual',
            'info'   => 'Renovación mensual de servicio digital de películas y series solicitado por el cliente',
        ],
        'DISNEYP'   => [
            'nombre' => 'Membresía digital de entretenimiento familiar',
            'info'   => 'Renovación mensual de servicio digital de películas, series y contenido familiar',
        ],
        'DISNEYS'   => [
            'nombre' => 'Membresía digital de entretenimiento familiar',
            'info'   => 'Renovación mensual de servicio digital de películas, series y contenido familiar',
        ],
        'MAX'       => [
            'nombre' => 'Membresía digital audiovisual premium',
            'info'   => 'Gestión mensual de acceso a servicio digital de películas, series y entretenimiento',
        ],
        'PRIME'     => [
            'nombre' => 'Membresía digital de video bajo demanda',
            'info'   => 'Renovación mensual de servicio digital de video bajo demanda',
        ],
        'PARAMOUNT' => [
            'nombre' => 'Membresía digital de películas y series',
            'info'   => 'Gestión mensual de servicio digital de entretenimiento audiovisual',
        ],
        'CRUNCHY'   => [
            'nombre' => 'Membresía digital de anime y entretenimiento',
            'info'   => 'Renovación mensual de servicio digital de anime, series animadas y contenido audiovisual',
        ],
        'SPOTIFY'   => [
            'nombre' => 'Membresía digital de audio y música',
            'info'   => 'Gestión mensual de servicio digital de música, audio y podcasts',
        ],
        'MAGIS'     => [
            'nombre' => 'Servicio digital de televisión por streaming',
            'info'   => 'Gestión mensual de acceso a servicio digital de televisión y canales por internet',
        ],
    ];

    public function generate(): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Productos SRI');

        // Fila 1: Título centrado (igual a plantilla oficial del SRI)
        $sheet->mergeCells('A1:I1');
        $sheet->setCellValue('A1', 'Plantilla de carga masiva Productos Facturador SRI');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '1F3864']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // Fila 2: Cabeceras (fondo azul, texto blanco, negrita)
        $headers = [
            'A2' => 'CÓDIGO PRINCIPAL',
            'B2' => 'CÓDIGO AUXILIAR',
            'C2' => 'DESCRIPCIÓN PRODUCTO',
            'D2' => 'INFORMACIÓN ADICIONAL',
            'E2' => 'PRECIO UNITARIO',
            'F2' => 'TARIFA DE IVA',
            'G2' => 'GRAVA ICE',
            'H2' => 'CÓDIGO ICE',
            'I2' => 'TURISMO',
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        $sheet->getStyle('A2:I2')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(18);

        // Datos desde la BD
        $productos = Producto::with('detalles')->where('activo', true)->get();
        $row = 3;
        $secuencial = 1;

        foreach ($productos as $producto) {
            [$nombre, $info] = $this->resolverNombreSri($producto);

            $sheet->setCellValue("A{$row}", $producto->codigopro);
            $sheet->setCellValue("B{$row}", sprintf('F%06d', $secuencial));
            $sheet->setCellValue("C{$row}", $nombre);
            $sheet->setCellValue("D{$row}", $info);
            $sheet->setCellValue("E{$row}", (float) $producto->preciopro);
            $sheet->setCellValue("F{$row}", 4);   // 15% IVA vigente Ecuador
            $sheet->setCellValue("G{$row}", 'NO');
            $sheet->setCellValue("H{$row}", 3000);
            $sheet->setCellValue("I{$row}", 'No');

            // Formato numérico con 6 decimales para precio (requerido por SRI)
            $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode('0.000000');

            $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D9D9D9']]],
            ]);

            $row++;
            $secuencial++;
        }

        // Ancho de columnas
        $sheet->getColumnDimension('A')->setWidth(18);
        $sheet->getColumnDimension('B')->setWidth(16);
        $sheet->getColumnDimension('C')->setWidth(48);
        $sheet->getColumnDimension('D')->setWidth(60);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(14);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setWidth(12);
        $sheet->getColumnDimension('I')->setWidth(10);

        $path = sys_get_temp_dir() . '/productos_sri_' . time() . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return $path;
    }

    private function resolverNombreSri(Producto $producto): array
    {
        $detalles    = $producto->detalles;
        $monthCount  = $detalles->isNotEmpty() ? (int) $detalles->first()->meses : 1;
        $uniqueIds   = $detalles->pluck('idser')->map(fn ($s) => strtoupper(trim($s)))->unique()->values();
        $uniqueCount = $uniqueIds->count();
        $deviceCount = $uniqueCount > 0 ? max(1, (int) floor($detalles->count() / $uniqueCount)) : 1;

        $mesLabel    = $monthCount  === 1 ? '1 mes'       : "{$monthCount} meses";
        $dispLabel   = $deviceCount === 1 ? '1 membresía' : "{$deviceCount} membresías";

        // Construir etiqueta de iniciales: N / NY / NYD / NDS3 / NSDF4 …
        $letras = $uniqueIds
            ->map(fn ($id) => self::INICIALES[$id] ?? strtoupper(substr($id, 0, 1)))
            ->implode('');
        $tag = $uniqueCount >= 3 ? $letras . $uniqueCount : $letras;

        if ($uniqueCount > 1) {
            return [
                "Combo membresías digitales {$tag} - {$mesLabel}",
                "Combo {$tag}: gestión de {$dispLabel} por servicio durante {$mesLabel}, servicios digitales de entretenimiento",
            ];
        }

        $idser = $uniqueIds->first() ?? '';

        if (isset(self::MAPA_SRI[$idser])) {
            return [
                self::MAPA_SRI[$idser]['nombre'] . " {$tag} - {$mesLabel}",
                self::MAPA_SRI[$idser]['info'] . " [{$tag}] - {$dispLabel}, {$mesLabel}",
            ];
        }

        return [
            "Suscripción digital {$tag} - {$mesLabel}",
            ($producto->descripcionpro ?? 'Servicio digital de entretenimiento') . " [{$tag}] - {$dispLabel}, {$mesLabel}",
        ];
    }
}
