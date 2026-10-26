<?php

namespace App\Console\Commands;

use App\Models\Empresa;
use App\Models\WaAgenteConfig;
use App\Services\WhatsApp\CatalogoService;
use Illuminate\Console\Command;

class WaProbarCatalogo extends Command
{
    protected $signature   = 'wa:probar {empresa : ID de la empresa} {texto : texto a buscar}';
    protected $description = 'Prueba la búsqueda de productos que usa el agente de WhatsApp (solo lectura)';

    public function handle(): int
    {
        $empresa = Empresa::find($this->argument('empresa'));
        if (!$empresa) {
            $this->error('Empresa no encontrada.');
            return self::FAILURE;
        }

        $config    = WaAgenteConfig::where('empresa_id', $empresa->id)->first();
        $catalogo  = new CatalogoService($empresa, $config);
        $productos = $catalogo->buscar($this->argument('texto'), (int) ($config?->max_productos ?: 5));

        if (!$productos) {
            $this->warn('Sin resultados.');
            return self::SUCCESS;
        }

        $this->table(
            ['Código', 'Nombre', 'Precio', 'Mayorista', 'Stock', 'Foto'],
            array_map(fn ($p) => [$p['codigo'], $p['nombre'], $p['precio'], $p['precio_mayorista'], $p['stock'], $p['tiene_foto'] ? 'sí' : 'no'], $productos)
        );

        return self::SUCCESS;
    }
}
