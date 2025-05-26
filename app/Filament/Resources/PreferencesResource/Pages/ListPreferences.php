<?php

namespace App\Filament\Resources\PreferencesResource\Pages;

use App\Filament\Resources\PreferencesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPreferences extends ListRecords
{
    protected static string $resource = PreferencesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
