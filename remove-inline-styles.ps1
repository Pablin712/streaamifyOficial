# Script para eliminar @section('styles') de vistas migradas a Enhanced Table v2
# Fecha: 1 de diciembre de 2025

$vistas = @(
    "resources\views\sales\clientes\index.blade.php",
    "resources\views\inventory\valores\index.blade.php",
    "resources\views\inventory\mantenimientos\index.blade.php",
    "resources\views\inventory\servicios\index.blade.php",
    "resources\views\inventory\cuentas\mails.blade.php",
    "resources\views\finance\costos.blade.php"
)

$contador = 0

foreach ($vista in $vistas) {
    $rutaCompleta = Join-Path -Path $PSScriptRoot -ChildPath $vista

    if (Test-Path $rutaCompleta) {
        Write-Host "Procesando: $vista" -ForegroundColor Cyan

        $contenido = Get-Content -Path $rutaCompleta -Raw

        # Buscar y eliminar @section('styles')...@endsection
        $regex = "@section\('styles'\)[\s\S]*?@endsection\s*\n"

        if ($contenido -match $regex) {
            $nuevoContenido = $contenido -replace $regex, ""
            Set-Content -Path $rutaCompleta -Value $nuevoContenido -NoNewline
            Write-Host "  ✓ Estilos inline eliminados" -ForegroundColor Green
            $contador++
        } else {
            Write-Host "  - No se encontró @section('styles')" -ForegroundColor Yellow
        }
    } else {
        Write-Host "  ✗ Archivo no encontrado: $rutaCompleta" -ForegroundColor Red
    }
}

Write-Host "`nResumen: $contador de $($vistas.Count) vistas procesadas exitosamente" -ForegroundColor Green
