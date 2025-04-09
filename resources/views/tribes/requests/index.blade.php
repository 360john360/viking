<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-viking-parchment leading-tight flex items-center space-x-2">
            <i class="fa-solid fa-user-check text-viking-blue"></i> {{-- Changed Icon --}}
            <span>Manage Join Petitions for Tribe: {{ $tribe->name }}</span>
             <span class="text-sm text-viking-steel font-normal"> (Kingdom: {{ $tribe->kingdom?->name ?? 'Unknown' }})</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
             {{-- Display Flashed Messages --}}
             @if (session('status')) <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 border border-green-400 rounded dark:bg-viking-green/80 dark:text-white dark:border-viking-green/70" role="alert"> {{ session('status') }} </div> @endif
             @if (session('error')) <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 border border-red-400 rounded dark:bg-viking-blood/80 dark:text-white dark:border-viking-blood/70" role="alert"> {{ session('error') }} </div> @endif
             @if (session('info')) <div class="mb-4 p-4 text-sm text-gray-700 bg-gray-100 border border-gray-400 rounded dark:bg-viking-stone/80 dark:text-viking-parchment dark:border-viking-steel/50" role="alert"> {{ session('info') }} </div> @endif

            <div class="bg-viking-wood/90 shadow-lg rounded-lg overflow-hidden">
                <div class="p-6 text-viking-parchment">

                    @if(!isset($tribe)) {{-- Added check in case controller fails before setting $tribe --}}
                         <p class="text-center text-viking-red italic py-6">Could not load tribe information.</p>
                    @elseif($requests->isEmpty())
                        <p class="text-center text-viking-steel italic py-6">No pending join petitions for your tribe.</p>
                    @else
                        <div class="overflow-x-auto relative border border-viking-steel/30 rounded-md">
                            <table class="w-full text-sm text-left text-viking-parchment">
                                <thead class="text-xs text-viking-steel uppercase bg-viking-dark/50">
                                    <tr>
                                        <th scope="col" class="py-3 px-6">Applicant</th>
                                        <th scope="col" class="py-3 px-6">Requested At</th>
                                        <th scope="col" class="py-3 px-6 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($requests as $request)
                                        <tr class="border-b border-viking-steel/30 hover:bg-viking-stone/50">
                                            <th scope="row" class="py-4 px-6 font-medium whitespace-nowrap">
                                                {{ $request->user->name ?? 'Unknown User' }}
                                                <span class="text-xs text-viking-steel"> (ID: {{ $request->user->id ?? '?' }})</span>
                                            </th>
                                            <td class="py-4 px-6">
                                                {{ $request->created_at->format('Y-m-d H:i') }}
                                                <span class="text-xs text-viking-steel"> ({{ $request->created_at->diffForHumans() }})</span>
                                            </td>
                                            <td class="py-4 px-6 text-right flex items-center justify-end space-x-2">
                                                {{-- Approve Form --}}
                                                <form method="POST" action="{{ route('tribe-requests.approve', $request) }}" class="inline-block">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="font-medium text-viking-green hover:underline px-2 py-1 rounded bg-green-900/50 hover:bg-green-800/50 transition duration-150 ease-in-out"
                                                            onclick="return confirm('Accept this warrior into the tribe?');">
                                                         <i class="fa-solid fa-check mr-1"></i> Approve
                                                    </button>
                                                </form>

                                                {{-- Reject Form --}}
                                                 <form method="POST" action="{{ route('tribe-requests.reject', $request) }}" class="inline-block">
                                                     @csrf
                                                     @method('PATCH')
                                                     <button type="submit"
                                                             class="font-medium text-viking-red hover:underline px-2 py-1 rounded bg-red-900/50 hover:bg-red-800/50 transition duration-150 ease-in-out"
                                                             onclick="return confirm('Reject this warrior\'s petition?');">
                                                          <i class="fa-solid fa-times mr-1"></i> Reject
                                                     </button>
                                                 </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                     <div class="mt-6">
                         <a href="{{ route('dashboard') }}" class="text-sm text-viking-blue hover:underline">&laquo; Back to Dashboard</a>
                     </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>