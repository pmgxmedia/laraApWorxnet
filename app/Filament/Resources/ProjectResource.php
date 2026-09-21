<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Filament\Resources\ProjectResource\RelationManagers;
use App\Models\Project;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('service_category_id')
                    ->relationship('serviceCategory', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Select::make('property_type')
                    ->options([
                        'home' => 'Home',
                        'complex' => 'Complex',
                        'business' => 'Business',
                        'office' => 'Office',
                        'retail' => 'Retail',
                        'estate' => 'Estate',
                    ])
                    ->default('home')
                    ->required(),
                Forms\Components\Select::make('budget_type')
                    ->options([
                        'fixed' => 'Fixed',
                        'range' => 'Range',
                        'quote' => 'Quote',
                    ])
                    ->default('range')
                    ->required(),
                Forms\Components\TextInput::make('budget_min')
                    ->numeric(),
                Forms\Components\TextInput::make('budget_max')
                    ->numeric(),
                Forms\Components\Toggle::make('budget_visible_to_providers')
                    ->default(true),
                Forms\Components\TextInput::make('area_label')
                    ->maxLength(255),
                Forms\Components\TextInput::make('lat')
                    ->numeric(),
                Forms\Components\TextInput::make('lng')
                    ->numeric(),
                Forms\Components\TextInput::make('exact_address')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('bid_deadline'),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending_review' => 'Pending Review',
                        'live' => 'Live',
                        'paused' => 'Paused',
                        'awarded' => 'Awarded',
                        'booked' => 'Booked',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'disputed' => 'Disputed',
                    ])
                    ->default('live')
                    ->required(),
                Forms\Components\Select::make('awarded_provider_company_id')
                    ->label('Awarded Provider')
                    ->relationship('awardedProvider', 'display_name')
                    ->searchable(),
                Forms\Components\Toggle::make('is_public')
                    ->default(true),
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
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable(),
                Tables\Columns\TextColumn::make('serviceCategory.name')
                    ->label('Category')
                    ->searchable(),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\BooleanColumn::make('is_public'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending_review' => 'Pending Review',
                        'live' => 'Live',
                        'paused' => 'Paused',
                        'awarded' => 'Awarded',
                        'booked' => 'Booked',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'disputed' => 'Disputed',
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
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }
}
