<?php

namespace App\Filament\Resources\CreditContracts;

use App\Filament\Resources\CreditContracts\Pages\CreateCreditContract;
use App\Filament\Resources\CreditContracts\Pages\EditCreditContract;
use App\Filament\Resources\CreditContracts\Pages\ListCreditContracts;
use App\Models\CreditContract;
use App\Models\Speciality;
use BackedEnum;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Navigation\NavigationItem;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CreditContractResource extends Resource
{
    protected static ?string $model = CreditContract::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $modelLabel = 'Kredit shartnomasi';

    protected static ?string $pluralModelLabel = 'Kredit shartnomalari';

    protected static string|\UnitEnum|null $navigationGroup = 'Kredit modul';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Talaba ma\'lumotlari')
                    ->columns(2)
                    ->components([
                        TextInput::make('full_name')
                            ->label('F.I.Sh.')
                            ->placeholder('Karimov Anvar Bahodirovich')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Select::make('speciality')
                            ->label('Mutaxassislik')
                            ->required()
                            ->options(fn () => static::specialityOptions())
                            ->searchable()
                            ->native(false)
                            ->allowHtml(),

                        TextInput::make('group_name')
                            ->label('Guruh nomi')
                            ->required()
                            ->maxLength(50),
                    ]),

                Section::make('Hujjat ma\'lumotlari')
                    ->columns(2)
                    ->components([
                        TextInput::make('jshshir')
                            ->label('JSHSHIR')
                            ->placeholder('14 raqam')
                            ->length(14)
                            ->numeric(),

                        TextInput::make('passport')
                            ->label('Pasport (seriya va raqami)')
                            ->placeholder('AA1234567')
                            ->maxLength(30),
                    ]),

                Section::make('Kredit modul')
                    ->columns(2)
                    ->components([
                        TextInput::make('subject_name')
                            ->label('Fan nomi')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('credits_count')
                            ->label('Kreditlar soni')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(60)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Set $set) => $set('total_amount', static::calcTotal($state)))
                            ->helperText(fn (Get $get) => '1 kredit = '.number_format(CreditContract::PRICE_PER_CREDIT, 0, '.', ' ').' so\'m. Umumiy summa: '
                                .number_format(static::calcTotal($get('credits_count')), 0, '.', ' ').' so\'m'),
                    ]),

                Section::make('Shartnoma')
                    ->columns(3)
                    ->hiddenOn('create')
                    ->components([
                        TextInput::make('contract_number')
                            ->label('Shartnoma raqami')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true),

                        DatePicker::make('contract_date')
                            ->label('Sana')
                            ->required()
                            ->native(false),

                        Select::make('payment_status')
                            ->label('To\'lov holati')
                            ->required()
                            ->native(false)
                            ->options([
                                'pending' => 'Kutilmoqda',
                                'partial' => 'Qisman to\'langan',
                                'paid' => 'To\'langan',
                            ]),
                    ]),

                Section::make('Izoh')
                    ->components([
                        Textarea::make('notes')
                            ->hiddenLabel()
                            ->rows(3),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('contract_date', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('contract_number')
                    ->label('Shartnoma №')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->copyable(),

                TextColumn::make('contract_date')
                    ->label('Sana')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('full_name')
                    ->label('F.I.Sh.')
                    ->searchable()
                    ->wrap(),

                TextColumn::make('speciality')
                    ->label('Mutaxassislik')
                    ->searchable()
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('group_name')
                    ->label('Guruh')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('credits_count')
                    ->label('Kr.')
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                TextColumn::make('total_amount')
                    ->label('Summa')
                    ->money('UZS', 0, locale: 'uz')
                    ->sortable(),

                TextColumn::make('payment_status')
                    ->label('Holat')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'pending' => 'Kutilmoqda',
                        'partial' => 'Qisman',
                        'paid' => 'To\'langan',
                        default => $state,
                    })
                    ->color(fn (string $state) => match ($state) {
                        'paid' => 'success',
                        'partial' => 'warning',
                        default => 'danger',
                    }),
            ])
            ->filters([
                SelectFilter::make('payment_status')
                    ->label('To\'lov holati')
                    ->options([
                        'pending' => 'Kutilmoqda',
                        'partial' => 'Qisman to\'langan',
                        'paid' => 'To\'langan',
                    ]),
                SelectFilter::make('speciality')
                    ->label('Mutaxassislik')
                    ->options(fn () => static::specialityOptions())
                    ->searchable(),
            ])
            ->recordActions([
                Action::make('pdf')
                    ->label('PDF yuklab olish')
                    ->icon(Heroicon::OutlinedDocumentArrowDown)
                    ->iconButton()
                    ->tooltip('Shartnomani PDF qilib yuklab olish')
                    ->color('success')
                    ->action(function (CreditContract $record) {
                        $pdf = Pdf::loadView('pdf.credit_contract', ['contract' => $record])
                            ->setPaper('a4');

                        $filename = "shartnoma-{$record->contract_number}.pdf";

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            $filename,
                            ['Content-Type' => 'application/pdf']
                        );
                    }),
                EditAction::make()
                    ->label('Tahrirlash')
                    ->iconButton()
                    ->tooltip('Tahrirlash')
                    ->color('primary'),
                DeleteAction::make()
                    ->label('O\'chirish')
                    ->iconButton()
                    ->tooltip('O\'chirish'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCreditContracts::route('/'),
            'create' => CreateCreditContract::route('/create'),
            'edit' => EditCreditContract::route('/{record}/edit'),
        ];
    }

    public static function getNavigationItems(): array
    {
        $base = static::getRouteBaseName();

        return [
            NavigationItem::make('Yangi kredit modul')
                ->group('Kredit modul')
                ->icon(Heroicon::OutlinedPlusCircle)
                ->url(static::getUrl('create'))
                ->isActiveWhen(fn () => request()->routeIs("{$base}.create"))
                ->sort(1),

            NavigationItem::make('Shartnomalar')
                ->group('Kredit modul')
                ->icon(Heroicon::OutlinedDocumentText)
                ->url(static::getUrl('index'))
                ->isActiveWhen(fn () => request()->routeIs("{$base}.index") || request()->routeIs("{$base}.edit"))
                ->sort(2),
        ];
    }

    protected static function specialityOptions(): array
    {
        return Speciality::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->pluck('name', 'name')
            ->all();
    }

    public static function calcTotal($credits): int
    {
        return ((int) $credits) * CreditContract::PRICE_PER_CREDIT;
    }
}
