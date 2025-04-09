<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-viking-parchment leading-tight flex items-center space-x-2">
           <i class="fa-solid fa-users text-viking-steel"></i> <span>Tribe: {{ $tribe->name }}</span>
           <span class="text-sm text-viking-steel font-normal"> (Kingdom: {{ $tribe->kingdom?->name ?? 'Unknown' }})</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-viking-wood/90 shadow-lg rounded-lg overflow-hidden">
                <div class="p-6 text-viking-parchment">

                    {{-- Details Section --}}
                    <dl class="divide-y divide-gray-200 dark:divide-gray-700">
                       {{-- ... dl content as before ... --}}
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4"> <dt class="text-sm font-medium text-viking-steel">Description</dt> <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">{{ $tribe->description ?: 'No description provided.' }}</dd> </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4"> <dt class="text-sm font-medium text-viking-steel">Slug</dt> <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">{{ $tribe->slug }}</dd> </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4"> <dt class="text-sm font-medium text-viking-steel">Parent Kingdom</dt> <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2"> @if($tribe->kingdom) <a href="{{ route('kingdoms.show', $tribe->kingdom) }}" class="text-viking-blue hover:underline"> {{ $tribe->kingdom->name }} </a> @else <span class="text-viking-steel italic">Unknown</span> @endif </dd> </div>
                        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4"> <dt class="text-sm font-medium text-viking-steel">Leader (Thane)</dt> <dd class="mt-1 text-sm sm:mt-0 sm:col-span-2">{{ $tribe->leader?->name ?? 'None Assigned' }}</dd> </div>
                    </dl>

                    {{-- MODIFIED: Request to Join Tribe Button/Status --}}
                    <div class="mt-6">
                        @auth
                            @php $user = Auth::user(); @endphp {{-- Define user once --}}

                            {{-- Check 1: Does the user have a pending request for THIS tribe? --}}
                            @if ($user->hasPendingTribeRequest($tribe))
                                 <p class="text-sm text-viking-gold italic font-semibold">Your request to join this tribe is pending approval.</p>

                            {{-- Check 2: Is user eligible to request? (In kingdom, not in any tribe) --}}
                            @elseif($user->current_kingdom_id === $tribe->kingdom_id && $user->current_tribe_id === null)
                                <form method="POST" action="{{ route('tribes.join.request', $tribe) }}">
                                    @csrf
                                    <x-primary-button class="bg-viking-green hover:bg-viking-green/80">
                                        <i class="fa-solid fa-person-circle-plus mr-2"></i>
                                        Request to Join This Tribe
                                    </x-primary-button>
                                </form>

                            {{-- Check 3: Is user in the WRONG kingdom? --}}
                            @elseif($user->current_kingdom_id !== $tribe->kingdom_id && $user->current_kingdom_id !== null)
                                <p class="text-sm text-viking-steel italic">This tribe belongs to '<span class="font-semibold">{{ $tribe->kingdom->name }}</span>'. You are currently allied with '<span class="font-semibold">{{ $user->currentKingdom->name }}</span>'.</p>
                            @elseif($user->current_kingdom_id === null)
                                 <p class="text-sm text-viking-steel italic">You must swear fealty to the '<span class="font-semibold">{{ $tribe->kingdom->name }}</span>' kingdom before petitioning its tribes.</p>

                            {{-- Check 4: Is user already a member of THIS tribe? --}}
                            @elseif($user->current_tribe_id === $tribe->id)
                                <p class="text-sm text-viking-steel italic font-semibold">You are currently a member of this tribe.</p>

                            {{-- Check 5: Is user in a DIFFERENT tribe? --}}
                            @elseif($user->current_tribe_id !== null)
                                 <p class="text-sm text-viking-steel italic">You must leave your current tribe (<span class="font-semibold">{{ $user->currentTribe?->name ?? 'Unknown' }}</span>) before joining another.</p>

                            @endif
                        @endauth
                    </div>
                    {{-- END MODIFIED SECTION --}}


                     {{-- Action Links --}}
                     <div class="mt-6 pt-4 border-t border-viking-steel/30 flex items-center space-x-4">
                         {{-- ... Edit link as before ... --}}
                         @can('update', $tribe) <a href="{{ route('tribes.edit', $tribe) }}" class="inline-flex items-center px-4 py-2 bg-viking-blue border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-viking-blue/80 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-viking-dark transition ease-in-out duration-150 shadow hover:shadow-md active:scale-95"> <i class="fa-solid fa-pencil mr-1"></i> Edit Tribe </a> @endcan
                     </div>

                    <div class="mt-6"> <a href="{{ route('tribes.index') }}" class="text-sm text-viking-blue hover:underline">&laquo; Back to Tribes List</a> </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>