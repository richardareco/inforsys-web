<?php

namespace App\Filament\Pages\Informes;

use App\Traits\CheckUserAcceso;
use Filament\Pages\Page;

/**
 * Base de las páginas del módulo Informes.
 * Cada informe define su título, ícono y la lista de contenidos previstos;
 * a medida que se desarrolle, la página concreta reemplaza la vista base.
 */
abstract class InformeBasePage extends Page
{
    use CheckUserAcceso;

    protected static ?string $navigationGroup = 'Informes';
    protected static string  $view            = 'filament.pages.informes.base';

    /** Color de acento del informe (hex). */
    protected static string $acento = '#4f46e5';

    abstract public function getDescripcion(): string;

    /** @return array<int, string> Contenidos previstos para este informe. */
    abstract public function getContenidos(): array;

    public function getIcono(): string
    {
        return static::$navigationIcon ?? 'heroicon-o-document-chart-bar';
    }

    public function getAcento(): string
    {
        return static::$acento;
    }

    // El título y la descripción se muestran en la tarjeta de la vista
    public function getHeading(): string
    {
        return '';
    }

    public function getSubheading(): ?string
    {
        return null;
    }

    public static function canAccess(): bool
    {
        return self::userHasAcceso('reportes');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
