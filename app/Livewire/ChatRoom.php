<?php

namespace App\Livewire;

use App\Models\Page;
use Livewire\Component;

class ChatRoom extends Component
{
    public Page $page;

    public $content = '';

    public $senderName = '';

    public $hasJoined = false;

    public $showPageInfo = false;

    public function mount(Page $page)
    {
        $this->page = $page;
        $this->page->load(['galleries', 'stories', 'donations']);
    }

    public function joinGroup()
    {
        $this->hasJoined = true;
    }

    public function togglePageInfo()
    {
        $this->showPageInfo = ! $this->showPageInfo;
    }

    public function sendMessage()
    {
        $this->validate([
            'senderName' => 'required|string|max:50',
            'content' => 'required|string',
        ]);

        $this->page->messages()->create([
            'sender_name' => $this->senderName,
            'content' => $this->content,
        ]);

        $this->content = '';

        $this->dispatch('scroll-to-bottom');
    }

    public function render()
    {
        return view('livewire.chatroom', [
            'messages' => $this->page->messages()->oldest()->get(),
        ])->layout('components.layouts.app', ['page' => $this->page]);
    }
}
