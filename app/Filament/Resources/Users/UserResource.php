<?php

namespace App\Filament\Resources\Users;

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Foydalanuvchilar';

    protected static ?string $modelLabel = 'Foydalanuvchi';

    protected static ?string $pluralModelLabel = 'Foydalanuvchilar';

    protected static ?int $navigationSort = 100;

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
                    ->label('Ismi')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(6),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true)
                    ->columnSpan(6),

                Select::make('role')
                    ->label('Rol')
                    ->required()
                    ->native(false)
                    ->options(static::availableRoleOptions())
                    ->default(User::ROLE_MARKETING)
                    ->columnSpan(6),

                TextInput::make('password')
                    ->label('Parol')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->minLength(6)
                    ->maxLength(255)
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->helperText(fn (string $operation): string => $operation === 'edit'
                        ? 'Boʻsh qoldirilsa, eski parol saqlanadi.'
                        : 'Kamida 6 belgi.')
                    ->columnSpan(6),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->where('id', '!=', auth()->id()))
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->columns([
                TextColumn::make('name')
                    ->label('Ismi')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                TextColumn::make('role')
                    ->label('Rol')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => User::ROLE_LABELS[$state] ?? ($state ?? '—'))
                    ->color(fn (?string $state): string => match ($state) {
                        User::ROLE_SUPER => 'danger',
                        User::ROLE_ADMIN => 'warning',
                        User::ROLE_MARKETING => 'success',
                        User::ROLE_ZAMDEKAN => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('created_at')
                    ->label('Yaratilgan')
                    ->dateTime('d.m.Y H:i')
                    ->since()
                    ->sortable()
                    ->color('gray')
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Rol')
                    ->options(User::ROLE_LABELS),
            ])
            ->recordActions([
                Action::make('change_password')
                    ->label('Parolni oʻzgartirish')
                    ->icon(Heroicon::OutlinedKey)
                    ->iconButton()
                    ->tooltip('Parolni oʻzgartirish')
                    ->color('warning')
                    ->modalHeading(fn (User $record) => $record->name.' uchun yangi parol')
                    ->modalSubmitActionLabel('Saqlash')
                    ->schema([
                        TextInput::make('password')
                            ->label('Yangi parol')
                            ->password()
                            ->revealable()
                            ->required()
                            ->minLength(6)
                            ->maxLength(255),

                        TextInput::make('password_confirmation')
                            ->label('Parolni tasdiqlang')
                            ->password()
                            ->revealable()
                            ->required()
                            ->same('password'),
                    ])
                    ->action(function (User $record, array $data): void {
                        $record->update(['password' => Hash::make($data['password'])]);

                        Notification::make()
                            ->title('Parol oʻzgartirildi')
                            ->body($record->email.' uchun yangi parol oʻrnatildi.')
                            ->success()
                            ->send();
                    }),

                EditAction::make()
                    ->label('Tahrirlash')
                    ->iconButton()
                    ->tooltip('Tahrirlash')
                    ->color('primary')
                    ->modalWidth(Width::TwoExtraLarge),

                DeleteAction::make()
                    ->label('Oʻchirish')
                    ->iconButton()
                    ->tooltip('Oʻchirish')
                    ->modalHeading('Foydalanuvchini oʻchirish')
                    ->modalDescription(fn (User $record) => $record->email.' foydalanuvchisini oʻchirmoqchimisiz?')
                    ->visible(fn (User $record): bool => ! $record->isSuperAdmin()),
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
            'index' => ManageUsers::route('/'),
        ];
    }

    protected static function availableRoleOptions(): array
    {
        return [
            User::ROLE_ADMIN => User::ROLE_LABELS[User::ROLE_ADMIN],
            User::ROLE_MARKETING => User::ROLE_LABELS[User::ROLE_MARKETING],
            User::ROLE_ZAMDEKAN => User::ROLE_LABELS[User::ROLE_ZAMDEKAN],
        ];
    }
}
