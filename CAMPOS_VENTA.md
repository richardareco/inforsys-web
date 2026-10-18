# Campos que se modifican al generar una VENTA

## FLUJO ACTUAL: Con módulo de caja (Pago inmediato)

Cuando se procesa una venta con pago, se ejecutan las siguientes operaciones en BD:

### 1. **invo1** - Cabecera de Factura (INSERT)
Tabla que guarda las ventas principales.

| Campo | Valor |
|-------|-------|
| `invnr` | Auto-generado por trigger |
| `total` | Total de la venta |
| `ctotal` | Total con descuentos/impuestos |
| `flag` | '4' (indicador de venta completada) |
| `fecha` | Fecha del día (YYYY-MM-DD) |
| `hora` | Hora actual (HH:MM:SS) |
| `pernr` | ID del cajero/vendedor |
| `custnr` | ID del cliente |
| `depo` | ID del depósito |
| `saldo` | **0** (venta pagada) |
| `sta_num` | '01' (estado: activo) |
| `sucursal_id` | 1 |
| `cajanr` | ID del registro en cajareg (UPDATE posterior) |

---

### 2. **invo2** - Detalles de Factura (INSERT x cantidad de items)
Tabla que guarda cada producto vendido.

| Campo | Valor |
|-------|-------|
| `invnr` | Referencia a invo1 |
| `item` | ID del producto |
| `descr` | Descripción del producto |
| `depo` | ID del depósito |
| `qty` | Cantidad vendida |
| `precio` | Precio unitario |
| `costo` | Costo unitario |
| `flag` | '3' (venta completada) |
| `fecha` | Fecha de la venta |

---

### 3. **items** - Stock General (UPDATE - DECREMENT)
Tabla de inventario general.

| Campo | Operación |
|-------|-----------|
| `stock` | **-= cantidad vendida** |

---

### 4. **itemsdepo** - Stock por Depósito (UPDATE - DECREMENT)
Tabla de inventario por depósito.

| Campo | Operación |
|-------|-----------|
| `qty` | **-= cantidad vendida** |

---

### 5. **cajareg** - Registro de Caja (INSERT)
Tabla que registra los movimientos de caja/pago.

| Campo | Valor |
|-------|-------|
| `fecha` | Fecha del pago |
| `totalus` | Total en moneda local |
| `total` | Total |
| `paygs` | Monto pagado en efectivo (GS) |
| `paytr` | Monto pagado por transferencia |
| `paytj` | Monto pagado por tarjeta |
| `ctotal` | Total con cambios |
| `sta_num` | '01' (activo) |
| `op` | 'PC' (operación: Punto de Caja) |
| `flag` | 1 (activo) |
| `tipo_moneda` | 'GS' |
| `cajanr` | Auto-generado por trigger |

---

## FLUJO PROPUESTO: Sin módulo de caja (Venta pendiente)

Cuando esté desactivado el módulo de caja, la venta debe quedar **pendiente de pago**.

### Cambios necesarios:

#### ❌ **NO se registra en cajareg**
- No hay registro de pago
- El dinero no entra a caja

#### ✅ **SÍ se registra en invo1** (con cambios)
- `invo1` se INSERT pero con:
  - `saldo` = **ctotal** (el monto adeudado, no 0)
  - `flag` = **'3'** o **'2'** (pendiente de pago, no completada)
  - Sin `cajanr` (porque no hay registro en cajareg)

#### ✅ **SÍ se registra en invo2**
- Igual que con caja

#### ✅ **SÍ se descuenta stock**
- Igual que con caja (items + itemsdepo)

#### ❌ **NO se crea registro en cajareg**
- No se registra el pago
- La deuda queda en invo1.saldo

---

## TABLA RESUMEN

| Tabla | Con Caja | Sin Caja | Cambios |
|-------|----------|----------|---------|
| **invo1** | INSERT | INSERT | `saldo=0` → `saldo=ctotal`, `flag='4'` → `flag='2 o 3'` |
| **invo2** | INSERT x items | INSERT x items | ✅ Igual |
| **items** | DECREMENT | DECREMENT | ✅ Igual |
| **itemsdepo** | DECREMENT | DECREMENT | ✅ Igual |
| **cajareg** | INSERT | ❌ NO | Se omite completamente |
| **cuentas_acobrar** | N/A | INSERT | Crear registro de deuda (opcional) |

---

## PRÓXIMAS ACCIONES

1. Modificar función `procesarVenta()` para verificar `modulo_caja_enabled`
2. Si está desactivado:
   - Cambiar `saldo` = `ctotal`
   - Cambiar `flag` según estado pendiente
   - Omitir INSERT a `cajareg`
   - Opcionalmente: registrar en `cuentas_acobrar`
3. Crear flujo de "Cobrar venta pendiente"

