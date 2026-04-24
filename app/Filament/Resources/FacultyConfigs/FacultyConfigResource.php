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
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
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
                ->helperText('Aktiv hisobotdagi fakultetlar ro\'yxatidan tanlang'),

            TextInput::make('telegram_chat_id')
                ->label('Telegram chat ID')
                ->required()
                ->placeholder('-1001234567890 (guruh) yoki 123456789 (shaxs)')
                ->helperText('Botni guruhga qo\'shing va chat ID-ni oling. Shaxsiy chat uchun foydalanuvchi botga /start yuborishi kerak.'),

            Toggle::make('is_active')
                ->label('Aktiv')
                ->default(true)
                ->helperText('O\'chiq bo\'lsa Telegramga yuborilmaydi'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Fakultet')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                TextColumn::make('telegram_chat_id')
                    ->label('Telegram chat ID')
                    ->copyable()
                    ->fontFamily('mono'),

                IconColumn::make('is_active')
                    ->label('Aktiv')
                    ->boolean(),

                TextColumn::make('updated_at')
                    ->label('Yangilangan')
                    ->dateTime('d.m.Y H:i')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()->label('Tahrirlash'),
                DeleteAction::make()->label('O\'chirish'),
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
