<?php

namespace Tests\Feature;

use App\Filament\Resources\ItemResource\Pages\CreateItem;
use App\Filament\Resources\ItemResource\Pages\EditItem;
use App\Services\Productos\FotosProducto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Concerns\BaseDelphiSqlite;
use Tests\TestCase;

/** Varias fotos por producto: ver las que ya tiene, eliminar y subir más desde el CRUD de Productos. */
class FotosProductoTest extends TestCase
{
    use BaseDelphiSqlite, RefreshDatabase;

    private FotosProducto $fotos;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
        $this->prepararDelphi();
        $this->prepararEmpresa();
        $this->producto('P1', ['descr' => 'BASE FLUIDA']);
        $this->fotos = new FotosProducto;
    }

    private function cuantas(string $item = 'P1'): int
    {
        return DB::connection('delphi')->table('itemsimage')->where('item', $item)->count();
    }

    // ───────────── Servicio ─────────────

    public function test_procesar_deja_un_jpeg_cuadrado_de_600_px(): void
    {
        $original = UploadedFile::fake()->image('ancha.png', 1200, 400);

        $jpeg = $this->fotos->procesar(file_get_contents($original->getRealPath()));

        $this->assertStringStartsWith("\xFF\xD8\xFF", $jpeg);
        [$ancho, $alto, $tipo] = getimagesizefromstring($jpeg);
        $this->assertSame([600, 600, IMAGETYPE_JPEG], [$ancho, $alto, $tipo]);
    }

    public function test_procesar_rechaza_lo_que_no_es_una_imagen(): void
    {
        $this->assertNull($this->fotos->procesar('esto es un texto, no una foto'));
        $this->assertNull($this->fotos->procesar(''));
    }

    public function test_agregar_suma_fotos_sin_borrar_las_existentes(): void
    {
        $this->foto('P1');

        $r = $this->fotos->agregar('P1', [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.png')]);

        $this->assertSame(['agregadas' => 2, 'omitidas' => 0, 'invalidas' => 0], $r);
        $this->assertSame(3, $this->cuantas());
    }

    public function test_agregar_respeta_el_maximo_de_fotos(): void
    {
        for ($i = 0; $i < FotosProducto::MAXIMO - 1; $i++) {
            $this->foto('P1');
        }

        $r = $this->fotos->agregar('P1', [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg'), UploadedFile::fake()->image('c.jpg')]);

        $this->assertSame(1, $r['agregadas']);
        $this->assertSame(2, $r['omitidas']);
        $this->assertSame(FotosProducto::MAXIMO, $this->cuantas());
    }

    public function test_agregar_informa_los_archivos_que_no_son_imagenes(): void
    {
        $falso = UploadedFile::fake()->createWithContent('doc.jpg', 'no soy una imagen');

        $r = $this->fotos->agregar('P1', [$falso, UploadedFile::fake()->image('ok.jpg')]);

        $this->assertSame(['agregadas' => 1, 'omitidas' => 0, 'invalidas' => 1], $r);
    }

    public function test_listar_devuelve_las_fotos_en_orden_y_solo_del_producto(): void
    {
        $a = $this->foto('P1');
        $this->producto('P2');
        $this->foto('P2');
        $b = $this->foto('P1');

        $this->assertSame([$a, $b], $this->fotos->listar('P1'));
    }

    public function test_eliminar_borra_solo_la_foto_de_ese_producto(): void
    {
        $this->producto('P2');
        $propia = $this->foto('P1');
        $ajena = $this->foto('P2');

        $this->assertFalse($this->fotos->eliminar('P1', $ajena), 'no se puede borrar la foto de otro producto');
        $this->assertSame(1, $this->cuantas('P2'));

        $this->assertTrue($this->fotos->eliminar('P1', $propia));
        $this->assertSame(0, $this->cuantas('P1'));
        $this->assertFalse($this->fotos->eliminar('P1', $propia), 'ya no existe');
    }

    // ───────────── Formulario de Productos ─────────────

    public function test_el_formulario_muestra_las_fotos_que_ya_tiene_con_su_boton_eliminar(): void
    {
        $a = $this->foto('P1');
        $b = $this->foto('P1');

        $html = Livewire::test(EditItem::class, ['record' => 'P1'])->assertSuccessful()->html();

        $this->assertStringContainsString("/api/item-image/P1/{$a}", $html);
        $this->assertStringContainsString("/api/item-image/P1/{$b}", $html);
        $this->assertStringContainsString('Principal', $html);
        $this->assertStringContainsString("eliminarFoto({$a})", $html);
        $this->assertStringContainsString('2 de ' . FotosProducto::MAXIMO . ' fotos', $html);
    }

    public function test_sin_fotos_el_formulario_lo_dice_y_ofrece_subir(): void
    {
        Livewire::test(EditItem::class, ['record' => 'P1'])
            ->assertSee('todavía no tiene fotos')
            ->assertSee('Agregar fotos');
    }

    public function test_eliminar_una_foto_desde_el_formulario(): void
    {
        $a = $this->foto('P1');
        $b = $this->foto('P1');

        $t = Livewire::test(EditItem::class, ['record' => 'P1'])->call('eliminarFoto', $a);

        $this->assertSame([$b], $this->fotos->listar('P1'));
        $this->assertStringNotContainsString("eliminarFoto({$a})", $t->html());
    }

    public function test_no_se_puede_eliminar_desde_el_formulario_la_foto_de_otro_producto(): void
    {
        $this->producto('P2');
        $ajena = $this->foto('P2');

        Livewire::test(EditItem::class, ['record' => 'P1'])->call('eliminarFoto', $ajena);

        $this->assertSame(1, $this->cuantas('P2'));
    }

    public function test_subir_varias_fotos_al_editar_las_suma_a_las_existentes(): void
    {
        $this->foto('P1');

        Livewire::test(EditItem::class, ['record' => 'P1'])
            ->fillForm(['fotos_nuevas' => [UploadedFile::fake()->image('a.jpg', 900, 700), UploadedFile::fake()->image('b.png', 300, 300)]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(3, $this->cuantas(), 'la existente se conserva y se suman 2');
    }

    public function test_guardar_sin_elegir_fotos_no_toca_las_que_ya_tiene(): void
    {
        $a = $this->foto('P1');

        Livewire::test(EditItem::class, ['record' => 'P1'])->fillForm(['descr' => 'NUEVO NOMBRE'])->call('save')->assertHasNoFormErrors();

        $this->assertSame([$a], $this->fotos->listar('P1'));
    }

    public function test_el_formulario_no_acepta_mas_del_maximo_de_fotos_a_la_vez(): void
    {
        $archivos = array_map(fn ($i) => UploadedFile::fake()->image("f{$i}.jpg"), range(1, FotosProducto::MAXIMO + 1));

        Livewire::test(EditItem::class, ['record' => 'P1'])
            ->fillForm(['fotos_nuevas' => $archivos])
            ->call('save')
            ->assertHasFormErrors(['fotos_nuevas']);

        $this->assertSame(0, $this->cuantas());
    }

    public function test_el_formulario_rechaza_archivos_que_no_son_imagenes(): void
    {
        Livewire::test(EditItem::class, ['record' => 'P1'])
            ->fillForm(['fotos_nuevas' => [UploadedFile::fake()->create('lista.pdf', 100, 'application/pdf')]])
            ->call('save')
            ->assertHasFormErrors(['fotos_nuevas']);

        $this->assertSame(0, $this->cuantas());
    }

    public function test_al_crear_un_producto_se_pueden_subir_sus_fotos(): void
    {
        Livewire::test(CreateItem::class)
            ->fillForm(['descr' => 'CREMA NUEVA', 'precio3' => 9000, 'fotos_nuevas' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')]])
            ->call('create')
            ->assertHasNoFormErrors();

        $item = DB::connection('delphi')->table('items')->where('descr', 'CREMA NUEVA')->value('item');
        $this->assertNotNull($item);
        $this->assertSame(2, $this->cuantas($item));
    }

    public function test_no_quedan_archivos_sueltos_en_el_disco(): void
    {
        Livewire::test(EditItem::class, ['record' => 'P1'])
            ->fillForm(['fotos_nuevas' => [UploadedFile::fake()->image('a.jpg')]])
            ->call('save');

        $this->assertSame([], Storage::disk('local')->allFiles('products'));
        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}
