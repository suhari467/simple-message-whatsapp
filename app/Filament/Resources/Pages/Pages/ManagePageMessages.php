<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Messages\Tables\MessagesTable;
use App\Filament\Resources\Pages\PageResource;
use BackedEnum;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ManagePageMessages extends ManageRelatedRecords
{
    protected static string $resource = PageResource::class;

    protected static string $relationship = 'messages';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftEllipsis;

    public static function getNavigationLabel(): string
    {
        return 'Pesan';
    }

    public function table(Table $table): Table
    {
        return MessagesTable::configure($table);
    }
}
