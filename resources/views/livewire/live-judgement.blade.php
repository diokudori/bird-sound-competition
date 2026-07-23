<div>
    <!-- Google Fonts -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;700&display=swap');
        .font-outfit { font-family: 'Outfit', sans-serif; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .glass-panel {
            background: rgba(15, 23, 42, 0.45);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .glow-active {
            box-shadow: 0 0 25px rgba(16, 185, 129, 0.25);
        }
        .glow-circle {
            transition: background-color 0.2s ease, box-shadow 0.2s ease;
        }
    </style>

    <div class="min-h-screen bg-slate-950 text-slate-100 font-outfit p-4 lg:p-6"
         x-data="audioJudgeComponent({
            grades: @js($gradesList),
            participantId: @entangle('activeParticipantId'),
            initialScores: @js($activeParticipantScores),
            cctvStatus: @entangle('cctvStatus')
         })"
         x-init="initAudio()">
        
        <!-- Top Navigation / Header -->
        <header class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 pb-4 border-b border-slate-900">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight bg-gradient-to-r from-emerald-400 via-teal-300 to-blue-500 bg-clip-text text-transparent">
                    CHIRP-TRACK AI
                </h1>
                <p class="text-xs text-slate-400 font-mono tracking-widest mt-1">AUTOMATED BIRD SINGING CONTEST JUDGE SYSTEM</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <!-- Excel Import Status & Action -->
                <div class="flex items-center gap-2">
                    <form wire:submit.prevent="importGrades" class="flex items-center gap-2 bg-slate-900/60 p-1.5 rounded-lg border border-slate-800">
                        <input type="file" wire:model="excelFile" id="excelFile" class="hidden">
                        <label for="excelFile" class="cursor-pointer text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 px-3 py-1.5 rounded transition-all">
                            Pilih Excel (.xlsx)
                        </label>
                        <button type="submit" class="text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white px-3 py-1.5 rounded transition-all flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                            Import
                        </button>
                    </form>
                </div>

                <!-- Export PDF Controller Trigger -->
                <a href="{{ route('contest.export-pdf', ['contestId' => $activeContest->id]) }}" 
                   target="_blank"
                   class="bg-gradient-to-r from-red-600 to-rose-500 hover:from-red-500 hover:to-rose-400 text-white font-semibold text-sm px-4 py-2 rounded-lg flex items-center gap-2 shadow-lg hover:shadow-red-900/30 transition-all duration-300">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Cetak Hasil (PDF)
                </a>
            </div>
        </header>

        <!-- Feedback Messages -->
        @if (session()->has('message'))
            <div class="mb-4 p-4 rounded-lg bg-emerald-950/80 border border-emerald-500/30 text-emerald-300 text-sm flex items-center justify-between">
                <span>{{ session('message') }}</span>
                <button @click="$el.parentElement.remove()" class="text-emerald-400 hover:text-emerald-200 font-bold">&times;</button>
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mb-4 p-4 rounded-lg bg-rose-950/80 border border-rose-500/30 text-rose-300 text-sm flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <button @click="$el.parentElement.remove()" class="text-rose-400 hover:text-rose-200 font-bold">&times;</button>
            </div>
        @endif

        <!-- Main Dashboard Layout Grid (2 Columns: 2/3 and 1/3) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- LEFT PANEL: 2/3 Main Judging Dashboard -->
            <div class="lg:col-span-2 flex flex-col gap-6">
                
                <!-- Active Bird Card -->
                @if($activeParticipant)
                <div class="glass-panel rounded-2xl p-6 relative overflow-hidden flex flex-col md:flex-row gap-6 items-center">
                    <!-- CCTV Status indicator line on top -->
                    <div class="absolute top-0 left-0 right-0 h-1 transition-colors duration-500"
                         :class="isCctvActive() ? 'bg-emerald-500 shadow-md shadow-emerald-500' : 'bg-rose-500 shadow-md shadow-rose-500'"></div>
                    
                    <!-- Bird Image / Avatar -->
                    <div class="relative">
                        <div class="w-24 h-24 rounded-full border-2 border-slate-700 overflow-hidden flex items-center justify-center bg-slate-900 shadow-inner">
                            @if($activeParticipant->bird->photo_path)
                                <img src="{{ asset('storage/' . $activeParticipant->bird->photo_path) }}" alt="Bird photo" class="w-full h-full object-cover">
                            @else
                                <svg class="w-12 h-12 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                            @endif
                        </div>
                        <span class="absolute bottom-0 right-0 flex h-5 w-5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                                  :class="isCctvActive() ? 'bg-emerald-400' : 'bg-rose-400'"></span>
                            <span class="relative inline-flex rounded-full h-5 w-5 border border-slate-950 text-[10px] text-white font-bold items-center justify-center font-mono"
                                  :class="isCctvActive() ? 'bg-emerald-500' : 'bg-rose-500'">
                                M
                            </span>
                        </span>
                    </div>

                    <!-- Bird Details -->
                    <div class="flex-1 text-center md:text-left">
                        <div class="flex flex-wrap items-center justify-center md:justify-start gap-2 mb-1">
                            <span class="bg-indigo-950 text-indigo-300 text-xs font-semibold px-2.5 py-0.5 rounded-full border border-indigo-500/20">
                                Gantangan {{ $activeParticipant->gantangan_number }}
                            </span>
                            <span class="bg-slate-800 text-slate-300 text-xs font-semibold px-2.5 py-0.5 rounded-full border border-slate-700">
                                {{ $activeParticipant->bird->bird_type }}
                            </span>
                        </div>
                        <h2 class="text-2xl font-bold tracking-tight text-white">{{ $activeParticipant->bird->name }}</h2>
                        <p class="text-sm text-slate-400 font-mono mt-0.5">{{ $activeParticipant->bird->ring_number }}</p>
                        <p class="text-xs text-slate-500 mt-2">Pemilik: <span class="text-slate-300 font-semibold">{{ $activeParticipant->bird->owner_name }}</span></p>
                    </div>

                    <!-- Dynamic Grade Indicator (Big Color Changing Circle) & Live Score -->
                    <div class="flex flex-row gap-6 items-center bg-slate-900/50 p-4 rounded-xl border border-slate-900 w-full md:w-auto">
                        <!-- Color Circle -->
                        <div class="flex flex-col items-center gap-1">
                            <div class="w-16 h-16 rounded-full border-4 border-slate-950 glow-circle flex items-center justify-center"
                                 :style="'background-color: ' + currentHexColor + '; box-shadow: 0 0 20px ' + currentHexColor">
                                <span class="text-slate-950 font-black text-lg" x-text="currentGradeName.split(' ')[0] || '-'"></span>
                            </div>
                            <span class="text-[10px] text-slate-400 font-mono mt-1" x-text="detectedFreq > 0 ? detectedFreq.toFixed(0) + ' Hz' : 'No Signal'"></span>
                        </div>

                        <!-- Live Score display -->
                        <div class="flex-1 md:w-36 text-center md:text-right">
                            <div class="text-[10px] tracking-widest text-slate-400 font-mono uppercase">TOTAL SKOR</div>
                            <div class="text-4xl font-black text-emerald-400 font-mono tracking-tight glow-active mt-1">
                                {{ $activeParticipant->total_score }}
                            </div>
                            <button wire:click="resetScores" 
                                    class="text-[10px] text-rose-400 hover:text-rose-300 transition-colors mt-2 underline"
                                    @click="clearChart()">
                                Reset Skor
                            </button>
                        </div>
                    </div>
                </div>
                @else
                <div class="glass-panel rounded-2xl p-8 text-center text-slate-400">
                    Pilih burung peserta di sidebar kanan untuk memulai penilaian.
                </div>
                @endif

                <!-- Audio Visualizer (Live Waves) -->
                <div class="glass-panel rounded-2xl p-5">
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-sm font-semibold tracking-wider text-slate-300 uppercase">Live Audio Feed</span>
                        <div class="flex items-center gap-2">
                            <div class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></div>
                            <span class="text-[11px] font-mono text-slate-400" x-text="micStatus">Menunggu Mic...</span>
                        </div>
                    </div>
                    
                    <!-- Canvas for Audio Waveform -->
                    <div class="w-full bg-slate-950 rounded-xl border border-slate-900/60 overflow-hidden relative">
                        <canvas id="audioWaveCanvas" class="w-full h-24 block"></canvas>
                        <!-- Threshhold Indicator Line -->
                        <div class="absolute w-full border-t border-dashed border-emerald-500/20 pointer-events-none" style="top: 75%;"></div>
                    </div>
                </div>

                <!-- Scatter Plot Visualizer (Sumbu X: Durasi, Sumbu Y: Grade) -->
                <div class="glass-panel rounded-2xl p-5">
                    <div class="flex justify-between items-center mb-4">
                        <div>
                            <h3 class="text-md font-semibold tracking-wider text-slate-200 uppercase">Grafik Analisis Kicauan</h3>
                            <p class="text-xs text-slate-500">Nilai persilangan Durasi (X) & Grade Frekuensi (Y)</p>
                        </div>
                        
                        <div class="flex gap-4 text-xs font-mono">
                            <span class="flex items-center gap-1.5 text-slate-300">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 inline-block"></span>
                                Aktif (CCTV)
                            </span>
                            <span class="flex items-center gap-1.5 text-slate-500">
                                <span class="w-2.5 h-2.5 rounded-full bg-rose-500 inline-block"></span>
                                Diam/Gantangan Kosong
                            </span>
                        </div>
                    </div>

                    <!-- Canvas for Scatter Plot -->
                    <div class="w-full bg-slate-950 rounded-xl border border-slate-900/60 overflow-hidden p-2">
                        <canvas id="scatterPlotCanvas" class="w-full h-80 block"></canvas>
                    </div>
                </div>

            </div>

            <!-- RIGHT PANEL: 1/3 Leaderboard & Contestants -->
            <div class="flex flex-col gap-6">

                <!-- CCTV Tracking Settings Control Panel (Simulated Panel) -->
                <div class="glass-panel rounded-2xl p-5">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="text-sm font-semibold tracking-wider text-slate-200 uppercase">CCTV Motion Tracker</h3>
                        <span class="text-[10px] text-indigo-400 font-mono tracking-widest uppercase">Local Host Feed</span>
                    </div>
                    
                    <p class="text-xs text-slate-400 mb-4 leading-relaxed">
                        Simulasi status sensor gerak CCTV untuk masing-masing gantangan. Jika CCTV mendeteksi burung diam, skor tidak akan bertambah.
                    </p>

                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-2 gap-2">
                        @foreach($participants as $part)
                        <button wire:click="toggleCctv({{ $part->id }})"
                                class="p-2.5 rounded-xl border flex items-center justify-between transition-all"
                                :class="cctvStatus[{{ $part->id }}] 
                                    ? 'bg-emerald-950/30 border-emerald-500/40 text-emerald-300' 
                                    : 'bg-rose-950/30 border-rose-500/40 text-rose-300'">
                            <div class="flex flex-col items-start">
                                <span class="text-[10px] font-mono opacity-80">GT-{{ $part->gantangan_number }}</span>
                                <span class="text-xs font-semibold truncate max-w-[80px]">{{ $part->bird->name }}</span>
                            </div>
                            <span class="w-2 h-2 rounded-full"
                                  :class="cctvStatus[{{ $part->id }}] ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400'"></span>
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- Leaderboard Sidebar Card -->
                <div class="glass-panel rounded-2xl overflow-hidden flex flex-col flex-1">
                    <div class="p-5 border-b border-slate-900 bg-slate-900/30 flex justify-between items-center">
                        <div>
                            <h3 class="text-sm font-bold tracking-wider text-slate-200 uppercase">LEADERBOARD</h3>
                            <p class="text-[10px] text-slate-400 font-mono mt-0.5">KONTES TERKINI secara Live</p>
                        </div>
                        <span class="animate-pulse bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 text-[10px] px-2 py-0.5 rounded-md font-mono">
                            LIVE
                        </span>
                    </div>

                    <!-- Participant List table -->
                    <div class="p-2 overflow-y-auto max-h-[350px] lg:max-h-none flex-1">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="text-[10px] text-slate-500 border-b border-slate-900/60 uppercase">
                                    <th class="py-2 px-3">Gtg</th>
                                    <th class="py-2 px-2">Burung</th>
                                    <th class="py-2 px-2 text-right">Skor</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-900/40">
                                @forelse($leaderboard as $index => $row)
                                <tr class="hover:bg-slate-900/40 transition-colors group cursor-pointer"
                                    wire:click="selectParticipant({{ $row['id'] }})"
                                    :class="{{ $activeParticipantId }} == {{ $row['id'] }} ? 'bg-slate-900/80 border-l-2 border-emerald-500' : ''">
                                    
                                    <!-- Gantangan & Rank Badge -->
                                    <td class="py-3 px-3">
                                        <div class="flex items-center gap-2">
                                            @if($index == 0)
                                                <span class="w-5 h-5 rounded-full bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 flex items-center justify-center font-bold text-[10px]" title="Juara 1">1</span>
                                            @elseif($index == 1)
                                                <span class="w-5 h-5 rounded-full bg-slate-300/20 text-slate-300 border border-slate-300/30 flex items-center justify-center font-bold text-[10px]" title="Juara 2">2</span>
                                            @elseif($index == 2)
                                                <span class="w-5 h-5 rounded-full bg-amber-700/20 text-amber-500 border border-amber-500/30 flex items-center justify-center font-bold text-[10px]" title="Juara 3">3</span>
                                            @else
                                                <span class="w-5 h-5 flex items-center justify-center text-slate-500 font-semibold">{{ $index + 1 }}</span>
                                            @endif
                                            <span class="text-[10px] text-slate-400 font-mono">#{{ $row['gantangan'] }}</span>
                                        </div>
                                    </td>

                                    <!-- Bird & Ring foot -->
                                    <td class="py-3 px-2">
                                        <div class="font-bold text-slate-200 group-hover:text-white transition-colors truncate max-w-[130px]">
                                            {{ $row['name'] }}
                                        </div>
                                        <div class="text-[10px] text-slate-500 font-mono truncate max-w-[130px]">
                                            {{ $row['ring_number'] }}
                                        </div>
                                    </td>

                                    <!-- Total Score -->
                                    <td class="py-3 px-2 text-right">
                                        <span class="font-mono font-bold text-slate-100 group-hover:text-emerald-400 transition-colors">
                                            {{ $row['total_score'] }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-slate-500">
                                        Belum ada data peserta.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>

    </div>

    <!-- Alpine JS Audio Processing Engine -->
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('audioJudgeComponent', (config) => ({
                grades: config.grades,
                participantId: config.participantId,
                initialScores: config.initialScores,
                cctvStatus: config.cctvStatus,
                
                // Mic State
                micStatus: 'OFF',
                audioCtx: null,
                analyser: null,
                source: null,
                animationFrameId: null,
                
                // Pitch Detection State
                detectedFreq: 0,
                currentHexColor: '#475569',
                currentGradeName: 'Scanning',
                currentGradeScore: 0,
                
                // Audio thresholds & counting duration
                volumeThreshold: 0.015,  // Threshold for bird singing
                singingActive: false,
                singStartTime: null,
                lastSingTime: null,
                maxSilenceGapMs: 600,   // Silence duration before we log a score point
                pitchHistory: [],       // Stores pitch values during one phrase

                // Graphics State
                waveCanvas: null,
                waveCtx: null,
                scatterCanvas: null,
                scatterCtx: null,
                scoresData: [],

                init() {
                    this.scoresData = this.initialScores.map(s => ({
                        x: s.x,
                        y: s.y,
                        freq: s.freq,
                        active: s.active
                    }));

                    // Setup canvases after DOM rendering
                    setTimeout(() => {
                        this.setupCanvases();
                        this.drawScatterPlot();
                    }, 300);

                    // Re-draw chart on window resize
                    window.addEventListener('resize', () => {
                        this.setupCanvases();
                        this.drawScatterPlot();
                    });

                    // Listen for Livewire updates or Excel imports
                    this.$watch('initialScores', (value) => {
                        this.scoresData = value.map(s => ({
                            x: s.x,
                            y: s.y,
                            freq: s.freq,
                            active: s.active
                        }));
                        this.drawScatterPlot();
                    });

                    // Livewire grades update listener
                    window.addEventListener('grades-updated', (event) => {
                        this.grades = event.detail.grades;
                    });
                },

                setupCanvases() {
                    this.waveCanvas = document.getElementById('audioWaveCanvas');
                    if (this.waveCanvas) {
                        this.waveCtx = this.waveCanvas.getContext('2d');
                        this.waveCanvas.width = this.waveCanvas.parentElement.clientWidth;
                        this.waveCanvas.height = 96;
                    }

                    this.scatterCanvas = document.getElementById('scatterPlotCanvas');
                    if (this.scatterCanvas) {
                        this.scatterCtx = this.scatterCanvas.getContext('2d');
                        // Calculate responsive sizing
                        this.scatterCanvas.width = this.scatterCanvas.parentElement.clientWidth;
                        this.scatterCanvas.height = 320;
                    }
                },

                isCctvActive() {
                    return this.cctvStatus[this.participantId] ?? true;
                },

                async initAudio() {
                    try {
                        const stream = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
                        this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                        this.analyser = this.audioCtx.createAnalyser();
                        this.analyser.fftSize = 2048;
                        
                        this.source = this.audioCtx.createMediaStreamSource(stream);
                        this.source.connect(this.analyser);
                        
                        this.micStatus = 'ON (Active)';
                        this.processAudio();
                    } catch (err) {
                        console.error('Error accessing microphone:', err);
                        this.micStatus = 'Error/Ditolak';
                    }
                },

                processAudio() {
                    const bufferLength = this.analyser.frequencyBinCount;
                    const dataArray = new Float32Array(bufferLength);
                    
                    const draw = () => {
                        this.animationFrameId = requestAnimationFrame(draw);
                        
                        // 1. Get raw PCM waveform data
                        this.analyser.getFloatTimeDomainData(dataArray);
                        
                        // 2. Draw live waveform visualizer
                        this.drawWaveform(dataArray, bufferLength);
                        
                        // 3. Compute pitch & volume
                        let volume = this.getRMS(dataArray);
                        let pitch = this.autoCorrelate(dataArray, this.audioCtx.sampleRate);
                        
                        if (volume > this.volumeThreshold && pitch > 0) {
                            this.detectedFreq = pitch;
                            this.matchGrade(pitch);
                            
                            // Bird has started singing or continues singing
                            if (!this.singingActive) {
                                this.singingActive = true;
                                this.singStartTime = Date.now();
                                this.pitchHistory = [pitch];
                            } else {
                                this.pitchHistory.push(pitch);
                            }
                            this.lastSingTime = Date.now();
                        } else {
                            // Silence / Noise floor
                            this.detectedFreq = 0;
                            
                            // Check if a phrase has ended (exceeded maximum silence gap)
                            if (this.singingActive && (Date.now() - this.lastSingTime > this.maxSilenceGapMs)) {
                                this.singingActive = false;
                                this.logPhraseScore();
                            }
                        }
                    };
                    
                    draw();
                },

                getRMS(buffer) {
                    let sum = 0;
                    for (let i = 0; i < buffer.length; i++) {
                        sum += buffer[i] * buffer[i];
                    }
                    return Math.sqrt(sum / buffer.length);
                },

                // Autocorrelation Pitch Detection Algorithm
                autoCorrelate(buffer, sampleRate) {
                    let SIZE = buffer.length;
                    let rms = this.getRMS(buffer);
                    
                    // Signal is too quiet
                    if (rms < this.volumeThreshold) return -1;
                    
                    // Pitch selection criteria (clipping peaks)
                    let r1 = 0, r2 = SIZE - 1;
                    let thres = 0.2;
                    for (let i = 0; i < SIZE / 2; i++) {
                        if (Math.abs(buffer[i]) < thres) { r1 = i; break; }
                    }
                    for (let i = SIZE - 1; i >= SIZE / 2; i--) {
                        if (Math.abs(buffer[i]) < thres) { r2 = i; break; }
                    }
                    buffer = buffer.slice(r1, r2);
                    SIZE = buffer.length;

                    let c = new Float32Array(SIZE);
                    for (let i = 0; i < SIZE; i++) {
                        for (let j = 0; j < SIZE - i; j++) {
                            c[i] = c[i] + buffer[j] * buffer[j + i];
                        }
                    }
                    let d = 0;
                    while (c[d] > c[d + 1]) d++;
                    let maxval = -1, maxpos = -1;
                    for (let i = d; i < SIZE; i++) {
                        if (c[i] > maxval) {
                            maxval = c[i];
                            maxpos = i;
                        }
                    }
                    
                    let T0 = maxpos;
                    let freq = sampleRate / T0;
                    
                    // Bird sounds are high-pitched (filter outliers outside 800Hz - 12kHz)
                    if (freq > 800 && freq < 12000) {
                        return freq;
                    }
                    return -1;
                },

                matchGrade(frequency) {
                    let matched = null;
                    for (let g of this.grades) {
                        if (frequency >= g.freq_min && frequency <= g.freq_max) {
                            matched = g;
                            break;
                        }
                    }

                    if (matched) {
                        this.currentHexColor = matched.hex_color;
                        this.currentGradeName = matched.grade_name;
                        this.currentGradeScore = matched.score;
                    } else {
                        // Fallback or out of range
                        this.currentHexColor = '#475569';
                        this.currentGradeName = 'Out of Grade';
                        this.currentGradeScore = 0;
                    }
                },

                logPhraseScore() {
                    if (this.pitchHistory.length === 0) return;

                    const durationSec = (Date.now() - this.singStartTime - this.maxSilenceGapMs) / 1000;
                    
                    // Only score sounds longer than 0.3 seconds to filter out pop noises
                    if (durationSec > 0.3) {
                        // Calculate average pitch of the singing phrase
                        const avgPitch = this.pitchHistory.reduce((a, b) => a + b, 0) / this.pitchHistory.length;
                        this.matchGrade(avgPitch);

                        const gradeScore = this.currentGradeScore;
                        // Score calculation: duration (consecutive seconds) * grade difficulty score
                        // Long continuous song phrase = Higher Score
                        const calculatedScore = Math.round(durationSec * gradeScore);

                        if (gradeScore > 0) {
                            const trackingActive = this.isCctvActive();
                            
                            // Push to UI local memory first
                            this.scoresData.push({
                                x: durationSec,
                                y: gradeScore,
                                freq: avgPitch,
                                active: trackingActive
                            });

                            // Push to Laravel DB using Livewire
                            this.$wire.call('recordScorePoint', durationSec, avgPitch, gradeScore, calculatedScore);
                            
                            // Re-draw chart
                            this.drawScatterPlot();
                        }
                    }
                    this.pitchHistory = [];
                },

                clearChart() {
                    this.scoresData = [];
                    setTimeout(() => this.drawScatterPlot(), 50);
                },

                drawWaveform(dataArray, bufferLength) {
                    if (!this.waveCtx || !this.waveCanvas) return;
                    
                    const ctx = this.waveCtx;
                    const width = this.waveCanvas.width;
                    const height = this.waveCanvas.height;
                    
                    ctx.clearRect(0, 0, width, height);
                    
                    // Waveform gradient color
                    let gradient = ctx.createLinearGradient(0, 0, width, 0);
                    gradient.addColorStop(0, '#10b981');
                    gradient.addColorStop(0.5, '#06b6d4');
                    gradient.addColorStop(1, '#3b82f6');
                    
                    ctx.lineWidth = 2.5;
                    ctx.strokeStyle = gradient;
                    ctx.beginPath();
                    
                    const sliceWidth = width / bufferLength;
                    let x = 0;
                    
                    for (let i = 0; i < bufferLength; i++) {
                        const v = dataArray[i] * 2; // Amplify wave form visually
                        const y = (v * height / 2) + height / 2;
                        
                        if (i === 0) {
                            ctx.moveTo(x, y);
                        } else {
                            ctx.lineTo(x, y);
                        }
                        
                        x += sliceWidth;
                    }
                    
                    ctx.lineTo(width, height / 2);
                    ctx.stroke();
                },

                drawScatterPlot() {
                    if (!this.scatterCtx || !this.scatterCanvas) return;
                    
                    const ctx = this.scatterCtx;
                    const width = this.scatterCanvas.width;
                    const height = this.scatterCanvas.height;
                    
                    ctx.clearRect(0, 0, width, height);

                    // Margins for axes labels
                    const padding = { top: 30, right: 30, bottom: 45, left: 55 };
                    const graphWidth = width - padding.left - padding.right;
                    const graphHeight = height - padding.top - padding.bottom;

                    // Plot boundaries
                    // X represents duration (0 to 10 seconds range)
                    const xMax = 10;
                    // Y represents grade score weight (50 to 100 range)
                    const yMin = 40;
                    const yMax = 110;

                    // Helper to translate data point (x, y) to canvas coordinates
                    const getCanvasX = (valX) => padding.left + (Math.min(valX, xMax) / xMax) * graphWidth;
                    const getCanvasY = (valY) => padding.top + graphHeight - ((valY - yMin) / (yMax - yMin)) * graphHeight;

                    // 1. Draw Grid Lines (Horizontal & Vertical)
                    ctx.strokeStyle = 'rgba(255, 255, 255, 0.05)';
                    ctx.lineWidth = 1;
                    ctx.fillStyle = 'rgba(255, 255, 255, 0.4)';
                    ctx.font = '10px JetBrains Mono';
                    
                    // Vertical grids (Duration on X)
                    for (let i = 0; i <= xMax; i += 2) {
                        let gx = getCanvasX(i);
                        ctx.beginPath();
                        ctx.moveTo(gx, padding.top);
                        ctx.lineTo(gx, padding.top + graphHeight);
                        ctx.stroke();
                        
                        // X Labels
                        ctx.textAlign = 'center';
                        ctx.fillText(i + 's', gx, padding.top + graphHeight + 18);
                    }

                    // Horizontal grids (Grades Score weight on Y)
                    for (let i = yMin + 10; i <= yMax - 10; i += 10) {
                        let gy = getCanvasY(i);
                        ctx.beginPath();
                        ctx.moveTo(padding.left, gy);
                        ctx.lineTo(padding.left + graphWidth, gy);
                        ctx.stroke();
                        
                        // Y Labels
                        ctx.textAlign = 'right';
                        ctx.fillText(i, padding.left - 12, gy + 4);
                    }

                    // Axes titles
                    ctx.fillStyle = '#94a3b8';
                    ctx.font = '11px Outfit';
                    ctx.textAlign = 'center';
                    ctx.fillText('Panjang / Durasi Kicauan (Detik)', padding.left + graphWidth/2, padding.top + graphHeight + 35);
                    
                    ctx.save();
                    ctx.translate(18, padding.top + graphHeight/2);
                    ctx.rotate(-Math.PI / 2);
                    ctx.fillText('Grade Suara (Threshold Nilai)', 0, 0);
                    ctx.restore();

                    // Outer border line
                    ctx.strokeStyle = 'rgba(255, 255, 255, 0.1)';
                    ctx.strokeRect(padding.left, padding.top, graphWidth, graphHeight);

                    // 2. Draw Data Points (Scatter Plot)
                    this.scoresData.forEach((pt) => {
                        let cx = getCanvasX(pt.x);
                        let cy = getCanvasY(pt.y);

                        // Colors based on CCTV active/inactive state
                        let pointColor = pt.active ? '#10b981' : '#f43f5e';
                        let glowColor = pt.active ? 'rgba(16, 185, 129, 0.4)' : 'rgba(244, 63, 94, 0.4)';

                        // Draw outer glow circle
                        ctx.fillStyle = glowColor;
                        ctx.beginPath();
                        ctx.arc(cx, cy, 9, 0, 2 * Math.PI);
                        ctx.fill();

                        // Draw solid center circle
                        ctx.fillStyle = pointColor;
                        ctx.beginPath();
                        ctx.arc(cx, cy, 5, 0, 2 * Math.PI);
                        ctx.fill();

                        // Connect line or highlight if active (for aesthetic visualization)
                        ctx.strokeStyle = pointColor;
                        ctx.lineWidth = 0.5;
                        ctx.beginPath();
                        ctx.arc(cx, cy, 14, 0, 2 * Math.PI);
                        ctx.stroke();
                    });
                }
            }));
        });
    </script>
</div>
