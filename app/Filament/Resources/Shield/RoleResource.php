<?php

namespace App\Filament\Resources\Shield;

use BezhanSalleh\FilamentShield\Resources\RoleResource as BaseRoleResource;
use App\Filament\Resources\Shield\RoleResource\Pages;
use BezhanSalleh\FilamentShield\Facades\FilamentShield;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Illuminate\Support\HtmlString;

class RoleResource extends BaseRoleResource
{
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'view' => Pages\ViewRole::route('/{record}'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }

    public static function getTabFormComponentForResources(): \Filament\Forms\Components\Component
    {
        return static::shield()->hasSimpleResourcePermissionView()
            ? static::getTabFormComponentForSimpleResourcePermissionsView()
            : Tab::make('resources')
                ->label(__('filament-shield::filament-shield.resources'))
                ->visible(fn (): bool => (bool) Utils::isResourceEntityEnabled())
                ->badge(static::getResourceTabBadgeCount() + count(static::getCustomPages()))
                ->schema([
                    Grid::make()
                        ->schema(static::getCustomResourceEntitiesSchema())
                        ->columns(static::shield()->getGridColumns()),
                ]);
    }

    public static function getCustomPages(): array
    {
        $pages = FilamentShield::getPages();
        // Exclude EditProfile
        return collect($pages)
            ->filter(fn ($page) => $page['class'] !== \App\Filament\Pages\EditProfile::class)
            ->toArray();
    }

    public static function getCustomResourceEntitiesSchema(): ?array
    {
        $schemas = parent::getResourceEntitiesSchema() ?? [];
        
        $pages = static::getCustomPages();

        $pageSchemas = collect($pages)
            ->map(function ($page) {
                $sectionLabel = static::shield()->hasLocalizedPermissionLabels()
                    ? FilamentShield::getLocalizedPageLabel($page['class'])
                    : $page['permission'];
                
                return Section::make($sectionLabel)
                    ->description(fn () => new HtmlString('<span style="word-break: break-word;">' . $page['class'] . '</span>'))
                    ->compact()
                    ->schema([
                        static::getCheckboxListFormComponent(
                            $page['permission'].'_page_tab', // name
                            [
                                $page['permission'] => static::shield()->hasLocalizedPermissionLabels()
                                    ? FilamentShield::getLocalizedPageLabel($page['class'])
                                    : $page['permission'],
                            ], // options
                            false, // searchable
                            static::shield()->getResourceCheckboxListColumns(), // columns
                            static::shield()->getResourceCheckboxListColumnSpan() // columnSpan
                        ),
                    ])
                    ->columnSpan(static::shield()->getSectionColumnSpan())
                    ->collapsible();
            })
            ->toArray();

        return array_merge(array_values($schemas), array_values($pageSchemas));
    }

    public static function getTabFormComponentForPage(): \Filament\Forms\Components\Component
    {
        // Only keep EditProfile
        $options = collect(FilamentShield::getPages())
            ->filter(fn ($page) => $page['class'] === \App\Filament\Pages\EditProfile::class)
            ->flatMap(fn ($page) => [
                $page['permission'] => static::shield()->hasLocalizedPermissionLabels()
                    ? FilamentShield::getLocalizedPageLabel($page['class'])
                    : $page['permission'],
            ])
            ->toArray();
            
        $count = count($options);

        return Tab::make('pages')
            ->label(__('filament-shield::filament-shield.pages'))
            ->visible(fn (): bool => (bool) Utils::isPageEntityEnabled() && $count > 0)
            ->badge($count)
            ->schema([
                static::getCheckboxListFormComponent(
                    'pages_tab', // name
                    $options // options
                ),
            ]);
    }
}
