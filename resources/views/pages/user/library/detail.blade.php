<x-user-layout>
    @section('title', $library->title)

    <div class="px-4 sm:px-6 lg:px-8 py-8 md:py-0 w-full max-w-[96rem] mx-auto">
        <div class="xl:flex">
             <div class="md:flex flex-1">
                <x-community.feed-left-content />

                <div class="flex-1 md:ml-8 xl:mx-4 2xl:mx-8">
                    <div class="md:py-8">
                        <div class="mb-4">
                            <a href="{{ route('pustaka-list') }}" class="text-sm font-medium text-indigo-500 hover:text-indigo-600 flex items-center">
                                <svg class="w-3 h-3 fill-current mr-2" viewBox="0 0 12 12">
                                    <path d="M5.4 10.6L.8 6l4.6-4.6L6.8 2.8 3.6 6l3.2 3.2z" />
                                </svg>
                                <span>Kembali ke Pustaka</span>
                            </a>
                        </div>

                        <article class="bg-white dark:bg-gray-800 p-6 shadow-md rounded-xl border border-gray-100 dark:border-gray-700/60">
                            <div class="flex flex-col md:flex-row gap-8">
                                <!-- Cover -->
                                <div class="w-full md:w-1/3 flex-shrink-0">
                                    @if($library->cover_image)
                                        <img class="w-full rounded-lg shadow-lg" src="{{ Storage::url($library->cover_image) }}" alt="{{ $library->title }}">
                                    @else
                                        <div class="w-full aspect-[3/4] bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-400 rounded-lg">
                                             <svg class="w-16 h-16 fill-current" viewBox="0 0 24 24">
                                                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                                            </svg>
                                        </div>
                                    @endif

                                    <div class="mt-6">
                                        @if($library->file_path)
                                            @auth
                                                <a href="{{ Storage::url($library->file_path) }}" target="_blank" class="btn w-full bg-indigo-500 hover:bg-indigo-600 text-white">
                                                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0 mr-2" viewBox="0 0 16 16">
                                                        <path d="M15 15H1a1 1 0 01-1-1V2a1 1 0 011-1h4v2H2v10h12V3h-3V1h4a1 1 0 011 1v12a1 1 0 01-1 1zM9 7h3l-4 4-4-4h3V1h2v6z" />
                                                    </svg>
                                                    <span>Download / Baca PDF</span>
                                                </a>
                                            @else
                                                <a href="{{ route('login') }}" class="btn w-full bg-indigo-500 hover:bg-indigo-600 text-white">
                                                    <svg class="w-4 h-4 fill-current opacity-50 shrink-0 mr-2" viewBox="0 0 24 24">
                                                        <path d="M12 2C9.243 2 7 4.243 7 7v3H6c-1.103 0-2 .897-2 2v8c0 1.103.897 2 2 2h12c1.103 0 2-.897 2-2v-8c0-1.103-.897-2-2-2h-1V7c0-2.757-2.243-5-5-5zm6 10v8H6v-8h12zm-9-2V7c0-1.654 1.346-3 3-3s3 1.346 3 3v3H9z"/>
                                                    </svg>
                                                    <span>Login untuk Baca PDF</span>
                                                </a>
                                            @endauth
                                        @else
                                            <button disabled class="btn w-full bg-gray-200 dark:bg-gray-700 text-gray-400 cursor-not-allowed">
                                                File Tidak Tersedia
                                            </button>
                                        @endif
                                    </div>
                                </div>

                                <!-- Details -->
                                <div class="flex-1">
                                    <div class="mb-4">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800 dark:bg-indigo-500/30 dark:text-indigo-200">
                                                {{ $library->category }}
                                            </span>
                                             <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $library->price_type == 'free' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                                {{ $library->price_type == 'free' ? 'Gratis' : 'Berbayar' }}
                                            </span>
                                        </div>
                                        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100 mb-4">{{ $library->title }}</h1>

                                        <div class="prose dark:prose-invert max-w-none text-gray-600 dark:text-gray-400">
                                            {!! nl2br(e($library->description)) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($library->episodes->count() > 0)
                                <div class="mt-8 border-t border-gray-100 dark:border-gray-700/60 pt-8"
                                    x-data="{
                                        currentEpisodeIndex: 0,
                                        episodes: {{ Js::from($library->episodes->map(fn($e) => ['title' => $e->title, 'url' => Storage::url($e->file_path)])) }},
                                        isPlaying: false,
                                        audio: null,
                                        init() {
                                            this.audio = this.$refs.audioPlayer;
                                            // Don't auto-load src to avoid pre-loading all if multiple players on page, but here we have one.
                                            if(this.episodes.length > 0) {
                                                this.audio.src = this.episodes[0].url;
                                            }
                                        },
                                        playEpisode(index) {
                                            if (this.currentEpisodeIndex === index) {
                                                if (this.audio.paused) {
                                                    this.audio.play();
                                                    this.isPlaying = true;
                                                } else {
                                                    this.audio.pause();
                                                    this.isPlaying = false;
                                                }
                                            } else {
                                                this.currentEpisodeIndex = index;
                                                this.audio.src = this.episodes[index].url;
                                                this.audio.play();
                                                this.isPlaying = true;
                                            }
                                        },
                                        togglePlay() {
                                            if (this.audio.paused) {
                                                this.audio.play();
                                                this.isPlaying = true;
                                            } else {
                                                this.audio.pause();
                                                this.isPlaying = false;
                                            }
                                        }
                                    }">

                                    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">Podcast Episodes</h2>

                                    @auth
                                        <!-- Player Control -->
                                        <div class="mb-6 bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm">
                                            <div class="flex items-center gap-4">
                                                <button @click="togglePlay()" class="w-12 h-12 flex items-center justify-center rounded-full bg-indigo-500 hover:bg-indigo-600 text-white transition-colors shrink-0">
                                                    <svg x-show="!isPlaying" class="w-5 h-5 ml-1 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                    <svg x-show="isPlaying" class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/></svg>
                                                </button>
                                                <div class="flex-1 overflow-hidden">
                                                    <p class="text-sm text-indigo-500 font-medium mb-1">Now Playing</p>
                                                    <p class="text-gray-900 dark:text-gray-100 font-bold truncate" x-text="episodes[currentEpisodeIndex].title"></p>
                                                </div>
                                            </div>
                                            <audio x-ref="audioPlayer" @play="isPlaying = true" @pause="isPlaying = false" @ended="isPlaying = false" controls class="w-full mt-4"></audio>
                                        </div>

                                        <!-- Playlist -->
                                        <div class="space-y-2 max-h-96 overflow-y-auto">
                                            <template x-for="(episode, index) in episodes" :key="index">
                                                <div
                                                    @click="playEpisode(index)"
                                                    class="flex items-center justify-between p-3 rounded-lg cursor-pointer transition-colors"
                                                    :class="currentEpisodeIndex === index ? 'bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-100 dark:border-indigo-800' : 'hover:bg-gray-50 dark:hover:bg-gray-800 border border-transparent'"
                                                >
                                                    <div class="flex items-center gap-3 overflow-hidden">
                                                        <span class="w-8 h-8 flex items-center justify-center rounded-full text-xs font-medium shrink-0"
                                                            :class="currentEpisodeIndex === index ? 'bg-indigo-500 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-500'">
                                                            <span x-text="index + 1"></span>
                                                        </span>
                                                        <span class="font-medium text-gray-800 dark:text-gray-200 truncate" x-text="episode.title"></span>
                                                    </div>
                                                    <div x-show="currentEpisodeIndex === index && isPlaying">
                                                        <span class="flex h-3 w-3 relative">
                                                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                                            <span class="relative inline-flex rounded-full h-3 w-3 bg-indigo-500"></span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    @else
                                        <!-- Auth Wall -->
                                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-8 text-center border border-gray-100 dark:border-gray-700">
                                            <div class="mb-4">
                                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                                                </svg>
                                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Dengarkan Podcast</h3>
                                                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 max-w-sm mx-auto">Login untuk mendengarkan {{ $library->episodes->count() }} episode podcast ini.</p>
                                            </div>
                                            <a href="{{ route('login') }}" class="btn bg-indigo-500 hover:bg-indigo-600 text-white w-full sm:w-auto px-6 py-2.5 rounded-lg font-medium transition-colors duration-200">
                                                Login Sekarang
                                            </a>
                                        </div>
                                    @endauth
                                </div>
                            @elseif($library->podcast_audio_path)
                                <div class="mt-8 border-t border-gray-100 dark:border-gray-700/60 pt-8" x-data="{ activeTab: 'outline' }">
                                    <h2 class="text-xl font-bold text-gray-800 dark:text-gray-100 mb-4">Podcast AI</h2>

                                    @auth
                                        <!-- Audio Player -->
                                        <div class="mb-6">
                                            <audio controls class="w-full rounded-lg">
                                                <source src="{{ Storage::url($library->podcast_audio_path) }}" type="audio/mpeg">
                                                Browser Anda tidak mendukung elemen audio.
                                            </audio>
                                        </div>

                                        <!-- Tabs -->
                                        <div class="flex border-b border-gray-200 dark:border-gray-700 mb-4">
                                            <button
                                                @click="activeTab = 'outline'"
                                                :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'outline', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'outline' }"
                                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm mr-8 focus:outline-none"
                                            >
                                                Outline
                                            </button>
                                            <button
                                                @click="activeTab = 'transcript'"
                                                :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'transcript', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300': activeTab !== 'transcript' }"
                                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm focus:outline-none"
                                            >
                                                Transcript
                                            </button>
                                        </div>

                                        <!-- Content -->
                                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-4 max-h-96 overflow-y-auto">
                                            <!-- Outline -->
                                            <div x-show="activeTab === 'outline'">
                                                @if(isset($library->podcast_metadata['outline']) && is_array($library->podcast_metadata['outline']))
                                                    <ul class="space-y-4">
                                                        @foreach($library->podcast_metadata['outline'] as $item)
                                                            <div class="flex flex-col gap-1">
                                                                <div class="flex items-center gap-2">
                                                                <span class="text-indigo-500 font-mono text-sm shrink-0">{{ $item['name'] }}</span>
                                                            </div>
                                                            <p class="text-gray-600 dark:text-gray-400">{{ $item['description'] }}</p>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <p class="text-gray-500 italic">Outline tidak tersedia.</p>
                                                @endif
                                            </div>

                                            <!-- Transcript -->
                                            <div x-show="activeTab === 'transcript'">
                                                @if(isset($library->podcast_metadata['transcript']) && is_array($library->podcast_metadata['transcript']))
                                                    <div class="space-y-4">
                                                        @foreach($library->podcast_metadata['transcript'] as $segment)
                                                            <div class="flex flex-col gap-1">
                                                                <div class="flex items-center gap-2">
                                                                    <span class="font-semibold text-gray-800 dark:text-gray-200 text-sm">{{ $segment['speaker'] ?? 'Speaker' }}</span>
                                                                    {{-- <span class="text-xs text-gray-500">{{ $segment['dialogue'] ?? '' }}</span> --}}
                                                                </div>
                                                                <p class="text-gray-600 dark:text-gray-400">{{ $segment['dialogue'] ?? '' }}</p>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <p class="text-gray-500 italic">Transkrip tidak tersedia.</p>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="bg-gray-50 dark:bg-gray-900/50 rounded-xl p-8 text-center border border-gray-100 dark:border-gray-700">
                                            <div class="mb-4">
                                                <svg class="w-12 h-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                                                </svg>
                                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Dengarkan Podcast</h3>
                                                <p class="text-gray-500 dark:text-gray-400 text-sm mt-1 max-w-sm mx-auto">Login untuk mendengarkan podcast dan melihat transkrip lengkap dari pustaka ini.</p>
                                            </div>
                                            <a href="{{ route('login') }}" class="btn bg-indigo-500 hover:bg-indigo-600 text-white w-full sm:w-auto px-6 py-2.5 rounded-lg font-medium transition-colors duration-200">
                                                Login Sekarang
                                            </a>
                                        </div>
                                    @endauth
                                </div>
                            @endif

                            @if ($library->open_notebook_source_id)
                                <div class="mt-8 md:col-span-1">
                                    @livewire('pustaka-chat', ['pustakaId' => $library->id])
                                </div>
                            @endif
                        </article>
                    </div>
                </div>
            </div>
             <x-community.feed-right-content />
        </div>
    </div>
</x-user-layout>
