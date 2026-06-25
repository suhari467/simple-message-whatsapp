<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Filament\Resources\Pages\PageResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('logo')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('view_public')
                    ->label('Lihat')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('success')
                    ->url(fn ($record) => url("/{$record->slug}"))
                    ->openUrlInNewTab(),
                Action::make('view_messages')
                    ->label('Pesan')
                    ->icon(Heroicon::OutlinedChatBubbleLeftEllipsis)
                    ->color('info')
                    ->url(fn ($record) => PageResource::getUrl('messages', ['record' => $record])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
