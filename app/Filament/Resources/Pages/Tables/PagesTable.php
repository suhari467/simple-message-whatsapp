<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Filament\Resources\Pages\PageResource;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Stack::make([
                    ImageColumn::make('logo')
                        ->height(180)
                        ->width('100%')
                        ->extraImgAttributes([
                            'class' => 'object-cover w-full rounded-t-xl',
                        ])
                        ->disk('public')
                        ->defaultImageUrl(asset('assets/img/default-page.png')),
                    Stack::make([
                        TextColumn::make('name')
                            ->weight(FontWeight::Bold)
                            ->size('lg')
                            ->searchable(),
                        TextColumn::make('slug')
                            ->color('gray')
                            ->size('sm')
                            ->prefix('/')
                            ->searchable(),
                        TextColumn::make('created_at')
                            ->dateTime('d M Y H:i')
                            ->color('gray')
                            ->size('xs'),
                    ])->space(1)->extraAttributes(['class' => 'p-4']),
                ]),
            ])
            ->contentGrid([
                'sm' => 1,
                'md' => 2,
                'lg' => 3,
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
                Action::make('view_recipients')
                    ->label('Penerima')
                    ->icon(Heroicon::OutlinedUsers)
                    ->color('gray')
                    ->url(fn ($record) => PageResource::getUrl('recipients', ['record' => $record])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
