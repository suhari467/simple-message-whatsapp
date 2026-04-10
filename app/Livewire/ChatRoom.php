<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Message;

class ChatRoom extends Component
{
    public $chats = [];
    public $senderName = '';
    public $messageContent = '';

    protected $listeners = ['messageSent' => 'loadMessages'];

    public function mount()
    {
        $this->loadMessages();
    }

    public function loadMessages()
    {
        $this->chats = Message::latest()->take(50)->get()->reverse()->values()->all();
    }

    public function sendMessage()
    {
        $this->validate([
            'senderName' => 'required|min:2',
            'messageContent' => 'required',
        ]);

        Message::create([
            'sender_name' => $this->senderName,
            'content' => $this->messageContent,
        ]);

        $this->messageContent = '';
        $this->loadMessages();
        
        $this->dispatch('messageAdded');
    }

    public function render()
    {
        return view('livewire.chat-room');
    }
}
