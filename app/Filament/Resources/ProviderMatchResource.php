<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProviderMatchResource\Pages;
use App\Filament\Resources\ProviderMatchResource\RelationManagers;
use App\Models\ProviderMatch;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class ProviderMatchResource extends Resource
{
    protected static ?string $model = ProviderMatch::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('service_request_id')
                    ->relationship('serviceRequest', 'title')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('provider_company_id')
                    ->relationship('providerCompany', 'display_name')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('match_score')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('distance_km')
                    ->numeric(),
                Forms\Components\Select::make('status')
                    ->options([
                        'notified' => 'Notified',
                        'viewed' => 'Viewed',
                        'responded' => 'Responded',
                        'declined' => 'Declined',
                        'expired' => 'Expired',
                    ])
                    ->default('notified')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('serviceRequest.title')
                    ->label('Service Request')
                    ->searchable(),
                Tables\Columns\TextColumn::make('providerCompany.display_name')
                    ->label('Provider Company')
                    ->searchable(),
                Tables\Columns\TextColumn::make('match_score'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'notified' => 'Notified',
                        'viewed' => 'Viewed',
                        'responded' => 'Responded',
                        'declined' => 'Declined',
                        'expired' => 'Expired',
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
            'index' => Pages\ListProviderMatches::route('/'),
            'create' => Pages\CreateProviderMatch::route('/create'),
            'edit' => Pages\EditProviderMatch::route('/{record}/edit'),
        ];
    }
}
