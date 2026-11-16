<?php

namespace App\Services\Caja;

use RuntimeException;

/** El cobro no se puede registrar (importes que no cubren, venta ya cobrada...). El mensaje es apto para mostrar al cajero. */
class CobroInvalido extends RuntimeException
{
}
