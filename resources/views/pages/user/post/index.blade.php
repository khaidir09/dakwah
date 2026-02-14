<x-user-layout>
    @section('title', 'Tulisan')
    <div class="px-4 sm:px-6 lg:px-8 py-8 md:py-0 w-full max-w-[96rem] mx-auto">
        <div class="xl:flex">
            <!-- Left + Middle content -->
            <div class="md:flex flex-1">
                <!-- Left content -->
                <x-community.feed-left-content />

                <!-- Middle content -->
                <div class="flex-1 md:ml-8 xl:mx-4 2xl:mx-8">
                    <div class="md:py-8" x-data="{ tab: new URLSearchParams(window.location.search).get('tab') || 'post' }">
                        <div class="mb-6">
                            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Tulisan</h1>
                        </div>

                        <!-- Tabs -->
                        <div class="relative mb-8">
                            <div class="absolute bottom-0 w-full h-px bg-gray-200 dark:bg-gray-700/60" aria-hidden="true"></div>
                            <ul class="relative text-sm font-medium flex flex-nowrap -mx-4 sm:-mx-6 lg:-mx-8 overflow-x-scroll no-scrollbar">
                                <li class="mr-6 last:mr-0 first:pl-4 sm:first:pl-6 lg:first:pl-8 last:pr-4 sm:last:pr-6 lg:last:pr-8">
                                    <a class="block pb-3 whitespace-nowrap border-b-2 cursor-pointer transition-colors duration-200"
                                       :class="tab === 'post' ? 'text-violet-500 border-violet-500' : 'text-gray-500 dark:text-gray-400 border-transparent hover:text-gray-600 dark:hover:text-gray-300'"
                                       @click.prevent="tab = 'post'; window.history.replaceState(null, '', '?tab=post')">
                                       Post
                                    </a>
                                </li>
                                <li class="mr-6 last:mr-0 first:pl-4 sm:first:pl-6 lg:first:pl-8 last:pr-4 sm:last:pr-6 lg:last:pr-8">
                                    <a class="block pb-3 whitespace-nowrap border-b-2 cursor-pointer transition-colors duration-200"
                                       :class="tab === 'article' ? 'text-violet-500 border-violet-500' : 'text-gray-500 dark:text-gray-400 border-transparent hover:text-gray-600 dark:hover:text-gray-300'"
                                       @click.prevent="tab = 'article'; window.history.replaceState(null, '', '?tab=article')">
                                       Artikel Ilmiah
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <!-- Tab Contents -->
                        <div x-show="tab === 'post'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                            <livewire:front.post-list />
                        </div>

                        <div x-show="tab === 'article'" x-cloak style="display: none;" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                            <livewire:front.article-list />
                        </div>

                    </div>
                </div>
            </div>

            <!-- Right content -->
            <x-community.feed-right-content />
        </div>
    </div>
</x-user-layout>
