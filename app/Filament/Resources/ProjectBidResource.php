<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectBidResource\Pages;
use App\Filament\Resources\ProjectBidResource\RelationManagers;
use App\Models\ProjectBid;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class ProjectBidResource extends Resource
{
    protected static ?string $model = ProjectBid::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('project_id')
                    ->relationship('project', 'title')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('provider_company_id')
                    ->relationship('providerCompany', 'display_name')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->required(),
                Forms\Components\Textarea::make('scope_included')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('exclusions')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('warranty_terms')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('estimated_start'),
                Forms\Components\TextInput::make('estimated_duration_days')
                    ->numeric(),
                Forms\Components\DatePicker::make('valid_until'),
                Forms\Components\Textarea::make('notes')
                    ->columnSpanFull(),
                Forms\Components\Select::make('status')
                    ->options([
                        'submitted' => 'Submitted',
                        'shortlisted' => 'Shortlisted',
                        'awarded' => 'Awarded',
                        'declined' => 'Declined',
                        'withdrawn' => 'Withdrawn',
                    ])
                    ->default('submitted')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.title')
                    ->label('Project')
                    ->searchable(),
                Tables\Columns\TextColumn::make('providerCompany.display_name')
                    ->label('Provider Company')
                    ->searchable(),
                Tables\Columns\TextColumn::make('amount'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'submitted' => 'Submitted',
                        'shortlisted' => 'Shortlisted',
                        'awarded' => 'Awarded',
                        'declined' => 'Declined',
                        'withdrawn' => 'Withdrawn',
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
            'index' => Pages\ListProjectBids::route('/'),
            'create' => Pages\CreateProjectBid::route('/create'),
            'edit' => Pages\EditProjectBid::route('/{record}/edit'),
        ];
    }
}
