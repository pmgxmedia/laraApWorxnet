<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceRequestMediaResource\Pages;
use App\Filament\Resources\ServiceRequestMediaResource\RelationManagers;
use App\Models\ServiceRequestMedia;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class ServiceRequestMediaResource extends Resource
{
    protected static ?string $model = ServiceRequestMedia::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('service_request_id')
                    ->relationship('serviceRequest', 'title')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('file_path')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options([
                        'image' => 'Image',
                        'video' => 'Video',
                        'document' => 'Document',
                    ])
                    ->default('image')
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
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('file_path')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'image' => 'Image',
                        'video' => 'Video',
                        'document' => 'Document',
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
            'index' => Pages\ListServiceRequestMedia::route('/'),
            'create' => Pages\CreateServiceRequestMedia::route('/create'),
            'edit' => Pages\EditServiceRequestMedia::route('/{record}/edit'),
        ];
    }
}
