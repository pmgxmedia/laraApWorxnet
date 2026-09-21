<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProviderServiceResource\Pages;
use App\Filament\Resources\ProviderServiceResource\RelationManagers;
use App\Models\ProviderService;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class ProviderServiceResource extends Resource
{
    protected static ?string $model = ProviderService::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('provider_company_id')
                    ->relationship('providerCompany', 'display_name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('service_category_id')
                    ->relationship('serviceCategory', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Textarea::make('speciality_notes')
                    ->columnSpanFull(),
                Forms\Components\KeyValue::make('brands'),
                Forms\Components\TextInput::make('years_experience')
                    ->numeric(),
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
                Tables\Columns\TextColumn::make('serviceCategory.name')
                    ->label('Service Category')
                    ->searchable(),
                Tables\Columns\TextColumn::make('years_experience'),
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
            'index' => Pages\ListProviderServices::route('/'),
            'create' => Pages\CreateProviderService::route('/create'),
            'edit' => Pages\EditProviderService::route('/{record}/edit'),
        ];
    }
}
