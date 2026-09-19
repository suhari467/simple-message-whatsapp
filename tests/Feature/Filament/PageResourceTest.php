<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Schemas\PageForm;
use App\Models\Page;
use App\Models\User;
use Filament\Schemas\Components\Wizard\Step;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class PageResourceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_page_form_has_four_wizard_steps(): void
    {
        $steps = PageForm::getSteps();

        $this->assertCount(4, $steps);
        $this->assertInstanceOf(Step::class, $steps[0]);
        $this->assertEquals('Informasi Dasar Grup', $steps[0]->getLabel());
        $this->assertEquals('Detail Mempelai & Acara', $steps[1]->getLabel());
        $this->assertEquals('Media & Cerita Perjalanan (Timeline)', $steps[2]->getLabel());
        $this->assertEquals('Hadiah Digital / Kado', $steps[3]->getLabel());
    }

    public function test_user_can_access_create_page_wizard(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/pages/create');
        $response->assertSuccessful();

        Livewire::actingAs($user)
            ->test(CreatePage::class)
            ->assertSuccessful()
            ->assertSee('Informasi Dasar Grup')
            ->assertSee('Detail Mempelai & Acara');
    }

    public function test_user_can_access_edit_page(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $page = Page::create([
            'name' => 'Existing Wedding',
            'slug' => 'existing-wedding-'.uniqid(),
            'user_id' => $user->id,
            'description' => 'Description',
        ]);

        $response = $this->actingAs($user)->get("/admin/pages/{$page->id}/edit");
        $response->assertSuccessful();

        Livewire::actingAs($user)
            ->test(EditPage::class, ['record' => $page->id])
            ->assertSuccessful()
            ->assertSee('Informasi Dasar Grup');
    }
}
