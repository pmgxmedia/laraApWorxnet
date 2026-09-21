<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionCodeResource\Pages;
use App\Filament\Resources\PromotionCodeResource\RelationManagers;
use App\Models\PromotionCode;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class PromotionCodeResource extends Resource
{
    protected static ?string $model = PromotionCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\Select::make('promotion_id')
                    ->relationship('promotion', 'title')
                    ->searchable(),
                Forms\Components\Select::make('discount_type')
                    ->options([
                        'percent' => 'Percent',
                        'fixed' => 'Fixed',
                    ])
                    ->default('percent')
                    ->required(),
                Forms\Components\TextInput::make('discount_value')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Forms\Components\TextInput::make('max_redemptions')
                    ->numeric(),
                Forms\Components\TextInput::make('redemptions')
                    ->numeric()
                    ->default(0),
                Forms\Components\DateTimePicker::make('expires_at'),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('promotion.title')
                    ->label('Promotion')
                    ->searchable(),
                Tables\Columns\TextColumn::make('discount_type'),
                Tables\Columns\TextColumn::make('discount_value'),
                Tables\Columns\BooleanColumn::make('is_active'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
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
            'index' => Pages\ListPromotionCodes::route('/'),
            'create' => Pages\CreatePromotionCode::route('/create'),
            'edit' => Pages\EditPromotionCode::route('/{record}/edit'),
        ];
    }
}
