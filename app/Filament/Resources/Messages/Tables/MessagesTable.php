<?php

namespace App\Filament\Resources\Messages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('page.name')
                    ->label('Halaman')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sender_name')
                    ->label('Pengirim')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('content')
                    ->label('Pesan')
                    ->limit(50)
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Diterima Pada')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
