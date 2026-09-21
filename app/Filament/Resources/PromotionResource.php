<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionResource\Pages;
use App\Filament\Resources\PromotionResource\RelationManagers;
use App\Models\Promotion;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                Forms\Components\Select::make('type')
                    ->options([
                        'featured_listing' => 'Featured Listing',
                        'homepage_banner' => 'Homepage Banner',
                        'category_banner' => 'Category Banner',
                        'email_campaign' => 'Email Campaign',
                    ])
                    ->required(),
                Forms\Components\Select::make('provider_company_id')
                    ->relationship('providerCompany', 'display_name')
                    ->searchable(),
                Forms\Components\Select::make('service_category_id')
                    ->relationship('serviceCategory', 'name')
                    ->searchable(),
                Forms\Components\TextInput::make('target_province')
                    ->maxLength(255),
                Forms\Components\TextInput::make('placement')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('starts_at'),
                Forms\Components\DateTimePicker::make('ends_at'),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending_approval' => 'Pending Approval',
                        'active' => 'Active',
                        'paused' => 'Paused',
                        'ended' => 'Ended',
                    ])
                    ->default('draft')
                    ->required(),
                Forms\Components\Select::make('created_by')
                    ->relationship('creator', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('impressions')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('clicks')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('conversions')
                    ->numeric()
                    ->default(0),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('starts_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('ends_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending_approval' => 'Pending Approval',
                        'active' => 'Active',
                        'paused' => 'Paused',
                        'ended' => 'Ended',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'featured_listing' => 'Featured Listing',
                        'homepage_banner' => 'Homepage Banner',
                        'category_banner' => 'Category Banner',
                        'email_campaign' => 'Email Campaign',
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
            'index' => Pages\ListPromotions::route('/'),
            'create' => Pages\CreatePromotion::route('/create'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),
        ];
    }
}
