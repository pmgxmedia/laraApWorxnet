<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProviderTeamMemberResource\Pages;
use App\Filament\Resources\ProviderTeamMemberResource\RelationManagers;
use App\Models\ProviderTeamMember;
use Filament\Forms;
use Filament\Resources\Form;
use Filament\Resources\Resource;
use Filament\Resources\Table;
use Filament\Tables;

class ProviderTeamMemberResource extends Resource
{
    protected static ?string $model = ProviderTeamMember::class;

    protected static ?string $navigationIcon = 'heroicon-o-collection';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('provider_company_id')
                    ->relationship('providerCompany', 'display_name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('job_title')
                    ->maxLength(255),
                Forms\Components\Toggle::make('can_accept_jobs')
                    ->default(true),
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
                Tables\Columns\TextColumn::make('providerCompany.display_name')
                    ->label('Provider Company')
                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable(),
                Tables\Columns\TextColumn::make('job_title'),
                Tables\Columns\BooleanColumn::make('can_accept_jobs'),
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
            'index' => Pages\ListProviderTeamMembers::route('/'),
            'create' => Pages\CreateProviderTeamMember::route('/create'),
            'edit' => Pages\EditProviderTeamMember::route('/{record}/edit'),
        ];
    }
}
