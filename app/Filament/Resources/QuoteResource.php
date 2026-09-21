<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuoteResource\Pages;
use App\Filament\Resources\QuoteResource\RelationManagers;
use App\Models\Quote;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class QuoteResource extends Resource
{
    protected static ?string $model = Quote::class;

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
                Forms\Components\TextInput::make('amount')
                    ->numeric()
                    ->required(),
                Forms\Components\KeyValue::make('line_items'),
                Forms\Components\Toggle::make('vat_included')
                    ->default(false),
                Forms\Components\Textarea::make('exclusions')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('warranty_terms')
                    ->maxLength(255),
                Forms\Components\DatePicker::make('valid_until'),
                Forms\Components\Select::make('status')
                    ->options([
                        'submitted' => 'Submitted',
                        'accepted' => 'Accepted',
                        'declined' => 'Declined',
                        'withdrawn' => 'Withdrawn',
                        'expired' => 'Expired',
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
                Tables\Columns\TextColumn::make('serviceRequest.title')
                    ->label('Service Request')
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
                        'accepted' => 'Accepted',
                        'declined' => 'Declined',
                        'withdrawn' => 'Withdrawn',
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
            'index' => Pages\ListQuotes::route('/'),
            'create' => Pages\CreateQuote::route('/create'),
            'edit' => Pages\EditQuote::route('/{record}/edit'),
        ];
    }
}
