<?php

namespace App\Filament\Resources\Specialities;

use App\Filament\Resources\Specialities\Pages\ManageSpecialities;
use App\Models\Speciality;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SpecialityResource extends Resource
{
    protected static ?string $model = Speciality::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static ?string $navigationLabel = 'Mutaxassisliklar';

    protected static ?string $modelLabel = 'Mutaxassislik';

    protected static ?string $pluralModelLabel = 'Mutaxassisliklar';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Mutaxassislik nomi')
                ->required()
                ->maxLength(255)
                ->unique(ignoreRecord: true)
                ->prefixIcon(Heroicon::OutlinedAcademicCap)
                ->columnSpanFull(),

            Toggle::make('is_active')
                ->label('Aktiv')
                ->default(true)
                ->helperText('Faqat aktiv mutaxassisliklar kredit modul yaratishda ko\'rinadi'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->striped()
            ->columns([
                TextColumn::make('name')
                    ->label('Mutaxassislik')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->wrap(),

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
                    ->toggleable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Aktivlik')
                    ->trueLabel('Faqat aktiv')
                    ->falseLabel('Faqat aktiv emas')
                    ->placeholder('Hammasi'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Tahrirlash')
                    ->iconButton()
                    ->tooltip('Tahrirlash')
                    ->color('primary')
                    ->modalWidth(Width::Large),
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
