<x-filament-widgets::widget>
    @php
        $pages = $this->getPages();
    @endphp

    @if(count($pages) > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; width: 100%;">
            @foreach($pages as $page)
                <div style="background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); border: 1px solid #312e81; border-radius: 16px; padding: 24px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3); color: #ffffff; font-family: system-ui, -apple-system, sans-serif; display: flex; flex-direction: column; gap: 16px; position: relative; overflow: hidden;">
                    
                    <!-- Decorative background glow -->
                    <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background: rgba(245, 158, 11, 0.15); filter: blur(40px); border-radius: 50%; pointer-events: none;"></div>

                    <!-- Header -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-b: 1px solid rgba(255, 255, 255, 0.1); padding-bottom: 12px;">
                        <div>
                            <h3 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #fcd34d; letter-spacing: -0.025em;">{{ $page['name'] }}</h3>
                            <p style="margin: 4px 0 0 0; font-size: 0.85rem; color: #94a3b8;">/{{ $page['slug'] }}</p>
                        </div>
                        <span style="background: rgba(245, 158, 11, 0.2); color: #fef08a; font-size: 0.75rem; font-weight: 600; padding: 4px 8px; border-radius: 9999px;">
                            Pernikahan
                        </span>
                    </div>

                    <!-- Date info with small icon -->
                    <div style="display: flex; align-items: center; gap: 8px; font-size: 0.9rem; color: #e2e8f0;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span>{{ Carbon\Carbon::parse($page['wedding_date'])->translatedFormat('l, d F Y') }}</span>
                    </div>

                    <!-- Countdown display -->
                    <div x-data="{
                             weddingDate: new Date('{{ $page['wedding_date'] }}').getTime(),
                             days: '00',
                             hours: '00',
                             minutes: '00',
                             seconds: '00',
                             isExpired: false,
                             updateCountdown() {
                                 const now = new Date().getTime();
                                 const distance = this.weddingDate - now;

                                 if (distance < 0) {
                                     this.isExpired = true;
                                     this.days = '00';
                                     this.hours = '00';
                                     this.minutes = '00';
                                     this.seconds = '00';
                                     return;
                                 }

                                 const d = Math.floor(distance / (1000 * 60 * 60 * 24));
                                 const h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                                 const m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                                 const s = Math.floor((distance % (1000 * 60)) / 1000);

                                 this.days = String(d).padStart(2, '0');
                                 this.hours = String(h).padStart(2, '0');
                                 this.minutes = String(m).padStart(2, '0');
                                 this.seconds = String(s).padStart(2, '0');
                             }
                         }" 
                         x-init="updateCountdown(); setInterval(() => updateCountdown(), 1000)"
                         style="display: flex; align-items: center; justify-content: center; gap: 12px; background: rgba(15, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.05); padding: 16px; border-radius: 12px;">
                        
                        <div style="display: flex; flex-direction: column; align-items: center; min-width: 50px;">
                            <span style="font-size: 1.75rem; font-weight: 900; color: #f59e0b; letter-spacing: -0.05em;" x-text="days">00</span>
                            <span style="font-size: 0.65rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; margin-top: 4px;">Hari</span>
                        </div>
                        <span style="font-size: 1.5rem; font-weight: 700; color: rgba(245, 158, 11, 0.5); align-self: flex-start; margin-top: 2px;">:</span>
                        
                        <div style="display: flex; flex-direction: column; align-items: center; min-width: 50px;">
                            <span style="font-size: 1.75rem; font-weight: 900; color: #f59e0b; letter-spacing: -0.05em;" x-text="hours">00</span>
                            <span style="font-size: 0.65rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; margin-top: 4px;">Jam</span>
                        </div>
                        <span style="font-size: 1.5rem; font-weight: 700; color: rgba(245, 158, 11, 0.5); align-self: flex-start; margin-top: 2px;">:</span>
                        
                        <div style="display: flex; flex-direction: column; align-items: center; min-width: 50px;">
                            <span style="font-size: 1.75rem; font-weight: 900; color: #f59e0b; letter-spacing: -0.05em;" x-text="minutes">00</span>
                            <span style="font-size: 0.65rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; margin-top: 4px;">Menit</span>
                        </div>
                        <span style="font-size: 1.5rem; font-weight: 700; color: rgba(245, 158, 11, 0.5); align-self: flex-start; margin-top: 2px;">:</span>
                        
                        <div style="display: flex; flex-direction: column; align-items: center; min-width: 50px;">
                            <span style="font-size: 1.75rem; font-weight: 900; color: #f59e0b; letter-spacing: -0.05em;" x-text="seconds">00</span>
                            <span style="font-size: 0.65rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; margin-top: 4px;">Detik</span>
                        </div>
                    </div>

                    <!-- Expired warning -->
                    <template x-if="isExpired">
                        <div style="font-size: 0.75rem; color: #ef4444; text-align: center; font-weight: 700; margin-top: 4px; letter-spacing: 0.05em; text-transform: uppercase;">
                            Acara Pernikahan Telah Berlangsung
                        </div>
                    </template>
                </div>
            @endforeach
        </div>
    @endif
</x-filament-widgets::widget>
