<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProviderVerificationResource\Pages;
use App\Filament\Resources\ProviderVerificationResource\RelationManagers;
use App\Models\ProviderVerification;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class ProviderVerificationResource extends Resource
{
    protected static ?string $model = ProviderVerification::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('provider_company_id')
                    ->relationship('providerCompany', 'display_name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('document_type')
                    ->options([
                        'identity' => 'Identity',
                        'business_registration' => 'Business Registration',
                        'vat_certificate' => 'VAT Certificate',
                        'insurance' => 'Insurance',
                        'security_accreditation' => 'Security Accreditation',
                        'qualification' => 'Qualification',
                        'other' => 'Other',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('file_path')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'expired' => 'Expired',
                    ])
                    ->default('pending')
                    ->required(),
                Forms\Components\Textarea::make('reviewer_notes')
                    ->columnSpanFull(),
                Forms\Components\Select::make('reviewed_by')
                    ->relationship('reviewer', 'name')
                    ->searchable(),
                Forms\Components\DateTimePicker::make('expires_at'),
                Forms\Components\DateTimePicker::make('reviewed_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('providerCompany.display_name')
                    ->label('Provider Company')
                    ->searchable(),
                Tables\Columns\TextColumn::make('document_type'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
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
            'index' => Pages\ListProviderVerifications::route('/'),
            'create' => Pages\CreateProviderVerification::route('/create'),
            'edit' => Pages\EditProviderVerification::route('/{record}/edit'),
        ];
    }
}
