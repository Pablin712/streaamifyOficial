<?php

namespace App\Console\Commands;

use App\Models\Categoria;
use App\Models\DetalleProducto;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\TipoProducto;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SyncCatalogProductsCommand extends Command
{
    protected $signature = 'catalog:sync-products
        {--services= : Servicios individuales separados por coma. Ej: NETFLIX,DISNEYP,SPOTIFY}
        {--months=1 : Meses separados por coma. Ej: 1,2,3,6,12}
        {--devices=1 : Dispositivos separados por coma. Ej: 1,2,3,4}
        {--combos= : Combos separados por coma usando + entre servicios. Ej: NETFLIX+DISNEYP,NETFLIX+SPOTIFY+MAX}
        {--type=Inmediata : Tipo de producto: Inmediata, Pedido o Personalizado}
        {--discount=10 : Descuento moderado para planes de 2 o más meses, en porcentaje}
        {--stars=4 : Estrellas por defecto}
        {--active=1 : Activo por defecto 1/0}
        {--spec= : Ruta a archivo JSON con la matriz de productos a generar}
        {--service-photo-map= : JSON con mapa servicio=>ruta imagen relativa a public/}
        {--generate-combo-images : Genera imagen mosaico para combos}
        {--combo-image-dir=public/storage/fotos/auto-combos : Carpeta de salida para imágenes de combo}
        {--dry-run : Solo muestra qué crearía o actualizaría}
    }';

    protected $description = 'Crea o actualiza masivamente productos de tienda (individuales, multi-dispositivo y combos) usando precios basados en servicios.';

    /** @var array<string, string> */
    private array $servicePhotoMap = [];

    public function handle(): int
    {
        $type = TipoProducto::where('nombre', $this->option('type'))->first();
        $categoriaIndividual = Categoria::where('nombre', 'Individual')->first();
        $categoriaCombo = Categoria::where('nombre', 'Combo')->first();

        if (!$type || !$categoriaIndividual || !$categoriaCombo) {
            $this->error('Faltan catálogos base. Verifica tipos_producto y categorias.');
            return self::FAILURE;
        }

        $definitions = $this->loadDefinitions();
        if ($definitions->isEmpty()) {
            $this->error('No se encontraron definiciones para procesar. Usa opciones o --spec.');
            return self::FAILURE;
        }

        $services = Servicio::query()->get()->keyBy('idser');
        $this->servicePhotoMap = $this->loadServicePhotoMap();
        $rows = [];
        $created = 0;
        $updated = 0;
        $errors = 0;

        foreach ($definitions as $definition) {
            try {
                $normalized = $this->normalizeDefinition($definition, $services);
                $payload = $this->buildProductPayload(
                    definition: $normalized,
                    typeId: (int) $type->id,
                    categoriaIndividualId: (int) $categoriaIndividual->id,
                    categoriaComboId: (int) $categoriaCombo->id,
                );

                $action = 'dry-run';

                if (!$this->option('dry-run')) {
                    DB::transaction(function () use ($payload, &$action, &$created, &$updated): void {
                        $producto = Producto::where('codigopro', $payload['codigopro'])->first();
                        $action = $producto ? 'updated' : 'created';

                        if (!$producto) {
                            $producto = new Producto();
                            $created++;
                        } else {
                            $updated++;
                        }

                        $producto->fill(Arr::except($payload, ['detalles']));
                        $producto->save();

                        DetalleProducto::where('producto_id', $producto->id)->delete();

                        foreach ($payload['detalles'] as $detalle) {
                            DetalleProducto::create([
                                'producto_id' => $producto->id,
                                'idser' => $detalle['idser'],
                                'descripcion' => $detalle['descripcion'],
                                'meses' => $detalle['meses'],
                            ]);
                        }
                    });
                }

                $rows[] = [
                    $payload['codigopro'],
                    Str::limit($payload['nombrepro'], 42),
                    number_format((float) $payload['preciopro'], 2),
                    $payload['categoria_id'] === (int) $categoriaCombo->id ? 'Combo' : 'Individual',
                    count($payload['detalles']),
                    $action,
                ];
            } catch (\Throwable $throwable) {
                $errors++;
                $this->warn('Error procesando definición: ' . $throwable->getMessage());
            }
        }

        $this->table(['Código', 'Nombre', 'Precio', 'Categoría', 'Detalles', 'Acción'], $rows);

        if ($this->option('dry-run')) {
            $this->info('Dry run completado. No se escribieron cambios.');
        } else {
            $this->info("Productos creados: {$created}");
            $this->info("Productos actualizados: {$updated}");
        }

        if ($errors > 0) {
            $this->warn("Definiciones con error: {$errors}");
        }

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function loadDefinitions(): Collection
    {
        if ($spec = $this->option('spec')) {
            $path = base_path($spec);
            if (!is_file($path)) {
                throw new \RuntimeException('No existe el archivo de especificación: ' . $path);
            }

            $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

            return collect($this->expandSpecDefinitions($decoded));
        }

        $months = $this->parseIntList((string) $this->option('months'));
        $devices = $this->parseIntList((string) $this->option('devices'));
        $individualServices = $this->parseStringList((string) $this->option('services'));
        $combos = collect($this->parseStringList((string) $this->option('combos')))
            ->map(fn (string $combo) => array_values(array_filter(array_map('trim', explode('+', strtoupper($combo))))));

        $definitions = collect();

        foreach ($individualServices as $serviceId) {
            foreach ($months as $month) {
                foreach ($devices as $deviceCount) {
                    $definitions->push([
                        'services' => [$serviceId],
                        'months' => $month,
                        'devices' => $deviceCount,
                    ]);
                }
            }
        }

        foreach ($combos as $comboServices) {
            foreach ($months as $month) {
                foreach ($devices as $deviceCount) {
                    $definitions->push([
                        'services' => $comboServices,
                        'months' => $month,
                        'devices' => $deviceCount,
                    ]);
                }
            }
        }

        return $definitions;
    }

    private function expandSpecDefinitions(array $spec): array
    {
        $defaults = $spec['defaults'] ?? [];
        $definitions = [];

        foreach (($spec['individuals'] ?? []) as $item) {
            $entry = is_string($item) ? ['service' => $item] : $item;
            foreach ($this->expandEntry($entry, $defaults, false) as $definition) {
                $definitions[] = $definition;
            }
        }

        foreach (($spec['combos'] ?? []) as $item) {
            foreach ($this->expandEntry($item, $defaults, true) as $definition) {
                $definitions[] = $definition;
            }
        }

        return $definitions;
    }

    private function expandEntry(array $entry, array $defaults, bool $isCombo): array
    {
        $months = $entry['months'] ?? $defaults['months'] ?? [1];
        $devices = $entry['devices'] ?? $defaults['devices'] ?? [1];
        $services = $isCombo
            ? ($entry['services'] ?? [])
            : [($entry['service'] ?? $entry['services'][0] ?? null)];

        $expanded = [];

        foreach ($months as $month) {
            foreach ($devices as $deviceCount) {
                $expanded[] = [
                    'services' => $services,
                    'months' => (int) $month,
                    'devices' => (int) $deviceCount,
                    'stars' => $entry['stars'] ?? $defaults['stars'] ?? $this->option('stars'),
                    'active' => $entry['active'] ?? $defaults['active'] ?? (int) $this->option('active'),
                    'photo' => $entry['photo'] ?? $defaults['photo'] ?? null,
                    'name' => $entry['name'] ?? null,
                    'description' => $entry['description'] ?? null,
                    'discount' => $entry['discount'] ?? $defaults['discount'] ?? $this->option('discount'),
                    'type' => $entry['type'] ?? $defaults['type'] ?? $this->option('type'),
                    'price_override' => $entry['price_override'] ?? null,
                ];
            }
        }

        return $expanded;
    }

    private function normalizeDefinition(array $definition, Collection $services): array
    {
        $serviceIds = collect($definition['services'] ?? [])
            ->map(fn ($serviceId) => strtoupper(trim((string) $serviceId)))
            ->filter()
            ->values();

        if ($serviceIds->isEmpty()) {
            throw new \InvalidArgumentException('La definición debe incluir al menos un servicio.');
        }

        $monthCount = (int) ($definition['months'] ?? 1);
        $deviceCount = (int) ($definition['devices'] ?? 1);

        if ($monthCount < 1 || $monthCount > 24) {
            throw new \InvalidArgumentException('Los meses deben estar entre 1 y 24.');
        }

        if ($deviceCount < 1 || $deviceCount > 10) {
            throw new \InvalidArgumentException('Los dispositivos deben estar entre 1 y 10.');
        }

        $resolvedServices = $serviceIds->map(function (string $serviceId) use ($services) {
            $service = $services->get($serviceId);

            if (!$service) {
                throw new \InvalidArgumentException('Servicio no encontrado: ' . $serviceId);
            }

            return $service;
        });

        $detailServices = collect();
        foreach ($resolvedServices as $service) {
            for ($index = 0; $index < $deviceCount; $index++) {
                $detailServices->push($service);
            }
        }

        return [
            'services' => $resolvedServices,
            'detail_services' => $detailServices,
            'months' => $monthCount,
            'devices' => $deviceCount,
            'stars' => (int) ($definition['stars'] ?? $this->option('stars')),
            'active' => (bool) ($definition['active'] ?? (int) $this->option('active')),
            'photo' => $definition['photo'] ?? null,
            'name' => $definition['name'] ?? null,
            'description' => $definition['description'] ?? null,
            'discount' => ((float) ($definition['discount'] ?? $this->option('discount'))) / 100,
            'price_override' => isset($definition['price_override']) ? (float) $definition['price_override'] : null,
        ];
    }

    private function buildProductPayload(array $definition, int $typeId, int $categoriaIndividualId, int $categoriaComboId): array
    {
        /** @var \Illuminate\Support\Collection $services */
        $services = $definition['services'];
        /** @var \Illuminate\Support\Collection $detailServices */
        $detailServices = $definition['detail_services'];
        $monthCount = (int) $definition['months'];
        $deviceCount = (int) $definition['devices'];
        $uniqueServiceCount = $services->count();
        $categoriaId = $uniqueServiceCount > 1 ? $categoriaComboId : $categoriaIndividualId;

        $baseMonthlyPrice = $detailServices->sum(function (Servicio $service) use ($detailServices): float {
            return $detailServices->count() === 1
                ? (float) ($service->precioser ?? 0)
                : (float) ($service->comboser ?? $service->precioser ?? 0);
        });

        $price = $definition['price_override'];
        if ($price === null) {
            $price = $monthCount === 1
                ? $baseMonthlyPrice
                : $baseMonthlyPrice * $monthCount * (1 - (float) $definition['discount']);
        }

        $serviceNames = $services->map(fn (Servicio $service) => $service->nombreser)->values();
        $serviceKey = $services->map(fn (Servicio $service) => $service->idser)->sort()->implode('+');
        $codePrefix = $uniqueServiceCount > 1 ? 'C' : 'I';
        $codeHash = strtoupper(substr(md5($serviceKey), 0, 8));
        $productCode = sprintf('%s%02dM%02dD%s', $codePrefix, $monthCount, $deviceCount, $codeHash);

        $productName = $definition['name'] ?: ($uniqueServiceCount > 1
            ? 'Combo ' . $serviceNames->implode(' + ') . " {$monthCount}M {$deviceCount}D"
            : $serviceNames->first() . " {$monthCount}M {$deviceCount}D");

        $description = $definition['description'] ?: ($uniqueServiceCount > 1
            ? 'Combo generado automáticamente para ' . $serviceNames->implode(', ') . ". {$monthCount} mes(es), {$deviceCount} dispositivo(s) por servicio."
            : "Plan generado automáticamente para {$serviceNames->first()}. {$monthCount} mes(es), {$deviceCount} dispositivo(s).");

        $detailsPayload = $detailServices->map(function (Servicio $service) use ($monthCount, $deviceCount, $uniqueServiceCount) {
            $detailLabel = $uniqueServiceCount > 1
                ? 'Detalle combo'
                : 'Detalle individual';

            return [
                'idser' => $service->idser,
                'descripcion' => "{$detailLabel}: {$service->nombreser}, {$monthCount} mes(es), {$deviceCount} dispositivo(s).",
                'meses' => $monthCount,
            ];
        })->values()->toArray();

        return [
            'codigopro' => $productCode,
            'nombrepro' => Str::limit($productName, 100, ''),
            'preciopro' => round((float) $price, 2),
            'estrellaspro' => max(0, min(5, (int) $definition['stars'])),
            'descripcionpro' => $description,
            'foto' => $definition['photo'] ?: $this->resolvePhoto($services, $categoriaId, $productCode, $monthCount, $deviceCount),
            'tipo_producto_id' => $typeId,
            'categoria_id' => $categoriaId,
            'activo' => $definition['active'],
            'detalles' => $detailsPayload,
        ];
    }

    private function resolvePhoto(Collection $services, int $categoriaId, string $productCode, int $monthCount, int $deviceCount): ?string
    {
        $existingPhoto = $this->guessPhoto($services, $categoriaId);
        if ($existingPhoto && $this->isUsablePublicFile($existingPhoto)) {
            return $this->normalizeRelativePublicPath($existingPhoto);
        }

        $uniqueServiceIds = $services->map(fn (Servicio $service) => (string) $service->idser)->unique()->values();

        if ($uniqueServiceIds->count() === 1) {
            $serviceId = $uniqueServiceIds->first();
            if ($serviceId && isset($this->servicePhotoMap[$serviceId]) && $this->isUsablePublicFile($this->servicePhotoMap[$serviceId])) {
                return $this->normalizeRelativePublicPath($this->servicePhotoMap[$serviceId]);
            }
        }

        if ($uniqueServiceIds->count() > 1 && (bool) $this->option('generate-combo-images')) {
            $generated = $this->generateComboImage($uniqueServiceIds->all(), $productCode, $monthCount, $deviceCount);
            if ($generated !== null) {
                return $generated;
            }
        }

        if ($uniqueServiceIds->isNotEmpty()) {
            $fallbackService = (string) $uniqueServiceIds->first();
            if (isset($this->servicePhotoMap[$fallbackService]) && $this->isUsablePublicFile($this->servicePhotoMap[$fallbackService])) {
                return $this->normalizeRelativePublicPath($this->servicePhotoMap[$fallbackService]);
            }
        }

        return null;
    }

    private function guessPhoto(Collection $services, int $categoriaId): ?string
    {
        $serviceIds = $services->map(fn (Servicio $service) => $service->idser)->sort()->values();

        if ($serviceIds->count() === 1) {
            $existingProduct = Producto::whereHas('detalles', function ($query) use ($serviceIds) {
                $query->where('idser', $serviceIds->first());
            })->where('categoria_id', $categoriaId)->whereNotNull('foto')->first();

            return $existingProduct?->foto;
        }

        $existingProducts = Producto::with('detalles')->where('categoria_id', $categoriaId)->whereNotNull('foto')->get();
        foreach ($existingProducts as $existingProduct) {
            $existingServices = $existingProduct->detalles->pluck('idser')->sort()->values();
            if ($existingServices->unique()->values()->toArray() === $serviceIds->toArray()) {
                return $existingProduct->foto;
            }
        }

        return null;
    }

    /** @return array<string, string> */
    private function loadServicePhotoMap(): array
    {
        $map = [];

        $fromExistingProducts = Producto::with('detalles')
            ->whereNotNull('foto')
            ->get();

        foreach ($fromExistingProducts as $producto) {
            $serviceIds = $producto->detalles->pluck('idser')->filter()->unique()->values();
            if ($serviceIds->count() === 1) {
                $serviceId = (string) $serviceIds->first();
                $photo = (string) $producto->foto;
                if ($photo !== '' && $this->isUsablePublicFile($photo)) {
                    $map[$serviceId] = $this->normalizeRelativePublicPath($photo);
                }
            }
        }

        $mapFileOption = (string) $this->option('service-photo-map');
        if ($mapFileOption !== '') {
            $mapFilePath = base_path($mapFileOption);
            if (!is_file($mapFilePath)) {
                throw new \RuntimeException('No existe el archivo service-photo-map: ' . $mapFilePath);
            }

            $decoded = json_decode((string) file_get_contents($mapFilePath), true, 512, JSON_THROW_ON_ERROR);
            foreach (($decoded['services'] ?? $decoded) as $serviceId => $photoPath) {
                $serviceId = strtoupper(trim((string) $serviceId));
                $photoPath = trim((string) $photoPath);
                if ($serviceId !== '' && $photoPath !== '') {
                    $map[$serviceId] = $this->normalizeRelativePublicPath($photoPath);
                }
            }
        }

        return $map;
    }

    private function generateComboImage(array $serviceIds, string $productCode, int $monthCount, int $deviceCount): ?string
    {
        if (!function_exists('imagecreatetruecolor')) {
            $this->warn('GD no está disponible, no se pudo generar imagen automática de combo.');
            return null;
        }

        $sourceRelativePaths = collect($serviceIds)
            ->map(fn (string $serviceId) => $this->servicePhotoMap[$serviceId] ?? null)
            ->filter()
            ->unique()
            ->values();

        if ($sourceRelativePaths->isEmpty()) {
            return null;
        }

        $targetDirAbsolute = base_path((string) $this->option('combo-image-dir'));
        if (!File::isDirectory($targetDirAbsolute)) {
            File::makeDirectory($targetDirAbsolute, 0755, true);
        }

        $targetFilename = strtolower($productCode) . '.jpg';
        $targetAbsolute = rtrim($targetDirAbsolute, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $targetFilename;

        $canvasSize = 1080;
        $canvas = imagecreatetruecolor($canvasSize, $canvasSize);
        if (!$canvas) {
            return null;
        }

        $bg = imagecolorallocate($canvas, 18, 22, 36);
        imagefilledrectangle($canvas, 0, 0, $canvasSize, $canvasSize, $bg);

        $count = min($sourceRelativePaths->count(), 4);
        $tile = $count === 1 ? 980 : 500;
        $padding = $count === 1 ? 50 : 40;

        $positions = $count === 1
            ? [[50, 50]]
            : [[40, 40], [540, 40], [40, 540], [540, 540]];

        for ($i = 0; $i < $count; $i++) {
            $relative = (string) $sourceRelativePaths[$i];
            $absolute = $this->resolvePublicPath($relative);
            if (!$absolute || !is_file($absolute)) {
                continue;
            }

            $raw = @file_get_contents($absolute);
            if ($raw === false) {
                continue;
            }

            $src = @imagecreatefromstring($raw);
            if (!$src) {
                continue;
            }

            $srcW = imagesx($src);
            $srcH = imagesy($src);
            [$x, $y] = $positions[$i];

            imagecopyresampled($canvas, $src, $x, $y, 0, 0, $tile, $tile, $srcW, $srcH);
            imagedestroy($src);
        }

        $textColor = imagecolorallocate($canvas, 240, 240, 240);
        imagestring($canvas, 5, $padding, $canvasSize - 80, 'Combo ' . implode(' + ', $serviceIds), $textColor);
        imagestring($canvas, 4, $padding, $canvasSize - 55, $monthCount . 'M ' . $deviceCount . 'D', $textColor);

        imagejpeg($canvas, $targetAbsolute, 88);
        imagedestroy($canvas);

        $publicBase = base_path('public');
        $relativeToPublic = str_replace('\\', '/', ltrim(str_replace($publicBase, '', $targetAbsolute), '\\/'));

        return $relativeToPublic;
    }

    private function normalizeRelativePublicPath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        $path = preg_replace('#^public/#', '', $path) ?? $path;
        $path = preg_replace('#^/+#', '', $path) ?? $path;

        return $path;
    }

    private function resolvePublicPath(string $path): ?string
    {
        $relative = $this->normalizeRelativePublicPath($path);
        $absolute = base_path('public/' . $relative);

        if (is_file($absolute)) {
            return $absolute;
        }

        return null;
    }

    private function isUsablePublicFile(string $path): bool
    {
        return $this->resolvePublicPath($path) !== null;
    }

    private function parseIntList(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn (string $item) => (int) trim($item))
            ->filter(fn (int $item) => $item > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function parseStringList(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn (string $item) => strtoupper(trim($item)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
