<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CafeResource\Pages;
use App\Filament\Resources\CafeResource\RelationManagers;
use App\Models\Cafe;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CafeResource extends Resource
{
    protected static ?string $model = Cafe::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
                TextInput::make('name')
                ->required()
                ->maxLength(255),
                TextInput::make('latitude')
                    ->required()
                    ->maxLength(255),
                TextInput::make('longitude')
                    ->required()
                    ->maxLength(255),
                TextInput::make('menu')
                    ->required()
                    ->maxLength(255),
                TextInput::make('price')
                    ->required()
                    ->maxLength(255),
                TextInput::make('wifi_speed')
                    ->required()
                    ->maxLength(255),
                TextInput::make('mosque')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
                Tables\Columns\TextColumn::make('name')->label('Name ')->searchable(),
                Tables\Columns\TextColumn::make('menu')->label('Menu')->searchable(),
                Tables\Columns\TextColumn::make('price')->label('Price')->searchable(),
                Tables\Columns\TextColumn::make('wifi_speed')->label('Wifi Speed')->searchable(),
                Tables\Columns\TextColumn::make('mosque')->label('Mosque')->searchable(),
                Tables\Columns\TextColumn::make('latitude')->label('Latitude')->searchable(),
                Tables\Columns\TextColumn::make('longitude')->label('Longitude')->searchable(),
                Tables\Columns\TextColumn::make('maps')->label('Maps')->searchable(),
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
            'index' => Pages\ListCafes::route('/'),
            'create' => Pages\CreateCafe::route('/create'),
            'edit' => Pages\EditCafe::route('/{record}/edit'),
        ];
    }
}
