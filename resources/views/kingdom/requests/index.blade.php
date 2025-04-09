<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{-- Display kingdom name if the user is a king, otherwise a generic title --}}
            {{ __('Kingdom Join Requests') }} @if($kingdom) for {{ $kingdom->name }} @endif
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Session Status Messages --}}
                    @if(session('status'))
                        <div class="mb-4 p-4 bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 rounded-lg shadow">
                            {{ session('status') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-4 p-4 bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded-lg shadow">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- Check if the user is actually a king (kingdom object exists) --}}
                    @if(!$kingdom)
                        <p>You are not currently designated as the King of any active kingdom, and therefore cannot manage join requests.</p>
                    @else
                        <h3 class="text-lg font-medium mb-4 border-b border-gray-300 dark:border-gray-700 pb-2">Pending Requests</h3>

                        {{-- Check if there are any requests using forelse --}}
                        @forelse ($requests as $request)
                            <div class="mb-4 p-4 border border-gray-200 dark:border-gray-700 rounded-lg flex justify-between items-center">
                                <div>
                                    <p>
                                        <strong>{{ $request->user->name ?? 'Unknown User' }}</strong>
                                        requested to join on
                                        {{ $request->created_at->format('M d, Y \a\t H:i') }}
                                        ({{ $request->created_at->diffForHumans() }})
                                    </p>
                                    {{-- Optionally display message if one exists: --}}
                                    {{-- @if($request->message) <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Message: {{ $request->message }}</p> @endif --}}
                                </div>
                                <div class="flex space-x-2">
                                    {{-- Approve Button/Form --}}
                                    <form method="POST" action="{{ route('kingdom.management.requests.approve', $request) }}" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-3 py-1 bg-green-500 hover:bg-green-600 text-white rounded text-sm font-medium">Approve</button>
                                    </form>

                                    {{-- Reject Button/Form --}}
                                    <form method="POST" action="{{ route('kingdom.management.requests.reject', $request) }}" class="inline-block">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-sm font-medium">Reject</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            {{-- Message displayed if $requests collection is empty --}}
                            <p>There are no pending join requests for your kingdom at this time.</p>
                        @endforelse

                    @endif {{-- End check if user is a king --}}

                    <div class="mt-6">
                        <a href="{{ route('dashboard') }}" class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">&laquo; Back to Dashboard</a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>