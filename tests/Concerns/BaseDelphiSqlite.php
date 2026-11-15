<?php

namespace Tests\Concerns;

use App\Models\Empresa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * La base Delphi de cada cliente es MySQL externa: en los tests se reemplaza por una SQLite en memoria (nueva en cada
 * test, con nombre propio para que no se mezcle el caché de "¿existe items_ficha?") con las tablas que usa el POS.
 */
trait BaseDelphiSqlite
{
    protected Empresa $empresa;
    protected User $usuario;

    protected function prepararDelphi(bool $conFicha = true): void
    {
        config(['database.connections.delphi' => [
            'driver' => 'sqlite', 'database' => 'file:t' . uniqid() . '?mode=memory&cache=shared', 'prefix' => '', 'foreign_key_constraints' => false,
        ]]);
        DB::purge('delphi');

        // SQLite no trae CONCAT_WS (lo usa el buscador del POS)
        DB::connection('delphi')->getPdo()->sqliteCreateFunction(
            'CONCAT_WS',
            fn ($sep, ...$partes) => implode($sep, array_filter($partes, fn ($p) => $p !== null)),
            -1
        );

        $s = Schema::connection('delphi');
        $s->create('items', function ($t) {
            $t->increments('nro');
            $t->string('item', 20)->unique();
            $t->string('scode', 20)->nullable();
            $t->string('scode1', 20)->nullable();
            $t->string('descr', 100)->nullable();
            $t->string('descr1', 85)->nullable();
            $t->string('marnr', 4)->nullable();
            $t->string('grupnr', 15)->nullable();
            $t->decimal('costo', 14, 4)->nullable()->default(0);
            $t->decimal('acosto', 14, 4)->nullable()->default(0);
            $t->decimal('precio1', 14, 4)->nullable()->default(0);
            $t->decimal('precio2', 14, 4)->nullable()->default(0);
            $t->decimal('precio3', 14, 4)->nullable()->default(0);
            $t->decimal('stock', 11, 4)->default(0);
            $t->string('unit', 10)->nullable();
        });
        $s->create('itemsdepo', function ($t) {
            $t->increments('nro');
            $t->string('depo', 4);
            $t->string('item', 20);
            $t->decimal('qty', 11, 3)->default(0);
        });
        $s->create('itemsimage', function ($t) {
            $t->increments('nro');
            $t->string('item', 20);
            $t->binary('image')->nullable();
        });
        $s->create('grupo', function ($t) {
            $t->increments('nro');
            $t->string('grupnr', 15);
            $t->string('descr', 60)->nullable();
        });
        $s->create('marca', function ($t) {
            $t->increments('nro');
            $t->string('marnr', 4);
            $t->string('descr', 60)->nullable();
        });
        $s->create('deposito', function ($t) {
            $t->increments('nro');
            $t->string('deponr', 4);
            $t->string('depo_nombre', 60)->nullable();
        });
        $s->create('cliente', function ($t) {
            $t->increments('nro');
            $t->string('custnr', 20);
            $t->string('custname', 100)->nullable();
            $t->string('ruc', 20)->nullable();
            $t->string('email', 100)->nullable();
        });
        $s->create('personal', function ($t) {
            $t->increments('nro');
            $t->string('pernr', 6);
            $t->string('pername', 60)->nullable();
        });

        if ($conFicha) {
            $s->create('items_ficha', function ($t) {
                $t->increments('nro');
                $t->string('item', 20)->unique();
                $t->string('marca', 80)->nullable();
                $t->string('contenido', 40)->nullable();
                $t->text('descripcion')->nullable();
                $t->text('beneficios')->nullable();
                $t->text('modo_uso')->nullable();
                $t->text('ingredientes')->nullable();
                $t->string('tipo_piel', 100)->nullable();
                $t->text('tonos')->nullable();
                $t->text('advertencias')->nullable();
                $t->boolean('destacado')->default(0);
                $t->dateTime('mod_fecha')->nullable();
            });
        }

        $d = DB::connection('delphi');
        $d->table('deposito')->insert(['deponr' => 'D01', 'depo_nombre' => 'Principal']);
        $d->table('cliente')->insert(['custnr' => '0001', 'custname' => 'OCASIONAL']);
        $d->table('personal')->insert(['pernr' => '001', 'pername' => 'Vendedora']);
        $d->table('grupo')->insert([['grupnr' => 'MK', 'descr' => 'Maquillaje'], ['grupnr' => 'CP', 'descr' => 'Cuidado de la piel']]);
    }

    /** Empresa + usuario administrador con acceso a ventas y productos, logueado. */
    protected function prepararEmpresa(array $empresa = [], array $accesos = []): void
    {
        $this->empresa = Empresa::create(array_merge(['nombre' => 'Boutique Test', 'db_name' => 'db_test'], $empresa));
        $this->usuario = User::create([
            'name' => 'Admin', 'email' => 'admin' . uniqid() . '@test.com', 'password' => bcrypt('x'), 'empresa_id' => $this->empresa->id,
            'role' => 'administrador', 'accesos' => array_merge(['ventas' => true, 'productos' => true, 'configuraciones' => true], $accesos),
        ]);
        $this->actingAs($this->usuario);
    }

    protected function cambiarTipo(string $tipo): void
    {
        $this->empresa->update(['tipo' => $tipo]);
        $this->usuario->unsetRelation('empresa');
    }

    /** Crea un producto con su stock en el depósito D01. */
    protected function producto(string $codigo, array $attrs = [], float $stock = 10): void
    {
        $d = DB::connection('delphi');
        $d->table('items')->insert(array_merge(['item' => $codigo, 'descr' => 'PRODUCTO ' . $codigo, 'grupnr' => 'MK', 'precio3' => 10000, 'precio1' => 0, 'costo' => 4321.5], $attrs));
        $d->table('itemsdepo')->insert(['depo' => 'D01', 'item' => $codigo, 'qty' => $stock]);
    }

    protected function foto(string $codigo, string $contenido = "\xFF\xD8\xFFfoto"): int
    {
        return (int) DB::connection('delphi')->table('itemsimage')->insertGetId(['item' => $codigo, 'image' => $contenido]);
    }
}
