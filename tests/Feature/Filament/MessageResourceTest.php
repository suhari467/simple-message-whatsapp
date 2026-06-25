<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Pages\Pages\ManagePageMessages;
use App\Models\Message;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MessageResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_messages_on_dedicated_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page = Page::create([
            'name' => 'Test Page',
            'slug' => 'test-page',
            'description' => 'Test description',
            'content' => 'Test content',
        ]);

        $message = Message::create([
            'sender_name' => 'John Doe',
            'content' => 'Hello World',
            'page_id' => $page->id,
        ]);

        Livewire::actingAs($admin)
            ->test(ManagePageMessages::class, [
                'record' => $page->id,
            ])
            ->assertCanSeeTableRecords([$message])
            ->assertTableColumnExists('sender_name')
            ->assertTableColumnExists('content')
            ->assertTableColumnExists('created_at');
    }

    public function test_admin_can_delete_message_from_dedicated_page(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $page = Page::create([
            'name' => 'Test Page',
            'slug' => 'test-page',
            'description' => 'Test description',
            'content' => 'Test content',
        ]);

        $message = Message::create([
            'sender_name' => 'John Doe',
            'content' => 'Hello World',
            'page_id' => $page->id,
        ]);

        Livewire::actingAs($admin)
            ->test(ManagePageMessages::class, [
                'record' => $page->id,
            ])
            ->callTableAction('delete', $message);

        $this->assertDatabaseMissing('messages', [
            'id' => $message->id,
        ]);
    }
}
