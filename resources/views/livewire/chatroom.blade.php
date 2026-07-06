<div>
    <div x-data="{
         joinPopup: @entangle('hasJoined').live,
         openEmoji: false,
         showInfo: @entangle('showPageInfo').live,
         audioPlaying: false,
         audioProgress: 0,
         waveformBars: [],
         activePhoto: null,
         galleryPhotos: [],
         driverObj: null,
         tutorialRefreshInterval: null,
         initAudio() {
            this.initWaveform();
            if(this.joinPopup) { 
                this.playAudio(); 
                this.startTutorial();
            }
            $watch('joinPopup', value => {
                if(value) { 
                    setTimeout(() => this.playAudio(), 500); 
                    setTimeout(() => this.updateWaveform(), 100);
                    this.startTutorial();
                }
            });
         },
         startTutorial() {
             if (!localStorage.getItem('tutorialCompleted')) {
                 setTimeout(() => {
                     if (window.driver && window.driver.js) {
                         const driver = window.driver.js.driver;
                         
                         const handleHeaderClick = (e) => {
                             const headerInfo = document.getElementById('header-info');
                             if (headerInfo) {
                                 const rect = headerInfo.getBoundingClientRect();
                                 if (e.clientX >= rect.left && e.clientX <= rect.right &&
                                     e.clientY >= rect.top && e.clientY <= rect.bottom) {
                                     if (this.driverObj) {
                                         this.driverObj.destroy();
                                     }
                                     this.showInfo = true;
                                 }
                             }
                         };
                         document.addEventListener('click', handleHeaderClick, true);

                         this.driverObj = driver({
                             showProgress: false,
                             allowClose: false,
                             disableActiveInteraction: false,
                             showButtons: ['next', 'close'],
                             stagePadding: 0,
                             steps: [
                                 { 
                                     element: '#header-info', 
                                     popover: { 
                                         title: 'Petunjuk', 
                                         description: 'Informasi acara dapat menekan header diatas', 
                                         side: 'bottom', 
                                         align: 'start' 
                                     } 
                                 }
                             ],
                             onDestroyed: () => {
                                 localStorage.setItem('tutorialCompleted', 'true');
                                 this.driverObj = null;
                                 document.removeEventListener('click', handleHeaderClick, true);
                                 if (this.tutorialRefreshInterval) {
                                     clearInterval(this.tutorialRefreshInterval);
                                     this.tutorialRefreshInterval = null;
                                 }
                             }
                         });
                         this.driverObj.drive();
                         this.tutorialRefreshInterval = setInterval(() => {
                             if (this.driverObj) {
                                 this.driverObj.refresh();
                             }
                         }, 500);
                     }
                 }, 800);
             }
         },
         playAudio() {
             let audio = this.$refs.bgAudio;
             if(audio) {
                 audio.play().then(() => { this.audioPlaying = true; }).catch(e => console.log('Autoplay blocked', e));
             }
         },
         toggleAudio() {
             let audio = this.$refs.bgAudio;
             if(!audio) return;
             if(this.audioPlaying) {
                 audio.pause();
                 this.audioPlaying = false;
             } else {
                 audio.play();
                 this.audioPlaying = true;
             }
         },
         updateProgress() {
             let audio = this.$refs.bgAudio;
             if(audio && audio.duration) {
                 this.audioProgress = (audio.currentTime / audio.duration) * 100;
             }
         },
         initWaveform() {
             this.updateWaveform();
             window.addEventListener('resize', () => this.updateWaveform());
         },
         updateWaveform() {
             let container = this.$refs.waveformContainer;
             let width = container ? container.clientWidth : 300;
             // Each bar is 3px width + 3px gap = 6px
             let numBars = Math.floor(width / 6);
             if (numBars < 10) numBars = 15;
             let bars = [];
             for(let i = 0; i < numBars; i++) {
                 let factor = Math.sin((i / (numBars - 1 || 1)) * Math.PI);
                 let baseHeight = 6 + Math.floor(factor * 16);
                 let height = baseHeight + (Math.sin(i * 1.8) * 3);
                 bars.push(Math.max(4, Math.min(26, Math.round(height))));
             }
             this.waveformBars = bars;
         }
     }" x-init="initAudio()"
        class="mx-auto h-[100dvh] relative bg-[#ece5dd] dark:bg-[#0b141a] flex flex-col font-sans overflow-hidden">

        @if($page->background_music)
            <audio x-ref="bgAudio" src="{{ Storage::url($page->background_music) }}" @timeupdate="updateProgress()"
                loop></audio>
        @endif

        <!-- INITIAL JOIN POPUP -->
        <div x-show="!joinPopup"
            class="absolute inset-0 z-50 bg-black/60 flex items-center justify-center backdrop-blur-sm transition-opacity duration-300">
            <div
                class="bg-white dark:bg-[#222e35] rounded-xl shadow-2xl p-8 max-w-sm w-full text-center m-4 transition-colors duration-300">
                @if($page->logo)
                    <img src="{{ Storage::url($page->logo) }}" alt="Logo"
                        class="w-24 h-24 rounded-full mx-auto mb-4 border-4 border-emerald-100 dark:border-emerald-900 object-cover shadow-sm">
                @else
                    <div
                        class="w-24 h-24 rounded-full bg-[#075e54] dark:bg-[#202c33] text-white flex items-center justify-center text-3xl mx-auto mb-4 shadow-sm">
                        {{ substr($page->name, 0, 1) }}
                    </div>
                @endif
                <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-2">{{ $page->name }}</h2>
                <p class="text-gray-500 dark:text-gray-400 mb-4 whitespace-pre-wrap text-sm line-clamp-3">{{ $page->description }}</p>

                @if($page->wedding_date)
                    <div class="bg-emerald-50 dark:bg-emerald-950/30 rounded-lg py-2 px-3 mb-4 inline-block">
                        <p class="text-[#075e54] dark:text-emerald-400 font-semibold text-xs">
                            📅 {{ $page->wedding_date->locale('id')->translatedFormat('l, d F Y') }}
                        </p>
                    </div>
                @endif

                <div class="bg-green-50 dark:bg-green-950/20 rounded-lg py-2 mb-6">
                    <p class="text-[#075e54] dark:text-emerald-400 font-medium text-sm">Total:
                        {{ $page->messages()->count() }} pesan terkirim
                    </p>
                </div>
                <button wire:click="joinGroup"
                    class="w-full bg-[#075e54] hover:bg-[#128c7e] dark:bg-[#00a884] dark:hover:bg-[#008f72] text-white font-bold py-3 px-4 rounded-lg transition duration-200 transform hover:scale-105 active:scale-95 shadow-md">
                    Join Event
                </button>
            </div>
        </div>

        <!-- MAIN CHATROOM (Hidden until Joined) -->
        <div x-show="joinPopup" style="display: none;" class="flex flex-col h-full overflow-hidden">

            <!-- HEADER -->
            <div id="header-info" class="bg-[#075e54] dark:bg-[#202c33] text-white px-4 py-3 flex items-center shadow-md cursor-pointer z-20"
                wire:click="togglePageInfo"
                @click="if (driverObj) { driverObj.destroy(); }">
                @if($page->logo)
                    <img src="{{ Storage::url($page->logo) }}" class="w-10 h-10 rounded-full mr-3 object-cover shadow-sm" loading="lazy">
                @else
                    <div
                        class="w-10 h-10 rounded-full bg-gray-300 dark:bg-gray-700 flex items-center justify-center font-bold mr-3 shadow-sm text-gray-600 dark:text-gray-300">
                        {{ substr($page->name, 0, 1) }}
                    </div>
                @endif
                <div class="flex-1">
                    <h1 class="font-bold text-lg leading-tight">{{ $page->name }}</h1>
                    <p class="text-xs text-green-100 dark:text-emerald-400">Tap here for event info</p>
                </div>
            </div>

            <!-- PAGE INFO SLIDE-OVER -->
            <div x-show="showInfo" x-transition:enter="transition ease-out duration-300 transform"
                x-transition:enter-start="-translate-y-full" x-transition:enter-end="translate-y-0"
                x-transition:leave="transition ease-in duration-300 transform" x-transition:leave-start="translate-y-0"
                x-transition:leave-end="-translate-y-full"
                class="absolute inset-0 bg-[#f0f2f5] dark:bg-[#111b21] text-gray-800 dark:text-gray-200 z-40 flex flex-col overflow-y-auto custom-scrollbar"
                style="display:none;">

                <!-- Modal Header -->
                <div
                    class="bg-[#075e54] dark:bg-[#202c33] text-white px-4 py-4 flex items-center sticky top-0 z-50 shadow-md">
                    <button @click="showInfo = false" class="mr-4 hover:opacity-80 transition-opacity">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                    <h2 class="font-bold text-lg">Info Event</h2>
                </div>

                <!-- Modal Content -->
                <div class="flex-1 pb-10 space-y-3 bg-[#f0f2f5] dark:bg-[#111b21]">
                    <!-- Profile Card -->
                    <div class="bg-white dark:bg-[#222e35] p-6 flex flex-col items-center text-center shadow-sm">
                        @if($page->logo)
                            <img src="{{ Storage::url($page->logo) }}" alt="Logo"
                                class="w-32 h-32 rounded-full object-cover shadow-md mb-4 border-4 border-emerald-100 dark:border-emerald-900" loading="lazy">
                        @else
                            <div
                                class="w-32 h-32 rounded-full bg-[#075e54] dark:bg-[#202c33] text-white flex items-center justify-center text-5xl font-bold shadow-md mb-4">
                                {{ substr($page->name, 0, 1) }}
                            </div>
                        @endif
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                            @if($page->bride_name && $page->groom_name)
                                {{ $page->bride_name }} & {{ $page->groom_name }}
                            @else
                                {{ $page->name }}
                            @endif
                        </h3>
                        <p class="text-emerald-600 dark:text-emerald-400 font-semibold mt-1">Grup ·
                            {{ $page->messages()->count() }} Pesan
                        </p>
                        @if($page->wedding_date)
                            <p class="text-gray-500 dark:text-gray-400 text-sm mt-2">
                                <span class="font-bold">Hari/Tanggal : </span>
                                {{ $page->wedding_date->locale('id')->translatedFormat('l, d F Y') }}
                            </p>

                            <!-- Countdown -->
                            <div x-data="{
                                target: new Date('{{ $page->wedding_date->format('Y-m-d') }}T00:00:00').getTime(),
                                days: 0, hours: 0, minutes: 0, seconds: 0,
                                update() {
                                    const now = new Date().getTime();
                                    const dist = this.target - now;
                                    if (dist < 0) {
                                        this.days = 0; this.hours = 0; this.minutes = 0; this.seconds = 0;
                                        return;
                                    }
                                    this.days = Math.floor(dist / (1000 * 60 * 60 * 24));
                                    this.hours = Math.floor((dist % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                    this.minutes = Math.floor((dist % (1000 * 60 * 60)) / (1000 * 60));
                                    this.seconds = Math.floor((dist % (1000 * 60)) / 1000);
                                }
                            }" x-init="update(); setInterval(() => update(), 1000)"
                                class="mt-4 p-3 bg-emerald-50 dark:bg-emerald-950/20 rounded-lg text-center w-full">
                                <div class="flex justify-center gap-4 text-gray-800 dark:text-white">
                                    <div class="flex flex-col items-center min-w-[50px]">
                                        <span class="text-lg font-bold" x-text="days">0</span>
                                        <span class="text-[10px] uppercase text-gray-500 dark:text-gray-400">Hari</span>
                                    </div>
                                    <span class="text-lg font-bold text-emerald-500">:</span>
                                    <div class="flex flex-col items-center min-w-[50px]">
                                        <span class="text-lg font-bold" x-text="hours">0</span>
                                        <span class="text-[10px] uppercase text-gray-555 dark:text-gray-400">Jam</span>
                                    </div>
                                    <span class="text-lg font-bold text-emerald-500">:</span>
                                    <div class="flex flex-col items-center min-w-[50px]">
                                        <span class="text-lg font-bold" x-text="minutes">0</span>
                                        <span class="text-[10px] uppercase text-gray-555 dark:text-gray-400">Menit</span>
                                    </div>
                                    <span class="text-lg font-bold text-emerald-500">:</span>
                                    <div class="flex flex-col items-center min-w-[50px]">
                                        <span class="text-lg font-bold" x-text="seconds">0</span>
                                        <span class="text-[10px] uppercase text-gray-555 dark:text-gray-400">Detik</span>
                                    </div>
                                </div>
                            </div>

                            @php
                                $eventTitle = rawurlencode("Pernikahan " . ($page->bride_name && $page->groom_name ? "{$page->bride_name} & {$page->groom_name}" : $page->name));
                                $startDate = $page->wedding_date->format('Ymd');
                                $endDate = $page->wedding_date->copy()->addDay()->format('Ymd');
                                $details = rawurlencode("Acara Pernikahan:\nAkad: {$page->akad_time} di {$page->akad_location}\nResepsi: {$page->resepsi_time} di {$page->resepsi_location}");
                                $location = rawurlencode($page->resepsi_location ?? $page->akad_location ?? '');
                                $calendarUrl = "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$eventTitle}&dates={$startDate}/{$endDate}&details={$details}&location={$location}";
                            @endphp
                            <a href="{{ $calendarUrl }}" target="_blank" rel="noopener noreferrer"
                                class="mt-3 inline-flex items-center justify-center gap-2 w-full bg-[#075e54] hover:bg-[#128c7e] dark:bg-[#00a884] dark:hover:bg-[#008f72] text-white font-semibold py-2 px-4 rounded-lg transition duration-200 text-sm shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                                Tambahkan Pengingat
                            </a>
                        @endif
                    </div>

                    <!-- Description -->
                    <div class="bg-white dark:bg-[#222e35] p-4 shadow-sm">
                        <h4
                            class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-2">
                            Deskripsi Acara</h4>
                        <p class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $page->description }}
                        </p>
                    </div>

                    <!-- Informasi Mempelai -->
                    <div class="bg-white dark:bg-[#222e35] p-4 shadow-sm">
                        <h4
                            class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-6">
                            Informasi Mempelai
                        </h4>

                        <div class="flex flex-col items-center gap-6">
                            <!-- Mempelai Wanita -->
                            <div class="flex flex-col items-center text-center">
                                @if($page->bride_image)
                                    <img src="{{ Storage::url($page->bride_image) }}"
                                        class="w-24 h-24 rounded-full object-cover shadow-md mb-3 border-4 border-emerald-50 dark:border-emerald-900/50" loading="lazy">
                                @else
                                    <div
                                        class="w-24 h-24 rounded-full bg-pink-100 dark:bg-pink-900/30 text-pink-600 dark:text-pink-400 flex items-center justify-center text-3xl font-bold shadow-md mb-3 border-4 border-pink-50 dark:border-pink-950">
                                        {{ substr($page->bride_name ?? 'W', 0, 1) }}
                                    </div>
                                @endif
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $page->bride_name }}</h3>
                                @if($page->bride_parents)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        Putri dari <span
                                            class="font-medium text-gray-800 dark:text-gray-300">{{ $page->bride_parents }}</span>
                                    </p>
                                @endif
                            </div>

                            <!-- Divider '&' -->
                            <div class="flex items-center justify-center w-full relative py-2">
                                <div class="absolute inset-0 flex items-center">
                                    <div class="w-full border-t border-gray-200 dark:border-gray-700"></div>
                                </div>
                                <div class="relative bg-white dark:bg-[#222e35] px-4">
                                    <span
                                        class="text-2xl font-serif italic text-emerald-600 dark:text-emerald-400">&</span>
                                </div>
                            </div>

                            <!-- Mempelai Pria -->
                            <div class="flex flex-col items-center text-center">
                                @if($page->groom_image)
                                    <img src="{{ Storage::url($page->groom_image) }}"
                                        class="w-24 h-24 rounded-full object-cover shadow-md mb-3 border-4 border-emerald-50 dark:border-emerald-900/50" loading="lazy">
                                @else
                                    <div
                                        class="w-24 h-24 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center text-3xl font-bold shadow-md mb-3 border-4 border-blue-50 dark:border-blue-950">
                                        {{ substr($page->groom_name ?? 'P', 0, 1) }}
                                    </div>
                                @endif
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $page->groom_name }}</h3>
                                @if($page->groom_parents)
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        Putra dari <span
                                            class="font-medium text-gray-800 dark:text-gray-300">{{ $page->groom_parents }}</span>
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Event Details (Akad & Resepsi) -->
                    @if($page->akad_location || $page->resepsi_location)
                        <div class="bg-white dark:bg-[#222e35] p-4 shadow-sm space-y-4">
                            <h4
                                class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-2">
                                Detail Acara Pernikahan</h4>

                            @if($page->akad_location)
                                <div class="flex items-start space-x-3">
                                    <div class="p-2 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400
                                                                                                rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h5 class="font-bold text-sm text-gray-900 dark:text-white">Akad Nikah</h5>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $page->akad_time }}</p>
                                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5">{{ $page->akad_location }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            @if($page->resepsi_location)
                                <div class="flex items-start space-x-3">
                                    <div class="p-2 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400
                                                                                                rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5a2 2 0 10-2 2h2zm0 0H4m8 0h8M4 8a2 2 0 00-2 2v3a2 2 0 002 2h3a3 3 0 003-3V8z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <h5 class="font-bold text-sm text-gray-900 dark:text-white">Resepsi</h5>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $page->resepsi_time }}</p>
                                        <p class="text-xs text-gray-600 dark:text-gray-300 mt-0.5">{{ $page->resepsi_location }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            @if($page->google_maps_url)
                                <a href="{{ $page->google_maps_url }}" target="_blank"
                                    class="mt-2 w-full flex items-center justify-center space-x-2 py-2.5 px-4 bg-[#075e54] hover:bg-[#128c7e] dark:bg-[#00a884]
                                          dark:hover:bg-[#008f72] text-white text-xs font-bold rounded-lg transition shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                    <span>Petunjuk Lokasi (Google Maps)</span>
                                </a>
                            @endif
                        </div>
                    @endif

                    <!-- Love Story Timeline -->
                    @if($page->stories && $page->stories->count() > 0)
                        <div class="bg-white dark:bg-[#222e35] p-4 shadow-sm">
                            <h4
                                class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-4">
                                Perjalanan Cinta Kami</h4>
                            <div class="relative border-l-2 border-emerald-200 dark:border-emerald-900 ml-4 space-y-6 pb-2">
                                @foreach($page->stories as $story)
                                    <div class="relative pl-6">
                                        <!-- Bullet Point -->
                                        <div
                                            class="absolute -left-[9px] top-1.5 w-4 h-4 rounded-full bg-emerald-500 border-4 border-white dark:border-[#222e35]">
                                        </div>
                                        <span
                                            class="inline-block px-2 py-0.5 text-[11px] font-semibold bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 rounded-full mb-1">{{ $story->date_or_year }}</span>
                                        <h5 class="font-bold text-sm text-gray-900 dark:text-white">{{ $story->title }}</h5>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1 whitespace-pre-wrap">{{ $story->description }}</p>
                                        @if($story->image_path)
                                            <div
                                                class="mt-2 w-32 rounded-lg overflow-hidden shadow-sm border border-gray-100 dark:border-gray-800">
                                                <img src="{{ Storage::url($story->image_path) }}" alt="{{ $story->title }}"
                                                    class="w-full h-auto object-cover" loading="lazy">
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Media, Links, Docs (Gallery) -->
                    @if($page->galleries && $page->galleries->count() > 0)
                        <div class="bg-white dark:bg-[#222e35] p-4 shadow-sm"
                            x-init="galleryPhotos = {{ json_encode($page->galleries->map(fn($g) => Storage::url($g->image_path))->toArray()) }}">
                            <div class="flex justify-between items-center mb-3">
                                <h4
                                    class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">
                                    Media, tautan, dan dok</h4>
                                <span
                                    class="text-xs text-gray-500 dark:text-gray-400 font-semibold">{{ $page->galleries->count() }}</span>
                            </div>
                            <div class="flex space-x-2 overflow-x-auto pb-2 custom-scrollbar">
                                @foreach($page->galleries as $index => $gallery)
                                    <div
                                        class="flex-none w-24 h-24 rounded-lg overflow-hidden border border-gray-100
                                                                                                dark:border-gray-850 shadow-sm">
                                        <img src="{{ Storage::url($gallery->image_path) }}" alt="Prewedding"
                                            @click="activePhoto = {{ $index }}"
                                            class="w-full h-full object-cover hover:scale-105 transition duration-200 cursor-pointer" loading="lazy">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Gallery Carousel Modal -->
                        <div x-show="activePhoto !== null" style="display:none;"
                            class="fixed inset-0 bg-black/95 z-[999] flex flex-col justify-between p-4"
                            @keydown.escape.window="activePhoto = null"
                            @keydown.left.window="activePhoto = (activePhoto > 0) ? activePhoto - 1 : galleryPhotos.length - 1"
                            @keydown.right.window="activePhoto = (activePhoto < galleryPhotos.length - 1) ? activePhoto + 1 : 0">

                            <!-- Close Button -->
                            <div class="flex justify-end z-[1000]">
                                <button @click="activePhoto = null"
                                    class="text-white/80 hover:text-white p-2 focus:outline-none transition-colors">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Carousel Area -->
                            <div class="flex-1 flex items-center justify-between gap-4 max-w-lg mx-auto w-full relative">
                                <!-- Prev Button -->
                                <button
                                    @click="activePhoto = (activePhoto > 0) ? activePhoto - 1 : galleryPhotos.length - 1"
                                    class="text-white/70 hover:text-white bg-black/40 hover:bg-black/60 p-2.5 rounded-full transition-colors focus:outline-none select-none">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>

                                <!-- Big Image Container -->
                                <div class="flex-1 h-[70vh] flex items-center justify-center relative select-none">
                                    <template x-for="(imgSrc, index) in galleryPhotos" :key="index">
                                        <img x-show="activePhoto === index" :src="imgSrc"
                                            class="max-w-full max-h-full object-contain rounded-lg shadow-2xl transition-all duration-300 transform"
                                            x-transition:enter="transition ease-out duration-200"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100">
                                    </template>
                                </div>

                                <!-- Next Button -->
                                <button
                                    @click="activePhoto = (activePhoto < galleryPhotos.length - 1) ? activePhoto + 1 : 0"
                                    class="text-white/70 hover:text-white bg-black/40 hover:bg-black/60 p-2.5 rounded-full transition-colors focus:outline-none select-none">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Indicator / Counter -->
                            <div class="text-center text-white/60 text-xs font-semibold mb-4 select-none">
                                <span x-text="(activePhoto + 1)"></span> / <span x-text="galleryPhotos.length"></span>
                            </div>
                        </div>
                    @endif

                    <!-- Donation / Rekening -->
                    @if($page->donations && $page->donations->count() > 0)
                        <div class="bg-white dark:bg-[#222e35] p-4 shadow-sm" x-data="{ copiedIndex: null }">
                            <h4
                                class="text-xs font-bold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider mb-4">
                                Kirim Kado
                            </h4>

                            <div class="grid grid-cols-1 gap-4 max-w-sm mx-auto">
                                @foreach($page->donations as $index => $donation)
                                    <div
                                        class="border border-gray-100 dark:border-gray-800/80 rounded-xl p-4 bg-[#f8f9fa] dark:bg-[#182229]/60 shadow-inner flex flex-col items-center text-center">
                                        <!-- Bank Name -->
                                        <span
                                            class="text-xs font-extrabold px-3 py-1 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 rounded-full mb-2 uppercase tracking-wide">
                                            {{ $donation->bank_name }}
                                        </span>

                                        <!-- Account Name -->
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white leading-tight">
                                            {{ $donation->account_name }}
                                        </p>

                                        <!-- Account Number -->
                                        <p
                                            class="text-xs font-mono text-gray-500 dark:text-gray-400 mt-1 select-all tracking-wider font-bold">
                                            {{ $donation->account_number }}
                                        </p>

                                        <!-- Copy Button -->
                                        <button
                                            @click="navigator.clipboard.writeText('{{ $donation->account_number }}'); copiedIndex = {{ $index }}; setTimeout(() => copiedIndex = null, 2000)"
                                            class="mt-3 w-full flex items-center justify-center gap-1.5 py-1.5 px-4 bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-500/10 dark:hover:bg-emerald-500/20 text-white dark:text-emerald-400 text-xs font-bold rounded-lg transition-all focus:outline-none shadow-sm dark:shadow-none">
                                            <!-- Copy / Success Icon -->
                                            <template x-if="copiedIndex !== {{ $index }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M15.666 3.888A2.25 2.25 0 0013.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 01-.75.75H9a.75.75 0 01-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 01-2.25 2.25H6.75A2.25 2.25 0 014.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 011.927-.184" />
                                                </svg>
                                            </template>
                                            <template x-if="copiedIndex === {{ $index }}">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M4.5 12.75l6 6 9-13.5" />
                                                </svg>
                                            </template>
                                            <span
                                                x-text="copiedIndex === {{ $index }} ? 'Nomor Tersalin!' : 'Salin Nomor'"></span>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- MESSAGES LIST -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3 custom-scrollbar relative bg-[#ece5dd] dark:bg-[#0b141a]"
                id="chat-container" wire:poll.5s>
                <!-- Background overlay with pattern -->
                <div class="absolute inset-0 opacity-[0.06] dark:opacity-[0.04] dark:invert pointer-events-none z-0"
                    style="background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); background-repeat: repeat; background-attachment: local;">
                </div>

                <div class="sticky top-0 z-30 space-y-2 mb-4">
                    <!-- Sticky Welcome Message -->
                    @if($page->content)
                        <div
                            class="bg-white dark:bg-[#202c33] text-gray-900 dark:text-[#e9edef] p-3 rounded-lg text-xs
                                                        shadow-sm border border-gray-200 dark:border-gray-800/40 relative z-10">
                            <!-- Pinned Label -->
                            <div
                                class="flex items-center gap-1.5 text-gray-400 dark:text-gray-500 mb-2 pb-1 border-b border-gray-100 dark:border-gray-800/60">
                                <svg class="w-3.5 h-3.5 transform -rotate-45" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z" />
                                </svg>
                                <span class="text-[9px] font-semibold uppercase tracking-wider">Pesan Disematkan</span>
                            </div>
                            <div class="prose prose-sm dark:prose-invert prose-p:text-xs prose-p:leading-relaxed prose-p:my-0 prose-headings:text-sm prose-headings:my-0 prose-ul:text-xs prose-ul:my-0 prose-li:text-xs prose-li:my-0 prose-a:text-emerald-600 dark:prose-a:text-emerald-400 whitespace-pre-wrap">{!! str($page->content)->markdown() !!}</div>
                        </div>
                    @endif

                    <!-- Sticky Audio Player (VN Style) -->
                    @if($page->background_music)
                        <div class="flex justify-end">
                            <div
                                class="bg-white dark:bg-[#202c33] w-full rounded-lg rounded-tr-none p-3 shadow-sm flex items-center gap-4 relative border border-gray-200 dark:border-gray-800/40">
                                <button @click="toggleAudio()"
                                    class="text-gray-600 dark:text-gray-300 hover:text-gray-800 dark:hover:text-white flex items-center justify-center shrink-0 transition-colors">
                                    <template x-if="!audioPlaying">
                                        <!-- Play Icon -->
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z" />
                                        </svg>
                                    </template>
                                    <template x-if="audioPlaying">
                                        <!-- Pause Icon -->
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z" />
                                        </svg>
                                    </template>
                                </button>

                                <!-- Waveform with Dot -->
                                <div class="flex-1 flex items-center gap-0.5 relative h-8 select-none"
                                    x-ref="waveformContainer">
                                    <!-- Progress Dot -->
                                    <div class="absolute h-3 w-3 rounded-full bg-gray-600 dark:bg-emerald-500 -ml-1.5 top-1/2 -translate-y-1/2 z-10 transition-[left] duration-100 ease-linear"
                                        :style="'left: ' + audioProgress + '%'"></div>

                                    <!-- Static Waveform Bars -->
                                    <div class="w-full flex items-center justify-between gap-[3px]">
                                        <template x-for="(height, index) in waveformBars" :key="index">
                                            <div class="w-[3px] rounded-full transition-colors duration-100"
                                                :class="(index / (waveformBars.length - 1 || 1) * 100) < audioProgress ? 'bg-gray-600 dark:bg-emerald-500' : 'bg-gray-300 dark:bg-gray-700'"
                                                :style="'height: ' + height + 'px'"></div>
                                        </template>
                                    </div>
                                </div>

                                <!-- Triangle Corner Right -->
                                <div class="absolute top-0 -right-2">
                                    <svg width="10" height="10" viewBox="0 0 10 10"
                                        class="fill-current text-white dark:text-[#202c33]">
                                        <path d="M0 0 L10 0 L0 10 Z" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                @foreach($messages as $msg)
                    <div class="flex {{ $msg->sender_name === $senderName ? 'justify-end' : 'justify-start' }} relative z-10"
                        wire:key="msg-{{ $msg->id }}">
                        <div
                            class="max-w-[80%] rounded-lg p-2 shadow-sm relative {{ $msg->sender_name === $senderName ? 'bg-[#dcf8c6] dark:bg-[#005c4b] text-gray-900 dark:text-[#e9edef] rounded-tr-none' : 'bg-white dark:bg-[#202c33] text-gray-900 dark:text-[#e9edef] rounded-tl-none' }}">
                            <p
                                class="text-[10px] font-bold {{ $msg->sender_name === $senderName ? 'text-[#075e54] dark:text-emerald-300' : 'text-blue-600 dark:text-blue-400' }} mb-1">
                                {{ $msg->sender_name }}
                            </p>
                            <div class=" text-xs break-words leading-relaxed markdown-content">
                                {!! str($msg->content)->markdown(['html_input' => 'escape']) !!}
                            </div>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400 text-right mt-1">
                                {{ $msg->created_at->format('H:i') }}
                            </p>

                            <!-- Triangle Corner -->
                            <div class="absolute top-0 {{ $msg->sender_name === $senderName ? '-right-2' : '-left-2' }}">
                                <svg width="10" height="10" viewBox="0 0 10 10"
                                    class="fill-current {{ $msg->sender_name === $senderName ? 'text-[#dcf8c6] dark:text-[#005c4b]' : 'text-white dark:text-[#202c33]' }}">
                                    @if($msg->sender_name === $senderName)
                                        <path d="M0 0 L10 0 L0 10 Z" />
                                    @else
                                        <path d=" M10 0 L0 0 L10 10 Z" />
                                    @endif
                                </svg>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- CHAT INPUT -->
            <div class="bg-[#f0f0f0] dark:bg-[#1f2c34] p-3 border-t border-gray-300 dark:border-gray-850">
                <div class="flex items-center gap-1 sm:gap-2">
                    <!-- EMOJI BUTTON -->
                    <div class="relative shrink-0">
                        <button type="button" @click="openEmoji = !openEmoji"
                            class="w-10 h-10 flex items-center justify-center text-gray-500 dark:text-gray-400 hover:text-[#075e54] dark:hover:text-[#00a884] transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                        </button>
                        <!-- EMOJI PICKER POPUP -->
                        <div x-show="openEmoji" style="display:none;" class="absolute bottom-12 left-0 z-50">
                            <emoji-picker
                                @emoji-click="$wire.set('content', $wire.content + $event.detail.unicode); openEmoji = false; $refs.chatInput.focus()"></emoji-picker>
                        </div>
                    </div>

                    <!-- NAME INPUT -->
                    <div class="w-20 sm:w-36 shrink-0 relative">
                        <input type="text" wire:model="senderName" placeholder="Your Name"
                            class="w-full p-2 rounded-full border border-gray-300 dark:border-gray-700 bg-white dark:bg-[#2a3942] text-gray-800 dark:text-white text-xs focus:outline-none focus:ring-1 focus:ring-[#075e54] dark:focus:ring-[#00a884]">
                        @error('senderName')
                            <span
                                class="absolute -top-6 left-2 bg-red-500 text-white text-[9px] px-2 py-0.5 rounded shadow z-50 whitespace-nowrap">Wajib
                                diisi</span>
                        @enderror
                    </div>

                    <!-- MESSAGE INPUT -->
                    <div
                        class="flex-1 flex items-center bg-white dark:bg-[#2a3942] rounded-full px-4 py-2 shadow-inner">
                        <input type="text" wire:model="content" wire:keydown.enter="sendMessage" x-ref="chatInput"
                            placeholder="Type a message... (Use **bold**, *italic*)"
                            class="flex-1 bg-transparent border-none focus:outline-none text-xs text-gray-800 dark:text-white placeholder-gray-400 dark:placeholder-gray-500">
                    </div>

                    <!-- SEND BUTTON -->
                    <button wire:click="sendMessage"
                        class="bg-[#075e54] dark:bg-[#00a884] text-white w-10 h-10 rounded-full flex items-center justify-center hover:bg-[#128c7e] dark:hover:bg-[#008f72] transition shrink-0 shadow-md"
                        wire:loading.attr="disabled">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="currentColor">
                            <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css">
        <script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
        <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@1/index.js"></script>
        <script> document.addEventListener('livewire:initialized', () => {
                const container = document.getElementById('chat-container');
                let isNearBottom = false;

                if (container) {
                    if (localStorage.getItem('tutorialCompleted')) {
                        container.scrollTop = container.scrollHeight;
                        isNearBottom = true;
                    }
                    container.addEventListener('scroll', () => {
                        isNearBottom = (container.scrollHeight - container.clientHeight - container.scrollTop) < 150;
                    });
                }

                Livewire.hook('morph.updated', ({ component, el }) => {
                    if (localStorage.getItem('tutorialCompleted') && container && isNearBottom) {
                        container.scrollTop = container.scrollHeight;
                    }
                });

                window.addEventListener('scroll-to-bottom', () => {
                    if (localStorage.getItem('tutorialCompleted') && container) {
                        setTimeout(() => {
                            container.scrollTop = container.scrollHeight;
                            isNearBottom = true;
                        }, 50);
                    }
                });

                // Auto Dark Mode Detection and class injection
                const applyTheme = () => {
                    if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                };
                applyTheme();
                window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', applyTheme);
            });
        </script>
        <style>
            .markdown-content p {
                color: inherit;
                margin-bottom: 0.25em;
                margin-top: 0;
            }

            .markdown-content p:last-child {
                margin-bottom: 0;
            }

            .markdown-content strong {
                font-weight: 700;
                color: inherit;
            }

            .markdown-content em {
                font-style: italic;
                color: inherit;
            }

            .markdown-content del {
                text-decoration: line-through;
            }

            .custom-scrollbar::-webkit-scrollbar {
                width: 6px;
            }

            .custom-scrollbar::-webkit-scrollbar-thumb {
                background-color: rgba(0, 0, 0, 0.2);
                border-radius: 10px;
            }

            /* Dark Mode scrollbar styling override */
            .dark .custom-scrollbar::-webkit-scrollbar-thumb {
                background-color: rgba(255, 255, 255, 0.15);
            }

            .driver-popover-close-btn {
                display: block !important;
            }

            /* Make sure the highlighted element is above the SVG overlay so it can be clicked */
            .driver-active-element {
                position: relative;
                z-index: 1000001 !important;
                pointer-events: auto !important;
            }
        </style>
    @endpush
</div>