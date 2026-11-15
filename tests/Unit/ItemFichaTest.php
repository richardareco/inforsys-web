<?php

namespace Tests\Unit;

use App\Models\ItemFicha;
use PHPUnit\Framework\TestCase;

/** La tabla items_ficha es latin1: lo que no entra se pasa a su equivalente simple o se descarta. */
class ItemFichaTest extends TestCase
{
    public function test_comillas_rayas_y_puntos_suspensivos_pasan_a_su_equivalente_simple(): void
    {
        $this->assertSame('"Hola" - \'mundo\'...', ItemFicha::paraLatin1('“Hola” — ‘mundo’…'));
        $this->assertSame('a - b', ItemFicha::paraLatin1('a – b'));
    }

    public function test_los_acentos_y_la_enie_se_conservan(): void
    {
        $this->assertSame('Crema hidratante con vitamina E, ñandú, café', ItemFicha::paraLatin1('Crema hidratante con vitamina E, ñandú, café'));
    }

    public function test_los_emojis_y_otros_caracteres_fuera_de_latin1_se_descartan(): void
    {
        $this->assertSame('Linda piel', ItemFicha::paraLatin1('Linda piel 😀✨'));
    }

    public function test_texto_vacio_o_solo_espacios_queda_en_null(): void
    {
        $this->assertNull(ItemFicha::paraLatin1(''));
        $this->assertNull(ItemFicha::paraLatin1("  \n "));
        $this->assertNull(ItemFicha::paraLatin1('😀'));
        $this->assertNull(ItemFicha::paraLatin1(null));
    }

    public function test_al_asignar_un_campo_de_texto_se_limpia_solo(): void
    {
        $f = new ItemFicha;
        $f->marca = '“L’Oréal” 😀';
        $f->beneficios = '   ';
        $f->destacado = 1;

        $this->assertSame('"L\'Oréal"', $f->marca);
        $this->assertNull($f->beneficios);
        $this->assertTrue($f->destacado);
    }

    public function test_los_tonos_se_guardan_como_json_y_vuelven_como_lista(): void
    {
        $f = new ItemFicha;
        $f->tonos = [['nombre' => 'Nude', 'color' => '#d8a48f']];

        $this->assertSame('[{"nombre":"Nude","color":"#d8a48f"}]', $f->getAttributes()['tonos']);
        $this->assertSame([['nombre' => 'Nude', 'color' => '#d8a48f']], $f->tonos);
    }
}
