<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProviderServiceAreaResource\Pages;
use App\Filament\Resources\ProviderServiceAreaResource\RelationManagers;
use App\Models\ProviderServiceArea;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class ProviderServiceAreaResource extends Resource
{
    protected static ?string $model = ProviderServiceArea::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('provider_company_id')
                    ->relationship('providerCompany', 'display_name')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('province')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('municipality')
                    ->maxLength(255),
                Forms\Components\TextInput::make('suburb')
                    ->maxLength(255),
                Forms\Components\TextInput::make('center_lat')
                    ->numeric(),
                Forms\Components\TextInput::make('center_lng')
                    ->numeric(),
                Forms\Components\TextInput::make('radius_km')
                    ->numeric()
                    ->default(15),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('providerCompany.display_name')
                    ->label('Provider Company')
                    ->searchable(),
                Tables\Columns\TextColumn::make('province')
                    ->searchable(),
                Tables\Columns\TextColumn::make('municipality')
                    ->searchable(),
                Tables\Columns\TextColumn::make('suburb'),
                Tables\Columns\TextColumn::make('radius_km'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListProviderServiceAreas::route('/'),
            'create' => Pages\CreateProviderServiceArea::route('/create'),
            'edit' => Pages\EditProviderServiceArea::route('/{record}/edit'),
        ];
    }
}
