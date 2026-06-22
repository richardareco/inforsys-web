<?php

namespace App\Filament\Resources\PresupuestoResource\Pages;

use App\Filament\Resources\PresupuestoResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreatePresupuesto extends CreateRecord
{
    protected static string $resource = PresupuestoResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $items   = $data['items'] ?? [];
        $pdscto  = (float) ($data['pdscto'] ?? 0);

        $subtotal = collect($items)->sum(fn ($i) => ($i['qty'] ?? 0) * ($i['precio'] ?? 0));
        $dscto    = round($subtotal * ($pdscto / 100), 2);
        $total    = $subtotal - $dscto;
        $ctotal   = collect($items)->sum(fn ($i) => ($i['qty'] ?? 0) * ($i['costo'] ?? 0));

        $hoy  = today()->toDateString();
        $hora = now()->format('H:i:s');

        DB::connection('delphi')->table('presupuesto1')->insert([
            'custnr'      => $data['custnr'],
            'depo'        => $data['depo'],
            'obs'         => $data['obs'] ?? null,
            'total'       => $total,
            'totalv'      => $total,
            'ctotal'      => $ctotal,
            'dscto'       => $dscto,
            'pdscto'      => $pdscto,
            'fecha'       => $hoy,
            'hora'        => $hora,
            'anulado'     => 0,
            'cerrado'     => 0,
            'enviado'     => 0,
            'tipo_moneda' => 'GS',
        ]);

        $nro    = DB::connection('delphi')->getPdo()->lastInsertId();
        $presnr = DB::connection('delphi')->table('presupuesto1')->where('nro', $nro)->value('presnr');

        foreach ($items as $item) {
            DB::connection('delphi')->table('presupuesto2')->insert([
                'presnr'  => $presnr,
                'item'    => $item['item'],
                'descr'   => $item['descr'],
                'qty'     => $item['qty'],
                'precio'  => $item['precio'],
                'costo'   => $item['costo'] ?? 0,
                'fecha'   => $hoy,
                'anulado' => 0,
                'custnr'  => $data['custnr'],
            ]);
        }

        return \App\Models\Presupuesto::on('delphi')->where('nro', $nro)->first();
    }

    protected function getRedirectUrl(): string
    {
        return PresupuestoResource::getUrl('view', ['record' => $this->record->nro]);
    }
}
