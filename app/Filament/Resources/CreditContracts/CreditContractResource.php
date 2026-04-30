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
use Filament\Forms\Components\Placeholder;
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
use Filament\Tables\Table;

class CreditContractResource extends Resource
{
    protected static ?string $model = CreditContract::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $modelLabel = 'Kredit shartnomasi';

    protected static ?string $pluralModelLabel = 'Kredit shartnomalari';

    protected static string|\UnitEnum|null $navigationGroup = 'Kredit modul';

    public static function canCreate(): bool
    {
        $role = auth()->user()?->role;

        return in_array($role, [\App\Models\User::ROLE_SUPER, \App\Models\User::ROLE_ZAMDEKAN], true);
    }

    public static function canEdit($record): bool
    {
        $role = auth()->user()?->role;

        return in_array($role, [\App\Models\User::ROLE_SUPER, \App\Models\User::ROLE_ZAMDEKAN], true);
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->isSuperAdmin() === true;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->isSuperAdmin() === true;
    }

    protected static function canPay(): bool
    {
        $role = auth()->user()?->role;

        return in_array($role, [\App\Models\User::ROLE_SUPER, \App\Models\User::ROLE_MARKETING], true);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Shartnoma')
                    ->columns(2)
                    ->components([
                        DatePicker::make('contract_date')
                            ->label('Sana')
                            ->required()
                            ->default(now())
                            ->native(false),

                        Select::make('payment_status')
                            ->label('To\'lov holati')
                            ->required()
                            ->default('pending')
                            ->native(false)
                            ->options([
                                'pending' => 'Kutilmoqda',
                                'partial' => 'Qisman to\'langan',
                                'paid' => 'To\'langan',
                            ]),
                    ])
                    ->footerActions([])
                    ->description('Shartnoma raqami avtomatik beriladi.'),

                Section::make('Ta\'lim oluvchi')
                    ->columns(6)
                    ->components([
                        TextInput::make('full_name')
                            ->label('F.I.Sh.')
                            ->placeholder('AMIROV JASURBEK QUVONDIQ O\'G\'LI')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(6),

                        Textarea::make('address')
                            ->label('Yashash manzili')
                            ->rows(2)
                            ->placeholder('Surxondaryo viloyati, Termiz shahri, ...')
                            ->columnSpan(6),

                        TextInput::make('jshshir')
                            ->label('JSHSHIR')
                            ->placeholder('14 raqam')
                            ->length(14)
                            ->numeric()
                            ->columnSpan(2),

                        TextInput::make('passport')
                            ->label('Pasport')
                            ->placeholder('AA1234567')
                            ->maxLength(30)
                            ->columnSpan(2),

                        TextInput::make('phone')
                            ->label('Telefon raqami')
                            ->tel()
                            ->placeholder('+998901234567')
                            ->maxLength(30)
                            ->columnSpan(2),

                        TextInput::make('student_code')
                            ->label('Talaba kodi')
                            ->maxLength(50)
                            ->columnSpan(2),
                    ]),

                Section::make('Ta\'lim ma\'lumotlari')
                    ->columns(6)
                    ->components([
                        Select::make('speciality_id')
                            ->label('Mutaxassislik (qidiruvdan tanlang)')
                            ->required()
                            ->native(false)
                            ->searchable()
                            ->options(fn () => Speciality::query()
                                ->where('is_active', true)
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (Speciality $s) => [$s->id => static::specialityLabel($s)])
                                ->all())
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $s = $state ? Speciality::find($state) : null;
                                $set('speciality', $s?->name);
                                $set('education_type', $s?->education_type);
                                $set('education_form', $s?->education_form);
                                $set('faculty', $s?->faculty);
                            })
                            ->helperText('Nomi, shifr, fakultet, ta\'lim turi yoki shakli boʻyicha qidiring')
                            ->columnSpan(6),

                        TextInput::make('faculty')
                            ->label('Fakultet')
                            ->disabled()
                            ->placeholder('Mutaxassislik tanlangach toʻladi')
                            ->columnSpan(6),

                        TextInput::make('education_type')
                            ->label('Ta\'lim bosqichi')
                            ->disabled()
                            ->placeholder('—')
                            ->columnSpan(2),

                        TextInput::make('education_form')
                            ->label('Ta\'lim shakli')
                            ->disabled()
                            ->placeholder('—')
                            ->columnSpan(2),

                        TextInput::make('course')
                            ->label('O\'quv kursi')
                            ->required()
                            ->placeholder('3-kurs')
                            ->maxLength(20)
                            ->columnSpan(2),

                        TextInput::make('group_name')
                            ->label('Guruh nomi')
                            ->required()
                            ->maxLength(50)
                            ->columnSpan(6),
                    ]),

                Section::make('Kredit modul')
                    ->columns(2)
                    ->components([
                        TextInput::make('credits_count')
                            ->label('Kreditlar soni')
                            ->required()
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(120)
                            ->live(onBlur: true)
                            ->helperText('1 kredit = '.number_format(CreditContract::PRICE_PER_CREDIT, 0, '.', ' ').' soʻm'),

                        Placeholder::make('total_summary')
                            ->label('Umumiy summa')
                            ->content(function (Get $get): string {
                                $credits = (int) $get('credits_count');
                                $total = $credits * CreditContract::PRICE_PER_CREDIT;
                                return number_format($total, 0, '.', ' ').' soʻm';
                            }),
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

                TextColumn::make('passport')
                    ->label('Pasport')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('jshshir')
                    ->label('JSHSHIR')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('speciality')
                    ->label('Mutaxassislik')
                    ->searchable()
                    ->wrap()
                    ->description(fn (CreditContract $r) => trim(implode(' · ', array_filter([
                        $r->education_type,
                        $r->education_form,
                    ]))) ?: null)
                    ->toggleable(),

                TextColumn::make('group_name')
                    ->label('Guruh')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('education_type')
                    ->label('Ta\'lim turi')
                    ->badge()
                    ->color('info')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('education_form')
                    ->label('Ta\'lim shakli')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Kunduzgi' => 'success',
                        'Sirtqi' => 'warning',
                        'Kechki' => 'primary',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),

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
            ->searchPlaceholder('F.I.Sh., pasport yoki JSHSHIR boʻyicha qidiring')
            ->filters([])
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

                Action::make('pay')
                    ->label('To\'lash')
                    ->icon(Heroicon::OutlinedBanknotes)
                    ->iconButton()
                    ->tooltip('To\'lov kiritish')
                    ->color('success')
                    ->visible(fn (): bool => static::canPay())
                    ->modalHeading(fn (CreditContract $record) => 'To\'lov kiritish — '.$record->contract_number)
                    ->modalSubmitActionLabel('Saqlash')
                    ->fillForm(fn (CreditContract $record): array => [
                        'paid_amount' => (int) $record->paid_amount,
                        'payment_status' => $record->payment_status,
                    ])
                    ->schema([
                        \Filament\Forms\Components\Placeholder::make('summary')
                            ->label('Umumiy summa')
                            ->content(fn (CreditContract $record): string => number_format((int) $record->total_amount, 0, '.', ' ').' so\'m'),

                        \Filament\Forms\Components\TextInput::make('paid_amount')
                            ->label('To\'langan summa (so\'m)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, CreditContract $record, Set $set): void {
                                $paid = (int) $state;
                                $total = (int) $record->total_amount;
                                $set('payment_status', match (true) {
                                    $paid <= 0 => 'pending',
                                    $paid >= $total => 'paid',
                                    default => 'partial',
                                });
                            }),

                        \Filament\Forms\Components\Select::make('payment_status')
                            ->label('To\'lov holati')
                            ->required()
                            ->native(false)
                            ->options([
                                'pending' => 'Kutilmoqda',
                                'partial' => 'Qisman to\'langan',
                                'paid' => 'To\'langan',
                            ]),
                    ])
                    ->action(function (CreditContract $record, array $data): void {
                        $record->update([
                            'paid_amount' => (int) $data['paid_amount'],
                            'payment_status' => $data['payment_status'],
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('To\'lov saqlandi')
                            ->body($record->contract_number.' shartnomasi yangilandi.')
                            ->success()
                            ->send();
                    }),

                Action::make('history')
                    ->label('Tarix')
                    ->icon(Heroicon::OutlinedClock)
                    ->iconButton()
                    ->tooltip("O'zgarishlar tarixi")
                    ->color('gray')
                    ->modalHeading(fn (CreditContract $record) => $record->contract_number." — o'zgarishlar tarixi")
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Yopish')
                    ->modalContent(fn (CreditContract $record) => view('filament.credit-contracts.history', [
                        'activities' => $record->activities()->with('causer')->latest()->get(),
                    ])),

                EditAction::make()
                    ->label('Tahrirlash')
                    ->iconButton()
                    ->tooltip('Tahrirlash')
                    ->color('primary')
                    ->visible(fn (CreditContract $record): bool => static::canEdit($record)),
                DeleteAction::make()
                    ->label('O\'chirish')
                    ->iconButton()
                    ->tooltip('O\'chirish')
                    ->visible(fn (CreditContract $record): bool => static::canDelete($record)),
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
                ->visible(fn (): bool => static::canCreate())
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

    protected static function specialityLabel(Speciality $s): string
    {
        $tags = array_filter([
            $s->education_type,
            $s->education_form,
            $s->code,
        ]);

        $suffix = $tags ? ' — '.implode(' · ', $tags) : '';
        $faculty = $s->faculty ? ' ['.$s->faculty.']' : '';

        return $s->name.$suffix.$faculty;
    }

    public static function calcTotal($credits): int
    {
        return ((int) $credits) * CreditContract::PRICE_PER_CREDIT;
    }
}
