<?php

namespace App\Filament\Widgets;

use App\Models\Page;
use Filament\Widgets\Widget;

class WeddingCountdownWidget extends Widget
{
    protected string $view = 'filament.widgets.wedding-countdown-widget';

    protected int|string|array $columnSpan = 'full';

    /**
     * Get the pages and their wedding dates for the countdown.
     */
    public function getPages(): array
    {
        $query = Page::query();

        if (auth()->user()->role !== 'admin') {
            $query->where(fn ($q) => $q->where('user_id', auth()->id())->orWhereNull('user_id'));
        }

        return $query->whereNotNull('wedding_date')
            ->get()
            ->map(fn (Page $page) => [
                'name' => $page->name,
                'slug' => $page->slug,
                'wedding_date' => $page->wedding_date->format('Y-m-d').' 00:00:00',
            ])
            ->toArray();
    }
}
