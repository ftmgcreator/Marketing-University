<?php

namespace App\Filament\Resources\Reports;

use App\Filament\Resources\Reports\Pages\ManageReports;
use App\Models\Report;
use App\Services\TelegramReporter;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static ?string $navigationLabel = 'Hisobotlar';

    protected static ?string $modelLabel = 'Hisobot';

    protected static ?string $pluralModelLabel = 'Hisobotlar';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('report_date', 'desc')
            ->columns([
                TextColumn::make('report_date')
                    ->label('Sana')
                    ->date('d.m.Y')
                    ->sortable(),

                TextColumn::make('departments_count')
                    ->label('Kafedralar')
                    ->counts('departments')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                TextColumn::make('groups_count')
                    ->label('Guruhlar')
                    ->counts('groups')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                TextColumn::make('students_count')
                    ->label('Talabalar')
                    ->counts('students')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),

                IconColumn::make('is_active')
                    ->label('Aktiv')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('created_at')
                    ->label('Yuklangan')
                    ->dateTime('d.m.Y H:i')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('telegram')
                    ->label('Telegramga yuborish')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->iconButton()
                    ->tooltip('Telegramga yuborish')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Telegramga yuborilsinmi?')
                    ->modalDescription('Har fakultet uchun alohida Excel tahliliy fayl tayyorlanadi va sozlangan Telegram chatlariga yuboriladi.')
                    ->action(function (Report $record): void {
                        try {
                            $reporter = app(TelegramReporter::class);
                            $stats = $reporter->sendReport($record);

                            $body = "Yuborildi: {$stats['sent']} · O'tkazib yuborildi: {$stats['skipped']} · Xato: {$stats['failed']}";
                            if (! empty($stats['errors'])) {
                                $body .= "\nXatolar:\n - ".implode("\n - ", $stats['errors']);
                            }

                            Notification::make()
                                ->title($stats['failed'] > 0 ? 'Qisman muvaffaqiyatli' : 'Muvaffaqiyatli yuborildi')
                                ->body($body)
                                ->color($stats['failed'] > 0 ? 'warning' : 'success')
                                ->persistent()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Yuborishda xatolik')
                                ->body($e->getMessage())
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),

                Action::make('activate')
                    ->label('Aktivlashtirish')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->iconButton()
                    ->tooltip('Aktivlashtirish')
                    ->color('success')
                    ->visible(fn (Report $record) => ! $record->is_active)
                    ->requiresConfirmation()
                    ->action(function (Report $record): void {
                        Report::where('id', '!=', $record->id)->update(['is_active' => false]);
                        $record->update(['is_active' => true]);
                    }),

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
            'index' => ManageReports::route('/'),
        ];
    }
}
