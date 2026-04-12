<div>
    <div x-data="{
         joinPopup: @entangle('hasJoined').live,
         openEmoji: false,
         showInfo: @entangle('showPageInfo').live
     }"
     class="mx-auto h-screen relative bg-[#ece5dd] flex flex-col font-sans">
        
        <!-- INITIAL JOIN POPUP -->
        <div x-show="!joinPopup" 
             class="absolute inset-0 z-50 bg-black/60 flex items-center justify-center backdrop-blur-sm transition-opacity duration-300">
            <div class="bg-white rounded-xl shadow-2xl p-8 max-w-sm w-full text-center m-4">
                @if($page->logo)
                    <img src="{{ Storage::url($page->logo) }}" alt="Logo" class="w-24 h-24 rounded-full mx-auto mb-4 border-4 border-emerald-100 object-cover shadow-sm">
                @else
                    <div class="w-24 h-24 rounded-full bg-[#075e54] text-white flex items-center justify-center text-3xl mx-auto mb-4 shadow-sm">
                        {{ substr($page->name, 0, 1) }}
                    </div>
                @endif
                <h2 class="text-2xl font-bold text-gray-800 mb-2">{{ $page->name }}</h2>
                <p class="text-gray-500 mb-4 whitespace-pre-wrap text-sm line-clamp-3">{{ $page->description }}</p>
                <div class="bg-green-50 rounded-lg py-2 mb-6">
                    <p class="text-[#075e54] font-medium text-sm">Total: {{ $page->messages()->count() }} pesan terkirim</p>
                </div>
                <button wire:click="joinGroup" 
                        class="w-full bg-[#075e54] hover:bg-[#128c7e] text-white font-bold py-3 px-4 rounded-lg transition duration-200 transform hover:scale-105 active:scale-95 shadow-md">
                    Join Chatroom
                </button>
            </div>
        </div>

        <!-- MAIN CHATROOM (Hidden until Joined) -->
        <div x-show="joinPopup" style="display: none;" class="flex flex-col h-full overflow-hidden">
            
            <!-- HEADER -->
            <div class="bg-[#075e54] text-white px-4 py-3 flex items-center shadow-md cursor-pointer z-20" wire:click="togglePageInfo">
                @if($page->logo)
                    <img src="{{ Storage::url($page->logo) }}" class="w-10 h-10 rounded-full mr-3 object-cover shadow-sm">
                @else
                    <div class="w-10 h-10 rounded-full bg-gray-300 flex items-center justify-center font-bold mr-3 shadow-sm text-gray-600">
                        {{ substr($page->name, 0, 1) }}
                    </div>
                @endif
                <div class="flex-1">
                    <h1 class="font-bold text-lg leading-tight">{{ $page->name }}</h1>
                    <p class="text-xs text-green-100">Tap here for group info</p>
                </div>
            </div>

            <!-- PAGE INFO OVERLAY -->
            <div x-show="showInfo" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="absolute top-[64px] left-0 right-0 bg-white shadow-xl z-10 border-b border-gray-200" style="display:none;">
                <div class="p-6">
                    <h3 class="font-bold text-[#075e54] mb-2">Description</h3>
                    <p class="text-gray-700 text-sm whitespace-pre-wrap">{{ $page->description }}</p>
                    @if($page->content)
                    <hr class="my-4 border-gray-100">
                    <h3 class="font-bold text-[#075e54] mb-2">Info</h3>
                    <div class="text-gray-700 text-sm prose prose-sm">{!! str($page->content)->markdown() !!}</div>
                    @endif
                </div>
            </div>

            <!-- MESSAGES LIST -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar" id="chat-container" style="background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); background-repeat: repeat; background-attachment: fixed;">
                @if($page->content)
                <div class="bg-yellow-50 text-yellow-800 p-3 rounded-lg text-sm mb-4 shadow-sm border border-yellow-200">
                    {!! str($page->content)->markdown() !!}
                </div>
                @endif

                @foreach($messages as $msg)
                <div class="flex {{ $msg->sender_name === $senderName ? 'justify-end' : 'justify-start' }}" wire:key="msg-{{ $msg->id }}">
                    <div class="max-w-[80%] rounded-lg p-2 shadow-sm relative {{ $msg->sender_name === $senderName ? 'bg-[#dcf8c6] rounded-tr-none' : 'bg-white rounded-tl-none' }}">
                        <p class="text-xs font-bold {{ $msg->sender_name === $senderName ? 'text-[#075e54]' : 'text-blue-600' }} mb-1">{{ $msg->sender_name }}</p>
                        <div class="text-sm text-gray-800 break-words leading-relaxed markdown-content">
                            {!! str($msg->content)->markdown(['html_input' => 'escape']) !!}
                        </div>
                        <p class="text-[10px] text-gray-500 text-right mt-1">{{ $msg->created_at->format('H:i') }}</p>
                        
                        <!-- Triangle Corner -->
                        <div class="absolute top-0 {{ $msg->sender_name === $senderName ? '-right-2' : '-left-2' }}">
                            <svg width="10" height="10" viewBox="0 0 10 10" class="fill-current {{ $msg->sender_name === $senderName ? 'text-[#dcf8c6]' : 'text-white' }}">
                                @if($msg->sender_name === $senderName)
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

            <!-- CHAT INPUT -->
            <div class="bg-[#f0f0f0] p-3 flex flex-col gap-2 border-t border-gray-300">
                <div class="flex items-center gap-2">
                    <input type="text" wire:model="senderName" placeholder="Your Name" class="w-1/3 p-2 rounded-full border border-gray-300 text-sm focus:outline-none focus:ring-1 focus:ring-[#075e54]">
                    @error('senderName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
                <div class="flex items-center gap-2">
                    <div class="relative">
                        <button type="button" @click="openEmoji = !openEmoji" class="w-10 h-10 flex items-center justify-center text-gray-500 hover:text-[#075e54] transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </button>
                        <!-- EMOJI PICKER POPUP -->
                        <div x-show="openEmoji" style="display:none;" class="absolute bottom-12 left-0 z-50">
                            <emoji-picker @emoji-click="$wire.set('content', $wire.content + $event.detail.unicode); openEmoji = false; $refs.chatInput.focus()"></emoji-picker>
                        </div>
                    </div>

                    <div class="flex-1 flex items-center bg-white rounded-full px-4 py-2 shadow-inner">
                        <input type="text" 
                            wire:model="content"
                            wire:keydown.enter="sendMessage"
                            x-ref="chatInput"
                            placeholder="Type a message... (Use **bold**, *italic*)" 
                            class="flex-1 bg-transparent border-none focus:outline-none text-sm">
                    </div>
                    
                    <button wire:click="sendMessage" class="bg-[#075e54] text-white w-10 h-10 rounded-full flex items-center justify-center hover:bg-[#128c7e] transition shadow-md" wire:loading.attr="disabled">
                        <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@1/index.js"></script>
        <script>
            document.addEventListener('livewire:initialized', () => {
                const container = document.getElementById('chat-container');
                if (container) container.scrollTop = container.scrollHeight;
                
                Livewire.hook('morph.updated', ({ component, el }) => {
                    if (container) container.scrollTop = container.scrollHeight;
                });
            });
        </script>
        <style>
            .markdown-content p { margin-bottom: 0.25em; margin-top: 0; }
            .markdown-content p:last-child { margin-bottom: 0; }
            .markdown-content strong { font-weight: 700; }
            .markdown-content em { font-style: italic; }
            .markdown-content del { text-decoration: line-through; }
            .custom-scrollbar::-webkit-scrollbar { width: 6px; }
            .custom-scrollbar::-webkit-scrollbar-thumb { background-color: rgba(0,0,0,0.2); border-radius: 10px; }
        </style>
    @endpush
</div>
