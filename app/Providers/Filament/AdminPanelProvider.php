<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->brandName('TISU Marketing')
            ->favicon(asset('images/logo.png'))
            ->databaseNotifications()
            ->databaseNotificationsPolling('10s')
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString('<style>
                    /* Full-width search input in tables */
                    .fi-ta-search-field {
                        width: 100% !important;
                        max-width: none !important;
                        flex: 1 1 100% !important;
                    }
                    .fi-ta-search-field input {
                        width: 100% !important;
                    }
                    .fi-ta-header-toolbar > div:has(> .fi-ta-search-field) {
                        flex: 1 1 100% !important;
                        width: 100% !important;
                    }

                    /* Notifications panel polish */
                    .fi-no-database-notifications {
                        padding: 0;
                    }
                    .fi-no-database-notifications header.fi-no-database-notifications-header,
                    .fi-no-database-notifications .fi-modal-header {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        gap: 0.75rem;
                        padding: 1rem 1.25rem;
                        border-bottom: 1px solid rgba(148,163,184,0.18);
                        background: rgba(15,23,42,0.35);
                    }
                    .fi-no-database-notifications-header h2,
                    .fi-no-database-notifications .fi-modal-heading {
                        font-size: 1rem;
                        font-weight: 600;
                        margin: 0;
                    }
                    .fi-no-database-notifications-header-actions {
                        display: flex;
                        align-items: center;
                        gap: 0.5rem;
                    }
                    .fi-no-database-notifications-header-actions .fi-link,
                    .fi-no-database-notifications-header-actions a,
                    .fi-no-database-notifications-header-actions button[type="button"]:not(.fi-icon-btn) {
                        font-size: 0.8125rem;
                        font-weight: 500;
                        padding: 0.35rem 0.7rem;
                        border-radius: 0.5rem;
                        background: rgba(239,68,68,0.10);
                        color: rgb(248,113,113);
                        text-decoration: none;
                        border: 1px solid rgba(239,68,68,0.25);
                        transition: background 0.15s, color 0.15s;
                    }
                    .fi-no-database-notifications-header-actions .fi-link:hover,
                    .fi-no-database-notifications-header-actions a:hover,
                    .fi-no-database-notifications-header-actions button[type="button"]:not(.fi-icon-btn):hover {
                        background: rgba(239,68,68,0.18);
                        color: rgb(254,202,202);
                    }
                    .fi-no-database-notifications .fi-modal-close-btn,
                    .fi-no-database-notifications .fi-icon-btn {
                        outline: none !important;
                        box-shadow: none !important;
                        border: none !important;
                        background: transparent !important;
                        color: rgb(148,163,184) !important;
                        border-radius: 0.5rem !important;
                    }
                    .fi-no-database-notifications .fi-modal-close-btn:hover,
                    .fi-no-database-notifications .fi-icon-btn:hover {
                        background: rgba(148,163,184,0.12) !important;
                        color: rgb(241,245,249) !important;
                    }
                    .fi-no-database-notifications .fi-no-list {
                        padding: 0.5rem;
                        gap: 0.5rem;
                        display: flex;
                        flex-direction: column;
                    }
                    .fi-no-notification {
                        position: relative;
                        padding: 0.85rem 1rem;
                        border-radius: 0.65rem;
                        background: rgba(30,41,59,0.55);
                        border: 1px solid rgba(148,163,184,0.12);
                        transition: background 0.15s, border-color 0.15s;
                    }
                    .fi-no-notification:hover {
                        background: rgba(51,65,85,0.55);
                        border-color: rgba(148,163,184,0.22);
                    }
                    .fi-no-notification .fi-no-notification-title {
                        font-weight: 600;
                        font-size: 0.875rem;
                        line-height: 1.3;
                    }
                    .fi-no-notification .fi-no-notification-date {
                        font-size: 0.72rem;
                        color: rgb(148,163,184);
                        margin-top: 0.15rem;
                    }
                    .fi-no-notification .fi-no-notification-body {
                        font-size: 0.8125rem;
                        color: rgb(203,213,225);
                        line-height: 1.4;
                        margin-top: 0.35rem;
                        word-break: break-word;
                    }
                </style>'),
            );
    }
}
