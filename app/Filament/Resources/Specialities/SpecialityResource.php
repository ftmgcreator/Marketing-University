<?php

namespace App\Filament\Resources\Specialities;

use App\Filament\Resources\Specialities\Pages\ManageSpecialities;
use App\Models\Speciality;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SpecialityResource extends Resource
{
    protected static ?string $model = Speciality::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationLabel = 'Mutaxassisliklar';

    protected static ?string $modelLabel = 'Mutaxassislik';

    protected static ?string $pluralModelLabel = 'Mutaxassisliklar';

    public static function canAccess(): bool
    {
        return auth()->user()?->isSuperAdmin() === true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(12)
            ->components([
                TextInput::make('name')
                    ->label('Mutaxassislik nomi')
                    ->required()
                    ->maxLength(255)
                    ->prefixIcon(Heroicon::OutlinedAcademicCap)
                    ->columnSpan(12),

                TextInput::make('faculty')
                    ->label('Fakultet')
                    ->maxLength(255)
                    ->columnSpan(12),

                Select::make('education_type')
                    ->label('Ta\'lim turi')
                    ->options([
                        'Bakalavr' => 'Bakalavr',
                        'Magistr' => 'Magistr',
                        'Ordinatura' => 'Ordinatura',
                    ])
                    ->native(false)
                    ->placeholder('Tanlang')
                    ->searchable()
                    ->columnSpan(6),

                Select::make('education_form')
                    ->label('Ta\'lim shakli')
                    ->options([
                        'Kunduzgi' => 'Kunduzgi',
                        'Sirtqi' => 'Sirtqi',
                        'Kechki' => 'Kechki',
                    ])
                    ->native(false)
                    ->placeholder('Tanlang')
                    ->searchable()
                    ->columnSpan(6),

                TextInput::make('code')
                    ->label('Shifr')
                    ->maxLength(50)
                    ->columnSpan(4),

                TextInput::make('contract_amount')
                    ->label('Shartnoma summasi (so\'m)')
                    ->numeric()
                    ->minValue(0)
                    ->columnSpan(8),

                Toggle::make('is_active')
                    ->label('Aktiv')
                    ->default(true)
                    ->helperText('Faqat aktiv mutaxassisliklar kredit modul yaratishda ko\'rinadi')
                    ->columnSpan(12),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->striped()
            ->columns([
                TextColumn::make('code')
                    ->label('Shifr')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('name')
                    ->label('Mutaxassislik')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->wrap(),

                TextColumn::make('faculty')
                    ->label('Fakultet')
                    ->searchable()
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('education_type')
                    ->label('Ta\'lim turi')
                    ->badge()
                    ->color('info')
                    ->toggleable(),

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

                TextColumn::make('contract_amount')
                    ->label('Shartnoma summasi')
                    ->money('UZS', 0, locale: 'uz')
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Aktiv')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('updated_at')
                    ->label('Yangilangan')
                    ->dateTime('d.m.Y H:i')
                    ->since()
                    ->sortable()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Aktivlik')
                    ->trueLabel('Faqat aktiv')
                    ->falseLabel('Faqat aktiv emas')
                    ->placeholder('Hammasi'),

                SelectFilter::make('education_type')
                    ->label('Ta\'lim turi')
                    ->options(fn () => Speciality::query()
                        ->whereNotNull('education_type')
                        ->distinct()
                        ->orderBy('education_type')
                        ->pluck('education_type', 'education_type')
                        ->all()),

                SelectFilter::make('education_form')
                    ->label('Ta\'lim shakli')
                    ->options(fn () => Speciality::query()
                        ->whereNotNull('education_form')
                        ->distinct()
                        ->orderBy('education_form')
                        ->pluck('education_form', 'education_form')
                        ->all()),

                SelectFilter::make('faculty')
                    ->label('Fakultet')
                    ->options(fn () => Speciality::query()
                        ->whereNotNull('faculty')
                        ->distinct()
                        ->orderBy('faculty')
                        ->pluck('faculty', 'faculty')
                        ->all())
                    ->searchable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Tahrirlash')
                    ->iconButton()
                    ->tooltip('Tahrirlash')
                    ->color('primary')
                    ->modalWidth(Width::TwoExtraLarge),
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
            'index' => ManageSpecialities::route('/'),
        ];
    }
}
