<?php

namespace App\Filament\Resources\Admissions;

use App\Filament\Resources\Admissions\Pages\CreateAdmission;
use App\Filament\Resources\Admissions\Pages\EditAdmission;
use App\Filament\Resources\Admissions\Pages\ListAdmissions;
use App\Models\Admission;
use App\Models\Speciality;
use BackedEnum;
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
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AdmissionResource extends Resource
{
    protected static ?string $model = Admission::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;

    protected static ?string $modelLabel = 'Talaba';

    protected static ?string $pluralModelLabel = 'Talabalar';

    protected static string|\UnitEnum|null $navigationGroup = 'Qabul';

    public static function canAccess(): bool
    {
        $role = auth()->user()?->role;

        return in_array($role, [
            \App\Models\User::ROLE_SUPER,
            \App\Models\User::ROLE_ZAMDEKAN,
            \App\Models\User::ROLE_MARKETING,
        ], true);
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->isSuperAdmin() === true;
    }

    public static function canDeleteAny(): bool
    {
        return auth()->user()?->isSuperAdmin() === true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Shaxsiy ma\'lumotlar')
                    ->columns(6)
                    ->components([
                        TextInput::make('full_name')
                            ->label('F.I.SH.')
                            ->placeholder('Jumanazarova Zarina Rashid qizi')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(6),

                        TextInput::make('jshshir')
                            ->label('JSHSHIR')
                            ->placeholder('14 raqam')
                            ->length(14)
                            ->numeric()
                            ->columnSpan(2),

                        TextInput::make('passport')
                            ->label('Pasport seriyasi')
                            ->placeholder('AC2275207')
                            ->maxLength(30)
                            ->columnSpan(2),

                        TextInput::make('phone')
                            ->label('Telefon raqam')
                            ->tel()
                            ->placeholder('887237303')
                            ->maxLength(30)
                            ->columnSpan(2),

                        TextInput::make('phone2')
                            ->label('Tel nomer 2')
                            ->tel()
                            ->maxLength(30)
                            ->columnSpan(2),

                        TextInput::make('region')
                            ->label('Yashash hududi')
                            ->placeholder('Qumqo\'rg\'on tumani')
                            ->maxLength(255)
                            ->columnSpan(4),

                        Textarea::make('address')
                            ->label('To\'liq manzil')
                            ->rows(2)
                            ->placeholder('Surxondaryo viloyati, ...')
                            ->columnSpan(6),
                    ]),

                Section::make('Ta\'lim ma\'lumotlari')
                    ->columns(6)
                    ->components([
                        Select::make('speciality_id')
                            ->label('Mutaxassislik (qidiruvdan tanlang — ixtiyoriy)')
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
                                if ($s) {
                                    $set('speciality_code', $s->code);
                                    $set('faculty', $s->faculty);
                                    $set('education_type', $s->education_type);
                                    $set('education_form', $s->education_form);
                                    $set('contract_amount', $s->contract_amount ?: null);
                                }
                            })
                            ->helperText('Tanlasangiz quyidagi maydonlar (jumladan shartnoma summasi) avtomatik to\'ladi.')
                            ->columnSpan(6),

                        TextInput::make('speciality_code')
                            ->label('Mutaxassislik kodi')
                            ->placeholder('60230100-3')
                            ->maxLength(50)
                            ->columnSpan(2),

                        TextInput::make('faculty')
                            ->label('Fakultet')
                            ->placeholder('Pedagogika')
                            ->maxLength(255)
                            ->columnSpan(4),

                        Select::make('education_type')
                            ->label('Ta\'lim turi')
                            ->options([
                                'Bakalavr' => 'Bakalavr',
                                'Magistr' => 'Magistr',
                                'Ordinatura' => 'Ordinatura',
                            ])
                            ->native(false)
                            ->placeholder('Tanlang')
                            ->columnSpan(2),

                        Select::make('education_form')
                            ->label('Ta\'lim shakli')
                            ->options([
                                'Kunduzgi' => 'Kunduzgi',
                                'Sirtqi' => 'Sirtqi',
                                'Kechki' => 'Kechki',
                            ])
                            ->native(false)
                            ->placeholder('Tanlang')
                            ->columnSpan(2),

                        TextInput::make('course')
                            ->label('Kursi')
                            ->placeholder('1 kurs')
                            ->maxLength(20)
                            ->columnSpan(2),

                        TextInput::make('contract_amount')
                            ->label('Shartnoma summasi (so\'m)')
                            ->numeric()
                            ->minValue(0)
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('Mutaxassislik tanlangach avtomatik to\'ladi')
                            ->helperText('Qiymat mutaxassislik kartasidan olinadi — qo\'lda kiritilmaydi.')
                            ->columnSpan(6),
                    ]),

                Section::make('Qabul holati')
                    ->columns(2)
                    ->components([
                        DatePicker::make('admission_date')
                            ->label('Sana')
                            ->default(now())
                            ->native(false),

                        Select::make('status')
                            ->label('Qabul statusi')
                            ->required()
                            ->default(Admission::STATUS_PENDING)
                            ->native(false)
                            ->options(Admission::STATUSES),

                        Textarea::make('notes')
                            ->label('IZOH')
                            ->rows(3)
                            ->placeholder('4 000 000 to\'ladi. 02.07.2025 sanada.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('full_name')
                    ->label('F.I.SH.')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->wrap(),

                TextColumn::make('jshshir')
                    ->label('JSHSHIR')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                TextColumn::make('passport')
                    ->label('Pasport')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('phone2')
                    ->label('Tel 2')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('speciality_code')
                    ->label('Mutaxassislik kodi')
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('faculty')
                    ->label('Fakultet')
                    ->searchable()
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('speciality.name')
                    ->label('Mutaxassislik')
                    ->searchable()
                    ->wrap()
                    ->placeholder('—')
                    ->toggleable(),

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
                    ->toggleable(),

                TextColumn::make('course')
                    ->label('Kursi')
                    ->badge()
                    ->color('info')
                    ->toggleable(),

                TextColumn::make('region')
                    ->label('Yashash hududi')
                    ->searchable()
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('contract_amount')
                    ->label('Shartnoma summasi')
                    ->money('UZS', 0, locale: 'uz')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('admission_date')
                    ->label('Sana')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Qabul statusi')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Admission::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        Admission::STATUS_APPROVED => 'success',
                        Admission::STATUS_REJECTED => 'danger',
                        default => 'warning',
                    }),
            ])
            ->searchPlaceholder('F.I.SH., JSHSHIR, pasport, telefon, fakultet, kod boʻyicha qidiring')
            ->filters([
                SelectFilter::make('status')
                    ->label('Qabul statusi')
                    ->options(Admission::STATUSES),

                SelectFilter::make('speciality_id')
                    ->label('Mutaxassislik')
                    ->relationship('speciality', 'name')
                    ->searchable(),

                SelectFilter::make('faculty')
                    ->label('Fakultet')
                    ->options(fn () => Admission::query()
                        ->whereNotNull('faculty')
                        ->distinct()
                        ->orderBy('faculty')
                        ->pluck('faculty', 'faculty')
                        ->all())
                    ->searchable(),

                SelectFilter::make('education_type')
                    ->label('Ta\'lim turi')
                    ->options([
                        'Bakalavr' => 'Bakalavr',
                        'Magistr' => 'Magistr',
                        'Ordinatura' => 'Ordinatura',
                    ]),

                SelectFilter::make('education_form')
                    ->label('Ta\'lim shakli')
                    ->options([
                        'Kunduzgi' => 'Kunduzgi',
                        'Sirtqi' => 'Sirtqi',
                        'Kechki' => 'Kechki',
                    ]),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Tahrirlash')
                    ->iconButton()
                    ->tooltip('Tahrirlash')
                    ->color('primary')
                    ->modalWidth(Width::FiveExtraLarge),
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
            'index' => ListAdmissions::route('/'),
            'create' => CreateAdmission::route('/create'),
            'edit' => EditAdmission::route('/{record}/edit'),
        ];
    }

    public static function getNavigationItems(): array
    {
        $base = static::getRouteBaseName();

        return [
            NavigationItem::make('Yangi talaba qo\'shish')
                ->group('Qabul')
                ->icon(Heroicon::OutlinedPlusCircle)
                ->url(static::getUrl('create'))
                ->visible(fn (): bool => static::canCreate())
                ->isActiveWhen(fn () => request()->routeIs("{$base}.create"))
                ->sort(1),

            NavigationItem::make('Talabalarning ro\'yxati')
                ->group('Qabul')
                ->icon(Heroicon::OutlinedUsers)
                ->url(static::getUrl('index'))
                ->isActiveWhen(fn () => request()->routeIs("{$base}.index") || request()->routeIs("{$base}.edit"))
                ->sort(2),
        ];
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
}
