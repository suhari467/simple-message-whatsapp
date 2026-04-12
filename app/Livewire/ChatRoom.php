<?php

namespace App\Livewire;

use App\Models\Page;
use App\Models\Message;
use Livewire\Component;
use Illuminate\Support\Str;

class Chatroom extends Component
{
    public Page $page;
    public $content = '';
    public $senderName = 'Guest';
    public $hasJoined = false;
    public $showPageInfo = false;

    public function mount(Page $page)
    {
        $this->page = $page;
        $this->senderName = 'User-' . rand(1000, 9999);
    }

    public function joinGroup()
    {
        $this->hasJoined = true;
    }

    public function togglePageInfo()
    {
        $this->showPageInfo = !$this->showPageInfo;
    }

    public function sendMessage()
    {
        $this->validate([
            'content' => 'required|string',
        ]);

        $this->page->messages()->create([
            'sender_name' => $this->senderName,
            'content' => $this->content,
        ]);

        $this->content = '';
    }

    public function render()
    {
        return view('livewire.chatroom', [
            'messages' => $this->page->messages()->oldest()->get(),
        ])->layout('components.layouts.app');
    }
}
