<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceRequestResource\Pages;
use App\Filament\Resources\ServiceRequestResource\RelationManagers;
use App\Models\ServiceRequest;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class ServiceRequestResource extends Resource
{
    protected static ?string $model = ServiceRequest::class;

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
                Forms\Components\Select::make('urgency')
                    ->options([
                        'emergency' => 'Emergency',
                        '24h' => '24 Hours',
                        'week' => 'Within a Week',
                        'planned' => 'Planned',
                    ])
                    ->default('24h')
                    ->required(),
                Forms\Components\TextInput::make('area_label')
                    ->maxLength(255),
                Forms\Components\TextInput::make('lat')
                    ->numeric(),
                Forms\Components\TextInput::make('lng')
                    ->numeric(),
                Forms\Components\TextInput::make('exact_address')
                    ->maxLength(255),
                Forms\Components\TextInput::make('budget_min')
                    ->numeric(),
                Forms\Components\TextInput::make('budget_max')
                    ->numeric(),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'matched' => 'Matched',
                        'viewed' => 'Viewed',
                        'accepted' => 'Accepted',
                        'quoted' => 'Quoted',
                        'booked' => 'Booked',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'disputed' => 'Disputed',
                        'closed' => 'Closed',
                    ])
                    ->default('submitted')
                    ->required(),
                Forms\Components\Select::make('accepted_provider_company_id')
                    ->label('Accepted Provider')
                    ->relationship('acceptedProvider', 'display_name')
                    ->searchable(),
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
                Tables\Columns\TextColumn::make('urgency'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'matched' => 'Matched',
                        'viewed' => 'Viewed',
                        'accepted' => 'Accepted',
                        'quoted' => 'Quoted',
                        'booked' => 'Booked',
                        'in_progress' => 'In Progress',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'disputed' => 'Disputed',
                        'closed' => 'Closed',
                    ]),
                Tables\Filters\SelectFilter::make('urgency')
                    ->options([
                        'emergency' => 'Emergency',
                        '24h' => '24 Hours',
                        'week' => 'Within a Week',
                        'planned' => 'Planned',
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
            'index' => Pages\ListServiceRequests::route('/'),
            'create' => Pages\CreateServiceRequest::route('/create'),
            'edit' => Pages\EditServiceRequest::route('/{record}/edit'),
        ];
    }
}
