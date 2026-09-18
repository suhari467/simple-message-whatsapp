<?php

namespace Tests\Feature;

use App\Livewire\ChatRoom;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class ChatroomTest extends TestCase
{
    use DatabaseTransactions;

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
        $response->assertSee('Join Event');
        $response->assertSeeLivewire(ChatRoom::class);
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

        Livewire::test(ChatRoom::class, ['page' => $page])
            // Awalnya belum join
            ->assertSet('hasJoined', false)
            ->assertSee('Join Event')
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

        $component = Livewire::test(ChatRoom::class, ['page' => $page])
            ->call('joinGroup')
            ->set('senderName', 'Developer')
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

        Livewire::test(ChatRoom::class, ['page' => $page])
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

    // ──────────────────────────────────────────────
    // 8. Tampilkan Title Prefix & Footer Author
    // ──────────────────────────────────────────────
    public function test_halaman_menampilkan_title_prefix_dan_footer_author(): void
    {
        $uniqueSlug = 'event-'.uniqid();
        $page = Page::create([
            'title_prefix' => 'The Wedding Of',
            'name' => 'Romeo & Juliet',
            'slug' => $uniqueSlug,
            'description' => 'Wedding celebration',
        ]);

        Livewire::test(ChatRoom::class, ['page' => $page])
            ->assertSee('The Wedding Of')
            ->assertSee('Romeo & Juliet')
            ->assertSee('Copyright')
            ->assertSee('Retech ID')
            ->assertSee(config('app.name'))
            ->assertSee('Created by')
            ->assertSee('https://instagram.com/suhari378');
    }

    // ──────────────────────────────────────────────
    // 9. Tampilkan Tipe Hadiah: Transfer & Datang Langsung
    // ──────────────────────────────────────────────
    public function test_halaman_menampilkan_tipe_hadiah_transfer_dan_datang_langsung(): void
    {
        $uniqueSlug = 'gift-event-'.uniqid();
        $page = Page::create([
            'name' => 'Gift Event Room',
            'slug' => $uniqueSlug,
            'description' => 'Event with gifts',
        ]);

        $page->donations()->create([
            'gift_type' => 'transfer',
            'bank_name' => 'BCA',
            'account_name' => 'Suhari Developer',
            'account_number' => '1234567890',
        ]);

        $page->donations()->create([
            'gift_type' => 'datang_langsung',
            'account_name' => 'Suhari Kediaman',
            'address' => 'Jl. Merdeka No. 45, Jakarta Pusat',
        ]);

        Livewire::test(ChatRoom::class, ['page' => $page])
            ->assertSee('Kirim Kado / Donasi')
            ->assertSee('BCA')
            ->assertSee('1234567890')
            ->assertSee('Kirim Kado')
            ->assertSee('Suhari Kediaman')
            ->assertSee('Jl. Merdeka No. 45, Jakarta Pusat');
    }

    // ──────────────────────────────────────────────
    // 10. Tampilkan Love Story Timeline dengan Gambar Berdampingan
    // ──────────────────────────────────────────────
    public function test_halaman_menampilkan_love_story_timeline_dengan_gambar_berdampingan(): void
    {
        $uniqueSlug = 'timeline-event-'.uniqid();
        $page = Page::create([
            'name' => 'Timeline Room',
            'slug' => $uniqueSlug,
            'description' => 'Event with love story',
        ]);

        $page->stories()->create([
            'title' => 'Pertama Berjumpa',
            'date_or_year' => 'Tahun 2021',
            'description' => 'Momen pertama kali bertatap mata di kampus.',
            'image_path' => 'stories/test-story.jpg',
            'sort_order' => 1,
        ]);

        Livewire::test(ChatRoom::class, ['page' => $page])
            ->assertSee('Perjalanan Cinta Kami')
            ->assertSee('Pertama Berjumpa')
            ->assertSee('Tahun 2021')
            ->assertSee('Momen pertama kali bertatap mata di kampus.')
            ->assertSeeHtml('stories/test-story.jpg');
    }

    // ──────────────────────────────────────────────
    // 11. Tampilkan Foto Mempelai dengan Fitur Pop-out (Lightbox)
    // ──────────────────────────────────────────────
    public function test_halaman_menampilkan_foto_mempelai_dengan_fitur_popout(): void
    {
        $uniqueSlug = 'couple-event-'.uniqid();
        $page = Page::create([
            'name' => 'Couple Room',
            'slug' => $uniqueSlug,
            'description' => 'Event with couple photos',
            'bride_name' => 'Siti Nurhaliza',
            'bride_image' => 'brides/bride.jpg',
            'groom_name' => 'Ahmad Dahlan',
            'groom_image' => 'grooms/groom.jpg',
        ]);

        Livewire::test(ChatRoom::class, ['page' => $page])
            ->assertSeeHtml('brides/bride.jpg')
            ->assertSeeHtml('grooms/groom.jpg')
            ->assertSeeHtml('previewPhoto')
            ->assertSee('Siti Nurhaliza')
            ->assertSee('Ahmad Dahlan');
    }
}
