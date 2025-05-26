<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PreferencesResource\Pages;
use App\Filament\Resources\PreferencesResource\RelationManagers;
use App\Models\Preferences;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PreferencesResource extends Resource
{
    protected static ?string $model = Preferences::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Tables\Columns\TextColumn::make('user_id')->label('User Id')->searchable(),
                Tables\Columns\TextColumn::make('preference_menu')->label('Menu')->searchable(),
                Tables\Columns\TextColumn::make('preference_price')->label('Price')->searchable(),
                Tables\Columns\TextColumn::make('preference_wifi_speed')->label('Wifi Speed')->searchable(),
                Tables\Columns\TextColumn::make('preference_distance')->label('Disance')->searchable(),
                Tables\Columns\TextColumn::make('preference_mosque')->label('Mosque')->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPreferences::route('/'),
            'create' => Pages\CreatePreferences::route('/create'),
            'edit' => Pages\EditPreferences::route('/{record}/edit'),
        ];
    }
}
