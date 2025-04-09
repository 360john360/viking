<x-app-layout>
    {{-- Define the header content using the kingdom name --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Kingdom: {{ $kingdom->name }}
        </h2>
    </x-slot>

    {{-- Main page content --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Kingdom Details --}}
                    <dl class="divide-y divide-gray-200 dark:divide-gray-700">
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Description</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0 sm:col-span-2">{{ $kingdom->description ?: 'N/A' }}</dd>
                        </div>
                         <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Slug</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0 sm:col-span-2">{{ $kingdom->slug }}</dd>
                        </div>
                         <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</dt>
                            <dd class="mt-1 text-sm font-medium sm:mt-0 sm:col-span-2 {{ $kingdom->is_active ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $kingdom->is_active ? 'Active' : 'Inactive' }}
                            </dd>
                        </div>
                         <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">King</dt>
                            <dd class="mt-1 text-sm text-gray-900 dark:text-gray-100 sm:mt-0 sm:col-span-2">{{ $kingdom->king ? $kingdom->king->name : 'None Assigned' }}</dd>
                        </div>
                         {{-- Add more details later (members count, tribes etc) --}}
                    </dl>

                     {{-- Request to Join Button/Form --}}
                    <div class="mt-6">
                         @auth
                            @if (Auth::user()->current_kingdom_id === null)
                                <form method="POST" action="{{ route('kingdoms.join.request', $kingdom) }}">
                                    @csrf
                                    {{-- Using a styled button component potentially from Breeze --}}
                                    <x-primary-button>Request to Join This Kingdom</x-primary-button>
                                </form>
                            @elseif (Auth::user()->current_kingdom_id === $kingdom->id)
                                 <p class="text-sm text-gray-600 dark:text-gray-400 italic">You are currently a member of this kingdom.</p>
                            @else
                                 <p class="text-sm text-gray-600 dark:text-gray-400 italic">You must leave your current kingdom before requesting to join another.</p>
                            @endif
                        @endauth
                    </div>

                    {{-- Action Links visible based on Policy --}}
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 flex items-center space-x-4">
                        @can('update', $kingdom)
                            <a href="{{ route('kingdoms.edit', $kingdom) }}" class="text-blue-600 dark:text-blue-400 hover:underline">Edit Kingdom</a>
                        @endcan

                        {{-- Delete Button Removed --}}
                    </div>

                     <div class="mt-6">
                         <a href="{{ route('kingdoms.index') }}" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">&laquo; Back to Kingdoms List</a>
                     </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>