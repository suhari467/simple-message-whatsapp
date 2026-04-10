<div class="flex flex-col h-screen bg-[#ece5dd] font-sans overflow-hidden">
    <!-- Header -->
    <div class="bg-[#075e54] text-white p-3 flex items-center shadow-md z-10">
        <div class="w-10 h-10 bg-gray-300 rounded-full mr-3 flex items-center justify-center text-gray-600 font-bold overflow-hidden">
            <img src="https://ui-avatars.com/api/?name=WA+Chat&background=random" alt="Avatar">
        </div>
        <div class="flex-1">
            <h2 class="font-bold text-lg leading-tight">WhatsApp SPA</h2>
            <p class="text-xs text-green-100">online</p>
        </div>
    </div>

    <!-- Chat Area -->
    <div id="chat-area" class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar" style="background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); background-repeat: repeat; background-attachment: fixed;">
        @foreach($chats as $chat)
            <div class="flex {{ $chat['sender_name'] == $senderName ? 'justify-end' : 'justify-start' }}" wire:key="msg-{{ $chat['id'] }}">
                <div class="max-w-[80%] rounded-lg p-2 shadow-sm relative {{ $chat['sender_name'] == $senderName ? 'bg-[#dcf8c6] rounded-tr-none' : 'bg-white rounded-tl-none' }}">
                    <p class="text-xs font-bold text-blue-600 mb-1">{{ $chat['sender_name'] }}</p>
                    <p class="text-sm text-gray-800">{{ $chat['content'] }}</p>
                    <p class="text-[10px] text-gray-500 text-right mt-1">{{ Carbon\Carbon::parse($chat['created_at'])->format('H:i') }}</p>
                    
                    <!-- Triangle Corner -->
                    <div class="absolute top-0 {{ $chat['sender_name'] == $senderName ? '-right-2' : '-left-2' }}">
                        <svg width="10" height="10" viewBox="0 0 10 10" class="fill-current {{ $chat['sender_name'] == $senderName ? 'text-[#dcf8c6]' : 'text-white' }}">
                            @if($chat['sender_name'] == $senderName)
                                <path d="M0 0 L10 0 L0 10 Z" />
                            @else
                                <path d="M10 0 L0 0 L10 10 Z" />
                            @endif
                        </svg>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Input Area -->
    <div class="bg-[#f0f0f0] p-3 flex flex-col gap-2 border-t border-gray-300">
        <div class="flex items-center gap-2">
            <input type="text" wire:model="senderName" placeholder="Your Name" class="w-1/3 p-2 rounded-full border border-gray-300 text-sm focus:outline-none focus:ring-1 focus:ring-[#075e54]">
            @error('senderName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div class="flex items-center gap-2">
            <div class="flex-1 flex items-center bg-white rounded-full px-4 py-2 shadow-inner">
                <input type="text" wire:model="messageContent" wire:keydown.enter="sendMessage" placeholder="Type a message..." class="flex-1 bg-transparent border-none focus:outline-none text-sm">
            </div>
            <button wire:click="sendMessage" class="bg-[#075e54] text-white w-10 h-10 rounded-full flex items-center justify-center hover:bg-[#128c7e] transition shadow-md">
                <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
                </svg>
            </button>
        </div>
    </div>

    <script>
        document.addEventListener('livewire:initialized', () => {
             const chatArea = document.getElementById('chat-area');
             chatArea.scrollTop = chatArea.scrollHeight;

             Livewire.on('messageAdded', () => {
                 setTimeout(() => {
                     chatArea.scrollTo({
                         top: chatArea.scrollHeight,
                         behavior: 'smooth'
                     });
                 }, 100);
             });
        });
    </script>

    <style>
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: rgba(0,0,0,0.2);
            border-radius: 10px;
        }
    </style>
</div>
