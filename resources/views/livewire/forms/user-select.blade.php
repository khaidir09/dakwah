<div class="space-y-4">
    <!-- Search Input -->
    <div class="relative">
        <label for="user-search" class="block text-sm font-medium mb-1">Assign Users</label>
        <input
            wire:model.live.debounce.300ms="search"
            id="user-search"
            class="form-input w-full"
            type="text"
            placeholder="Search users by name or email..."
            autocomplete="off"
        />

        <!-- Search Results Dropdown -->
        @if (count($searchResults) > 0)
            <div class="absolute z-10 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700/60 rounded-md shadow-lg mt-1 max-h-60 overflow-y-auto">
                <ul>
                    @foreach ($searchResults as $result)
                        <li
                            wire:click="selectUser({{ $result->id }})"
                            class="px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700/60 cursor-pointer flex justify-between items-center"
                        >
                            <div>
                                <div class="font-medium text-gray-800 dark:text-gray-100">{{ $result->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $result->email }}</div>
                            </div>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </li>
                    @endforeach
                </ul>
            </div>
        @elseif(strlen($search) >= 2)
            <div class="absolute z-10 w-full bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700/60 rounded-md shadow-lg mt-1 p-4 text-center text-sm text-gray-500">
                No users found.
            </div>
        @endif
    </div>

    <!-- Selected Users List -->
    @if (count($selectedUsers) > 0)
        <div class="flex flex-wrap gap-2">
            @foreach ($selectedUsers as $index => $user)
                <div class="inline-flex items-center bg-violet-100 dark:bg-violet-500/30 text-violet-600 dark:text-violet-200 rounded-full px-3 py-1 text-sm font-medium">
                    <span>{{ $user['name'] }}</span>
                    <button
                        type="button"
                        wire:click="removeUser({{ $index }})"
                        class="ml-2 text-violet-400 hover:text-violet-600 dark:hover:text-violet-100 focus:outline-none"
                    >
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <!-- Hidden Input for Form Submission -->
                    <input type="hidden" name="user_ids[]" value="{{ $user['id'] }}">
                </div>
            @endforeach
        </div>
    @endif
</div>
