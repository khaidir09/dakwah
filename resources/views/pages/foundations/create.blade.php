<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-[96rem] mx-auto">
        <!-- Page header -->
        <div class="mb-8">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Add New Foundation</h1>
        </div>

        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-xl p-6">
            <form action="{{ route('foundations.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Name -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" for="name">Foundation Name <span class="text-red-500">*</span></label>
                    <input id="name" name="name" class="form-input w-full" type="text" value="{{ old('name') }}" required />
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Website URL -->
                <div class="mb-4">
                    <label class="block text-sm font-medium mb-1" for="website_url">Website URL <span class="text-red-500">*</span></label>
                    <input id="website_url" name="website_url" class="form-input w-full" type="url" value="{{ old('website_url') }}" required placeholder="https://example.com" />
                    @error('website_url')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Logo -->
                <div class="mb-6">
                    <label class="block text-sm font-medium mb-1" for="logo">Logo <span class="text-red-500">*</span></label>
                    <input id="logo" name="logo" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-violet-50 file:text-violet-700 hover:file:bg-violet-100" type="file" accept="image/*" required />
                    @error('logo')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- User Selection (Livewire) -->
                <div class="mb-6">
                    <livewire:forms.user-select />
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end mt-6">
                    <a href="{{ route('foundations.index') }}" class="btn border-gray-200 dark:border-gray-700/60 hover:border-gray-300 dark:hover:border-gray-600 text-gray-600 dark:text-gray-300 mr-3">Cancel</a>
                    <button type="submit" class="btn bg-gray-900 text-gray-100 hover:bg-gray-800 dark:bg-gray-100 dark:text-gray-800 dark:hover:bg-white">Save Foundation</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
