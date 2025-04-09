<x-app-layout>
    {{-- Define the header content for this page --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Active Kingdoms') }}
        </h2>
    </x-slot>

    {{-- Main page content --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Link to the create kingdom form --}}
                     @can('create', App\Models\Kingdom::class) {{-- Check policy --}}
                        <div class="mb-4">
                            <a href="{{ route('kingdoms.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                                Establish New Kingdom
                            </a>
                        </div>
                    @endcan

                    {{-- Check if kingdoms exist --}}
                    @if ($kingdoms->count())
                        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                            {{-- Loop through kingdoms --}}
                            @foreach ($kingdoms as $kingdom)
                                <li class="py-3">
                                    {{-- Link to the kingdom's show page --}}
                                    <a href="{{ route('kingdoms.show', $kingdom) }}" class="text-blue-600 dark:text-blue-400 hover:underline">
                                         {{ $kingdom->name }}
                                    </a>
                                     <span class="text-sm text-gray-500 dark:text-gray-400">(ID: {{ $kingdom->id }})</span>
                                     {{-- Add Edit link if authorized --}}
                                     @can('update', $kingdom)
                                         <a href="{{ route('kingdoms.edit', $kingdom) }}" class="ml-4 text-sm text-gray-500 dark:text-gray-400 hover:underline">[Edit]</a>
                                     @endcan
                                </li>
                            @endforeach
                        </ul>

                        {{-- Display pagination links (styled by Tailwind) --}}
                        <div class="mt-6">
                            {{ $kingdoms->links() }}
                        </div>

                    @else
                        <p>No active kingdoms found yet.</p>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>