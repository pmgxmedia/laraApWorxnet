<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LocationSharingSessionResource\Pages;
use App\Filament\Resources\LocationSharingSessionResource\RelationManagers;
use App\Models\LocationSharingSession;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class LocationSharingSessionResource extends Resource
{
    protected static ?string $model = LocationSharingSession::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('appointment_id')
                    ->relationship('appointment', 'id')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('sharer_user_id')
                    ->relationship('sharer', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('lat')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('lng')
                    ->numeric()
                    ->required(),
                Forms\Components\DateTimePicker::make('started_at')
                    ->required(),
                Forms\Components\DateTimePicker::make('expires_at'),
                Forms\Components\DateTimePicker::make('stopped_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('appointment_id')
                    ->label('Appointment'),
                Tables\Columns\TextColumn::make('sharer.name')
                    ->label('Sharer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('started_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime(),
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
            'index' => Pages\ListLocationSharingSessions::route('/'),
            'create' => Pages\CreateLocationSharingSession::route('/create'),
            'edit' => Pages\EditLocationSharingSession::route('/{record}/edit'),
        ];
    }
}
