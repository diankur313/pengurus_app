<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession as MiddlewareAuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\MenuItem;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('')
            ->login()
            ->brandName('YISC Al-Azhar')
            ->brandLogo(asset('yisclogo.png'))
            ->brandLogoHeight('5rem')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->renderHook(
                'panels::head.end',
                fn (): string => '
                <style id="custom-sidebar-style">
                    .fi-sidebar-item-label, 
                    .fi-sidebar-item a span,
                    .fi-sidebar-item span { 
                        font-size: 0.875rem !important; 
                        font-weight: 500 !important; 
                        letter-spacing: normal !important;
                        text-transform: none !important;
                    }
                    .nested-menu-content { display: none; padding-left: 1rem; border-left: 1px solid rgba(200,200,200,0.2); margin-left: 1rem; }
                    .nested-menu-content.open { display: block; }
                    .nested-arrow { transition: transform 0.2s; margin-left: auto; }
                    .nested-arrow.open { transform: rotate(180deg); }
                    
                    /* Custom Calendar Style for New Lines */
                    .fc-event-title { 
                        white-space: pre-line !important; 
                        font-size: 0.75rem !important;
                        line-height: 1.2 !important;
                        padding: 2px !important;
                    }
                    .fc-event {
                        padding: 2px !important;
                    }
                </style>
                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        setTimeout(function() {
                            const config = {
                                "Civitas": ["All Civitas", "Kartu Tanda Anggota"],
                                "Finance": ["PPAB", "Archery"],
                                "Master Data": ["Daftar Domisili", "Daftar Pekerjaan", "Daftar Fee Transaksi", "Daftar Hari Libur", "Daftar Nama Bank", "Daftar Angkatan"]
                            };

                            const sidebarItems = Array.from(document.querySelectorAll(".fi-sidebar-group-items .fi-sidebar-item"));
                            
                            Object.keys(config).forEach(parentLabelName => {
                                const parentItem = sidebarItems.find(item => item.innerText.trim() === parentLabelName);
                                if (!parentItem || parentItem.dataset.nestedProcessed) return;
                                parentItem.dataset.nestedProcessed = "true";
                                
                                const link = parentItem.querySelector("a");
                                if (link) {
                                    link.href = "javascript:void(0)";
                                    const arrow = document.createElement("span");
                                    arrow.className = "nested-arrow";
                                    arrow.innerHTML = `<svg class=\"w-5 h-5 text-gray-400\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M19 9l-7 7-7-7\"></path></svg>`;
                                    link.appendChild(arrow);
                                    
                                    const contentDiv = document.createElement("div");
                                    contentDiv.classList.add("nested-menu-content");
                                    parentItem.after(contentDiv);
                                    
                                    let hasActiveChild = false;
                                    config[parentLabelName].forEach(childLabelName => {
                                        const childItem = sidebarItems.find(item => item.innerText.trim() === childLabelName);
                                        if (childItem) {
                                            // Berikan sedikit padding untuk indentasi visual
                                            childItem.style.paddingLeft = "1.5rem";
                                            if (childItem.querySelector(".fi-active") || childItem.querySelector("[aria-current=\"page\"]") || childItem.classList.contains("fi-sidebar-item-active")) {
                                                hasActiveChild = true;
                                            }
                                            contentDiv.appendChild(childItem);
                                        }
                                    });

                                    if (hasActiveChild) {
                                        contentDiv.classList.add("open");
                                        arrow.classList.add("open");
                                    }

                                    link.addEventListener("click", function() {
                                        contentDiv.classList.toggle("open");
                                        arrow.classList.toggle("open");
                                    });
                                }
                            });
                        }, 500);
                    });
                </script>
                ',
            )
            ->navigationGroups([
                \Filament\Navigation\NavigationGroup::make()
                    ->label('Filament Shield')
                    ->collapsible(true),
                \Filament\Navigation\NavigationGroup::make()
                    ->label('Super Admin')
                    ->collapsible(true),
                \Filament\Navigation\NavigationGroup::make()
                    ->label('Pendidikan')
                    ->collapsible(true),
                \Filament\Navigation\NavigationGroup::make()
                    ->label('PR / Humas')
                    ->collapsible(true),
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Edit Profil')
                    ->url(fn (): string => \App\Filament\Pages\EditProfile::getUrl())
                    ->icon('heroicon-o-user-circle')
                    ->visible(fn (): bool => auth()->user()->can('page_EditProfile')),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                \Saade\FilamentFullCalendar\FilamentFullCalendarPlugin::make()
                    ->selectable(true),
            ]);
    }
}
