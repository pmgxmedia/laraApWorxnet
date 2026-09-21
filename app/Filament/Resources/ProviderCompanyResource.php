<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProviderCompanyResource\Pages;
use App\Filament\Resources\ProviderCompanyResource\RelationManagers;
use App\Models\ProviderCompany;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class ProviderCompanyResource extends Resource
{
    protected static ?string $model = ProviderCompany::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('owner_user_id')
                    ->label('Owner')
                    ->relationship('owner', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('type')
                    ->options([
                        'individual' => 'Individual',
                        'company' => 'Company',
                    ])
                    ->default('individual')
                    ->required(),
                Forms\Components\TextInput::make('display_name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('registration_number')
                    ->maxLength(255),
                Forms\Components\TextInput::make('vat_number')
                    ->maxLength(255),
                Forms\Components\Textarea::make('bio')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('logo_path')
                    ->maxLength(255),
                Forms\Components\TextInput::make('primary_area')
                    ->maxLength(255),
                Forms\Components\TextInput::make('base_lat')
                    ->numeric(),
                Forms\Components\TextInput::make('base_lng')
                    ->numeric(),
                Forms\Components\TextInput::make('travel_radius_km')
                    ->numeric()
                    ->default(20),
                Forms\Components\Toggle::make('emergency_available')
                    ->default(false),
                Forms\Components\TextInput::make('rating_average')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('rating_count')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('completed_jobs')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('response_minutes_avg')
                    ->numeric(),
                Forms\Components\Select::make('verification_status')
                    ->options([
                        'unverified' => 'Unverified',
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                        'suspended' => 'Suspended',
                    ])
                    ->default('unverified')
                    ->required(),
                Forms\Components\Toggle::make('is_featured')
                    ->default(false),
                Forms\Components\Toggle::make('is_active')
                    ->default(true),
                Forms\Components\DateTimePicker::make('verified_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('display_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Owner')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('verification_status'),
                Tables\Columns\BooleanColumn::make('is_active'),
                Tables\Columns\TextColumn::make('rating_average'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('verification_status')
                    ->options([
                        'unverified' => 'Unverified',
                        'pending' => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                        'suspended' => 'Suspended',
                    ]),
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'individual' => 'Individual',
                        'company' => 'Company',
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
            'index' => Pages\ListProviderCompanies::route('/'),
            'create' => Pages\CreateProviderCompany::route('/create'),
            'edit' => Pages\EditProviderCompany::route('/{record}/edit'),
        ];
    }
}
