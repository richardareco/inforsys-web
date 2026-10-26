<?php

namespace App\Filament\Pages;

use App\Models\EcomConfig;
use App\Services\Tienda\CatalogoTienda;
use App\Support\Modulos;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/** Módulo E-commerce: configuración de la tienda online (catálogo web público). */
class TiendaOnlinePage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Tienda online';
    protected static ?string $navigationGroup = 'E-commerce';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $title           = 'Tienda online';
    protected static ?string $slug            = 'tienda-online';
    protected static string  $view            = 'filament.pages.tienda-online-page';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        $user = Auth::user();

        return $user && $user->empresa_id && Modulos::permite($user, 'ecommerce');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    private function config(): EcomConfig
    {
        $empresa = Auth::user()->empresa;

        return EcomConfig::firstOrCreate(
            ['empresa_id' => $empresa->id],
            ['slug' => EcomConfig::sugerirSlug($empresa->nombre), 'nombre' => $empresa->nombre]
        );
    }

    public function mount(): void
    {
        $c = $this->config();
        $todas = array_keys($this->categoriasDeLaBase());
        $ocultas = array_map('strval', (array) $c->categorias_ocultas);

        $this->form->fill(array_merge($c->attributesToArray(), [
            'categorias_visibles' => array_values(array_diff($todas, $ocultas)),
            'depositos'           => $c->depositos ?? [],
            'por_pagina'          => (string) ($c->por_pagina ?: 24),
        ]));
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('abrir')->label('Abrir la tienda')->icon('heroicon-o-arrow-top-right-on-square')->color('gray')
                ->url(fn () => $this->config()->urlPublica())->openUrlInNewTab(),
        ];
    }

    private function categoriasDeLaBase(): array
    {
        try {
            return (new CatalogoTienda($this->config()->load('empresa')))->todasLasCategorias();
        } catch (\Throwable) {
            return [];
        }
    }

    private function opcionesDepositos(): array
    {
        try {
            return \Illuminate\Support\Facades\DB::connection('delphi')->table('deposito')->orderBy('deponr')
                ->get(['deponr', 'depo_nombre'])
                ->mapWithKeys(fn ($d) => [(string) $d->deponr => trim($d->deponr . ' - ' . $d->depo_nombre, ' -')])->all();
        } catch (\Throwable) {
            return [];
        }
    }

    private function textoResumen(): HtmlString
    {
        try {
            $r = (new CatalogoTienda($this->config()->load('empresa')))->resumen();
        } catch (\Throwable $e) {
            return new HtmlString('<span class="text-danger-600">No se pudo leer el catálogo.</span>');
        }

        $sinFoto = $r['productos'] - $r['con_foto'];
        $html = "<b>{$r['productos']}</b> productos publicados en <b>{$r['categorias']}</b> categorías · <b>{$r['con_foto']}</b> con foto"
            . ($sinFoto > 0 ? " · <span style='color:#b45309'>{$sinFoto} sin foto</span>" : '')
            . " · <b>{$r['con_stock']}</b> con stock.<br><span style='opacity:.7;font-size:.85em'>Se publican los productos que tienen una categoría registrada. El nombre es la descripción del producto y el texto de detalle sale de “Descr. 1”.</span>";

        if ($this->config()->whatsappDigitos() === '') {
            $html .= "<div style='margin-top:.7rem;padding:.6rem .9rem;border-radius:.7rem;background:#fffbeb;border:1px solid #fde68a;color:#92400e;font-size:.9em'>"
                . '⚠️ Falta cargar el <b>WhatsApp</b> (sección Contacto): sin número, los botones “Consultar por WhatsApp” no aparecen en la tienda.</div>';
        }

        return new HtmlString($html);
    }

    public function form(Form $form): Form
    {
        return $form->statePath('data')->schema([
            Section::make('Catálogo web')
                ->description('Qué se está publicando ahora mismo')
                ->schema([
                    Placeholder::make('resumen')->label('')->content(fn () => $this->textoResumen()),
                    Toggle::make('activo')->label('Tienda activa (visible al público)')
                        ->helperText('Si la apagás, la dirección deja de mostrar el catálogo.'),
                    TextInput::make('slug')->label('Dirección de la tienda')->required()->maxLength(80)
                        ->prefix(url('/tienda') . '/')->helperText('Solo letras minúsculas, números y guiones.')
                        ->dehydrateStateUsing(fn ($state) => Str::slug((string) $state)),
                ]),

            Section::make('Identidad')->columns(2)->schema([
                TextInput::make('nombre')->label('Nombre de la tienda')->maxLength(80),
                TextInput::make('eslogan')->label('Frase destacada')->maxLength(160)->placeholder('Ej: Todo para tu belleza'),
                Textarea::make('texto_inicio')->label('Texto de presentación')->rows(3)->columnSpanFull()
                    ->helperText('Se muestra en la portada y en el pie de la tienda.'),
                FileUpload::make('logo')->label('Logo')->image()->disk('public')->directory('tienda')->maxSize(2048),
                FileUpload::make('portada')->label('Imagen de portada')->image()->disk('public')->directory('tienda')->maxSize(4096)
                    ->helperText('Se recomienda una imagen horizontal (por ejemplo 1600 × 600).'),
                ColorPicker::make('color')->label('Color de la marca'),
            ]),

            Section::make('Barra de anuncios')
                ->description('Una franja arriba de la tienda con mensajes que se desplazan de derecha a izquierda (promociones, envíos, novedades).')
                ->columns(2)->schema([
                    Toggle::make('anuncio_activo')->label('Mostrar la barra de anuncios')->live()->columnSpanFull(),
                    Textarea::make('anuncios')->label('Mensajes (uno por línea)')->rows(4)->columnSpanFull()
                        ->placeholder("🚚 Envíos a todo el país\n💳 Pagá por transferencia o efectivo\n🔥 Precios mayoristas desde 6 unidades")
                        ->visible(fn ($get) => (bool) $get('anuncio_activo'))->required(fn ($get) => (bool) $get('anuncio_activo'))
                        ->validationMessages(['required' => 'Escribí al menos un mensaje para la barra.']),
                    ColorPicker::make('anuncio_fondo')->label('Color de fondo')->helperText('Vacío: usa el color de la marca.')
                        ->visible(fn ($get) => (bool) $get('anuncio_activo')),
                    ColorPicker::make('anuncio_color_texto')->label('Color del texto')->helperText('Vacío: se elige solo para que se lea bien.')
                        ->visible(fn ($get) => (bool) $get('anuncio_activo')),
                ]),

            Section::make('Contacto')->columns(2)->schema([
                TextInput::make('whatsapp')->label('WhatsApp')->placeholder('+595 981 123456')->maxLength(30)
                    ->helperText('Los clientes te escriben desde los botones de la tienda.'),
                TextInput::make('instagram')->label('Instagram')->placeholder('@mitienda')->maxLength(120),
                TextInput::make('facebook')->label('Facebook')->placeholder('facebook.com/mitienda')->maxLength(120),
                TextInput::make('direccion')->label('Dirección del local')->maxLength(200),
                Textarea::make('horarios')->label('Horarios de atención')->rows(2)->columnSpanFull(),
            ]),

            Section::make('Qué se muestra en el catálogo')->columns(2)->schema([
                Toggle::make('mostrar_precios')->label('Mostrar precios')->helperText('Apagado: se muestra “Consultar precio”.'),
                Toggle::make('mostrar_stock')->label('Mostrar disponibilidad')->helperText('Etiqueta “Agotado” y estado “Disponible”.'),
                Toggle::make('mostrar_sin_stock')->label('Listar productos sin stock')->helperText('Apagado: solo se ven los que tienen stock.'),
                Toggle::make('mostrar_mayorista')->label('Mostrar precio mayorista')->live()
                    ->helperText('Es el precio 1 del producto, solo si es menor al precio normal.'),
                TextInput::make('mayorista_min_unidades')->label('Mayorista desde (unidades)')->numeric()->integer()->minValue(2)->maxValue(100000)
                    ->visible(fn ($get) => (bool) $get('mostrar_mayorista'))->required(fn ($get) => (bool) $get('mostrar_mayorista'))
                    ->validationMessages(['required' => 'Indicá desde cuántas unidades aplica el precio mayorista.']),
                Select::make('por_pagina')->label('Productos por página')->native(false)
                    ->options(['12' => '12', '24' => '24', '36' => '36', '48' => '48']),
                CheckboxList::make('categorias_visibles')->label('Categorías visibles')->columns(3)->columnSpanFull()
                    ->options(fn () => $this->categoriasDeLaBase())
                    ->helperText('Desmarcá las categorías que no querés mostrar en la tienda.'),
                CheckboxList::make('depositos')->label('Depósitos que cuentan para el stock')->columns(3)->columnSpanFull()
                    ->options(fn () => $this->opcionesDepositos())->helperText('Sin marcar ninguno se suma el stock de todos.'),
                TextInput::make('mensaje_whatsapp')->label('Mensaje de WhatsApp por producto')->columnSpanFull()->maxLength(255)
                    ->placeholder('Hola, quiero consultar por {producto} (Cód. {codigo}).')
                    ->helperText('Se escribe solo cuando el cliente toca “Consultar por WhatsApp”. Podés usar {producto} y {codigo}.'),
            ]),

            Section::make('Buscadores (Google)')->collapsed()->schema([
                TextInput::make('seo_titulo')->label('Título')->maxLength(120),
                TextInput::make('seo_descripcion')->label('Descripción')->maxLength(255),
            ]),
        ]);
    }

    public function save(): void
    {
        $c    = $this->config();
        $data = $this->form->getState();

        $slug = Str::slug((string) ($data['slug'] ?? ''));
        if ($slug === '' || EcomConfig::where('slug', $slug)->where('id', '!=', $c->id)->exists()) {
            Notification::make()->title('Esa dirección no está disponible')->body('Elegí otra o dejá la sugerida.')->danger()->send();

            return;
        }

        $todas   = array_map('strval', array_keys($this->categoriasDeLaBase()));
        $visibles = array_map('strval', (array) ($data['categorias_visibles'] ?? []));
        $data['categorias_ocultas'] = array_values(array_diff($todas, $visibles));
        $data['depositos'] = array_values((array) ($data['depositos'] ?? []));
        $data['por_pagina'] = (int) ($data['por_pagina'] ?? 24);
        $data['slug'] = $slug;
        if (empty($data['mostrar_mayorista'])) {
            $data['mayorista_min_unidades'] = null;
        }
        unset($data['categorias_visibles'], $data['resumen']);

        $c->update($data);

        Notification::make()->title('Tienda guardada')->body($c->activo ? 'Ya está visible en ' . $c->urlPublica() : 'Todavía está apagada: activala para que se vea.')->success()->send();
    }
}
