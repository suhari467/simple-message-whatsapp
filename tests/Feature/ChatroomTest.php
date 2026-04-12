<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\User;
use App\Livewire\Chatroom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ChatroomTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────
    // 1. Otorisasi Admin → UserResource (boleh)
    // ──────────────────────────────────────────────
    public function test_admin_dapat_mengakses_user_resource(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertSuccessful();
    }

    // ──────────────────────────────────────────────
    // 2. Otorisasi User → UserResource (ditolak)
    // ──────────────────────────────────────────────
    public function test_user_tidak_bisa_mengakses_user_resource(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/users');

        // Filament bisa return 403 atau redirect, tergantung konfigurasi
        $this->assertTrue(
            in_array($response->getStatusCode(), [403, 302]),
            'User biasa seharusnya tidak bisa akses /admin/users'
        );
    }

    // ──────────────────────────────────────────────
    // 3. Otorisasi User → PageResource (boleh)
    // ──────────────────────────────────────────────
    public function test_user_dapat_mengakses_page_resource(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/pages');

        $response->assertSuccessful();
    }

    // ──────────────────────────────────────────────
    // 4. Load halaman publik → popup Join muncul
    // ──────────────────────────────────────────────
    public function test_halaman_publik_menampilkan_popup_join(): void
    {
        $page = Page::create([
            'name' => 'Support Channel',
            'slug' => 'support-channel',
            'description' => 'Help center description.',
            'content' => 'Please provide detailed info.',
        ]);

        $response = $this->get('/support-channel');

        $response->assertSuccessful();
        $response->assertSee('Support Channel');
        $response->assertSee('Help center description.');
        $response->assertSee('Join Chatroom');
        $response->assertSeeLivewire(Chatroom::class);
    }

    // ──────────────────────────────────────────────
    // 5. Simulasi DOM: klik Join → popup hilang
    // ──────────────────────────────────────────────
    public function test_klik_join_menyembunyikan_popup(): void
    {
        $page = Page::create([
            'name' => 'General Chat',
            'slug' => 'general',
            'description' => 'General group discussion',
        ]);

        Livewire::test(Chatroom::class, ['page' => $page])
            // Awalnya belum join
            ->assertSet('hasJoined', false)
            ->assertSee('Join Chatroom')
            // Klik Join
            ->call('joinGroup')
            // Sesudah join, state berubah
            ->assertSet('hasJoined', true);
    }

    // ──────────────────────────────────────────────
    // 6. Kirim pesan dengan Markdown + Emoji
    // ──────────────────────────────────────────────
    public function test_kirim_pesan_markdown_dan_emoji(): void
    {
        $page = Page::create([
            'name' => 'Dev Room',
            'slug' => 'dev-room',
            'description' => 'Developer discussion',
        ]);

        $component = Livewire::test(Chatroom::class, ['page' => $page])
            ->call('joinGroup')
            ->set('content', '**Hello** world *italics* 🎉')
            ->call('sendMessage')
            ->assertHasNoErrors();

        // Verifikasi pesan disimpan di database
        $this->assertDatabaseHas('messages', [
            'page_id' => $page->id,
            'content' => '**Hello** world *italics* 🎉',
        ]);

        // Verifikasi rendering markdown menjadi HTML
        $component->assertSeeHtml('<strong>Hello</strong>');
        $component->assertSeeHtml('<em>italics</em>');
        $component->assertSee('🎉');
    }

    // ──────────────────────────────────────────────
    // 7. Toggle Page Info (klik header)
    // ──────────────────────────────────────────────
    public function test_toggle_page_info_header(): void
    {
        $page = Page::create([
            'name' => 'Info Room',
            'slug' => 'info-room',
            'description' => 'Room description here',
            'content' => 'Extra content',
        ]);

        Livewire::test(Chatroom::class, ['page' => $page])
            ->call('joinGroup')
            // Awalnya page info tersembunyi
            ->assertSet('showPageInfo', false)
            // Klik header pertama kali → muncul
            ->call('togglePageInfo')
            ->assertSet('showPageInfo', true)
            // Klik header kedua kali → hilang
            ->call('togglePageInfo')
            ->assertSet('showPageInfo', false);
    }
}
