<?php

namespace App\Filament\Resources\FacultyConfigs;

use App\Filament\Resources\FacultyConfigs\Pages\ManageFacultyConfigs;
use App\Models\FacultyConfig;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FacultyConfigResource extends Resource
{
    protected static ?string $model = FacultyConfig::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $navigationLabel = 'Fakultetlar (Telegram)';

    protected static ?string $modelLabel = 'Fakultet sozlamasi';

    protected static ?string $pluralModelLabel = 'Fakultet sozlamalari';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('name')
                ->label('Fakultet nomi')
                ->required()
                ->options(fn () => static::activeReportFacultyNames())
                ->searchable()
                ->native(false)
                ->prefixIcon(Heroicon::OutlinedAcademicCap)
                ->columnSpanFull()
                ->helperText('Aktiv hisobotdagi fakultetlar ro\'yxatidan tanlang'),

            TextInput::make('telegram_chat_id')
                ->label('Telegram chat ID')
                ->required()
                ->prefixIcon(Heroicon::OutlinedHashtag)
                ->placeholder('-1001234567890 (guruh) yoki 123456789 (shaxs)')
                ->columnSpanFull()
                ->helperText('Botni guruhga qo\'shing yoki shaxsiy chat uchun foydalanuvchi botga /start yuborishi kerak'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->striped()
            ->columns([
                TextColumn::make('name')
                    ->label('Fakultet')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->wrap(),

                TextColumn::make('telegram_chat_id')
                    ->label('Telegram chat ID')
                    ->copyable()
                    ->copyMessage('Nusxalandi')
                    ->fontFamily('mono')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—'),

                IconColumn::make('is_active')
                    ->label('Aktiv')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Yangilangan')
                    ->dateTime('d.m.Y H:i')
                    ->since()
                    ->sortable()
                    ->color('gray')
                    ->toggleable(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Tahrirlash')
                    ->iconButton()
                    ->tooltip('Tahrirlash')
                    ->color('primary')
                    ->modalWidth(Width::ExtraLarge),
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
            'index' => ManageFacultyConfigs::route('/'),
        ];
    }

    protected static function activeReportFacultyNames(): array
    {
        $report = \App\Models\Report::where('is_active', true)->orderByDesc('report_date')->first();
        if (! $report) return [];
        return $report->faculties()->orderBy('name')->pluck('name', 'name')->all();
    }
}
