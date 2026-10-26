<?php

namespace App\Services\WhatsApp;

/**
 * Reconoce el mensaje que arma el carrito de la tienda web:
 *
 *   🛒 *Pedido desde la tienda web*
 *   1) 2 x LABIAL ROJO (Cód. 000123) · Gs. 45.000 c/u · Subtotal Gs. 90.000
 *
 * Solo se toman códigos y cantidades. Los precios del mensaje se IGNORAN: los importes salen siempre del sistema.
 */
class PedidoWeb
{
    private const MAX_PRODUCTOS = 30;
    private const MAX_CANTIDAD = 10000;

    /** @return array<int, array{codigo: string, cantidad: float}>|null null si el texto no es un pedido de la tienda web */
    public static function interpretar(string $texto): ?array
    {
        if (!preg_match('/pedido\s+desde\s+la\s+tienda\s+web/iu', mb_substr($texto, 0, 120))) {
            return null;
        }

        preg_match_all('/^\s*\d+\)\s*(\d{1,6}(?:[.,]\d{1,3})?)\s*[x×]\s+.+?\(\s*C[oó]d\.?\s*([^)\s]{1,40})\s*\)/miu', $texto, $m, PREG_SET_ORDER);

        $items = [];
        foreach ($m as $fila) {
            $cantidad = (float) str_replace(',', '.', $fila[1]);
            $codigo = trim($fila[2]);
            if ($cantidad <= 0 || $cantidad > self::MAX_CANTIDAD || $codigo === '') {
                continue;
            }
            $items[$codigo] = ($items[$codigo] ?? 0) + $cantidad;
            if (count($items) >= self::MAX_PRODUCTOS) {
                break;
            }
        }

        $salida = [];
        foreach ($items as $codigo => $cantidad) {
            $salida[] = ['codigo' => (string) $codigo, 'cantidad' => min($cantidad, self::MAX_CANTIDAD)];
        }

        return $salida ?: null;
    }
}
