<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">

        <!-- Dashboard actions -->
        <div class="sm:flex sm:justify-between sm:items-center mb-8">

            <!-- Left: Title -->
            <div class="mb-4 sm:mb-0">
                <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Dashboard</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Ringkasan konten, antrian moderasi, dan aktivitas terbaru.</p>
            </div>

        </div>

        <!-- Cards -->
        <div class="grid grid-cols-12 gap-6">

            @foreach ($summary['stats'] as $stat)
                <x-dashboard.stat-card :label="$stat['label']" :total="$stat['total']" :current="$stat['current']"
                    :percent="$stat['percent']" />
            @endforeach

            <x-dashboard.moderation-panel :queues="$queues" :latestPending="$latestPending" />

            <x-dashboard.activity-feed :activity="$summary['activity']" />

        </div>

    </div>
</x-app-layout>
