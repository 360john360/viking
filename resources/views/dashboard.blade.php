<x-app-layout> {{-- Uses layouts.app.blade.php --}}
    {{-- Alpine.js state - Simplified, modals triggered directly --}}
    <div x-data="{
            openModal: '',
            editKingdomData: null {{-- Populated by trigger button --}}
            {{-- Removed pendingTribeRequests from Alpine - use Blade variable directly --}}
            {{-- Removed currentKingsKingdom getter - use Blade variable directly --}}
        }" class="bg-viking-dark text-viking-parchment">

        {{-- Header Slot --}}
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-viking-parchment leading-tight flex items-center space-x-3">
                <i class="fa-solid fa-shield-heart text-viking-gold text-2xl"></i>
                <span>{{ __('Stronghold Dashboard') }}</span>
            </h2>
        </x-slot>

        {{-- Main Content Area --}}
        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                {{-- Ensure $user is defined for checks/display --}}
                @php $user = Auth::user(); @endphp
                {{-- Removed user refresh - test without it first --}}

                {{-- Welcome Banner --}}
                <div class="bg-gradient-to-r from-viking-wood/80 via-viking-stone/60 to-viking-wood/80 border border-viking-steel/40 shadow-lg rounded-lg p-5 mb-6 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-xl text-viking-parchment font-cinzel"> Hail, {{ $user->name }}! </h3>
                        <p class="text-sm text-viking-steel" title="A mark of your legacy among the clans."> Standing: <span class="font-bold text-viking-gold">{{ $user->honourRank?->name ?? 'Unproven' }}</span> </p>
                    </div>
                    <div class="flex items-center space-x-4">
                        @if($user->currentKingdom)
                        <div class="w-14 h-14 bg-viking-stone rounded-full border-2 border-viking-gold flex items-center justify-center text-viking-steel text-xs italic" title="{{ $user->currentKingdom->name }} Crest Placeholder">
                             <i class="fa-solid fa-shield text-2xl text-viking-steel/70"></i>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Main Content Grid --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Column 1: User Status & Actions --}}
                    <div class="lg:col-span-2 space-y-6">
                        {{-- User Status Panel --}}
                        <div class="bg-viking-wood/90 border border-viking-steel/40 shadow-lg rounded-lg overflow-hidden relative">
                            <div class="p-6">
                                <h4 class="text-lg font-semibold text-viking-parchment mb-5 flex items-center space-x-2"> <i class="fa-solid fa-scroll text-viking-steel w-5 text-center"></i> <span>Your Chronicle</span> </h4>
                                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-5">
                                    <div title="Name"> <dt class="text-xs uppercase tracking-wider font-semibold text-viking-steel flex items-center space-x-1.5"><i class="fa-solid fa-signature fa-fw"></i><span>Name</span></dt> <dd class="mt-1 text-md text-viking-parchment">{{ $user->name }}</dd> </div>
                                    <div title="Email"> <dt class="text-xs uppercase tracking-wider font-semibold text-viking-steel flex items-center space-x-1.5"><i class="fa-solid fa-envelope fa-fw"></i><span>Missive</span></dt> <dd class="mt-1 text-sm text-viking-parchment break-words">{{ $user->email }}</dd> </div>
                                    <div title="Kingdom"> <dt class="text-xs uppercase tracking-wider font-semibold text-viking-steel flex items-center space-x-1.5"><i class="fa-solid fa-flag fa-fw"></i><span>Allegiance</span></dt> <dd class="mt-1 text-md font-semibold {{ $user->currentKingdom ? 'text-viking-gold' : 'text-viking-steel italic' }}">{{ $user->currentKingdom?->name ?? 'Unsworn' }}</dd> </div>
                                    <div title="Tribe"> <dt class="text-xs uppercase tracking-wider font-semibold text-viking-steel flex items-center space-x-1.5"><i class="fa-solid fa-users fa-fw"></i><span>Tribe</span></dt> <dd class="mt-1 text-sm text-viking-parchment">{{ $user->currentTribe?->name ?? 'No Tribe Sworn' }}</dd> </div>
                                    <div title="Rank"> <dt class="text-xs uppercase tracking-wider font-semibold text-viking-steel flex items-center space-x-1.5"><i class="fa-solid fa-award fa-fw"></i><span>Standing</span></dt> <dd class="mt-1 text-sm text-viking-parchment">{{ $user->honourRank?->name ?? 'Unproven' }}</dd> </div>
                                </dl>
                                 {{-- Leave Kingdom Action --}}
                                 @if ($user->current_kingdom_id && !$user->isKing())
                                    <div class="mt-6 pt-4 border-t border-viking-steel/30">
                                        <button @click="openModal = 'leaveKingdom'" type="button" class="inline-flex items-center px-4 py-2 bg-viking-blood border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-viking-blood/80 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-viking-dark transition ease-in-out duration-150 shadow-md hover:shadow-lg focus:shadow-outline active:scale-95">
                                            <i class="fa-solid fa-door-open mr-2"></i> {{ __('Forsake Allegiance') }}
                                        </button>
                                    </div>
                                @elseif ($user->isKing() && $user->current_kingdom_id)
                                     <div class="mt-6 pt-4 border-t border-viking-steel/30 text-sm text-viking-steel italic"> Kings must abdicate or use other means to leave their kingdom. </div>
                                @endif
                            </div>
                        </div> {{-- End User Status Panel --}}

                        {{-- Quick Actions Panel --}}
                         <div class="bg-viking-wood/90 border border-viking-steel/40 shadow-lg rounded-lg overflow-hidden">
                           <div class="p-6"> <h4 class="text-md font-semibold text-viking-parchment mb-4 flex items-center space-x-2"><i class="fa-solid fa-compass text-viking-steel w-5 text-center"></i><span>Explore</span></h4> <div class="grid grid-cols-2 sm:grid-cols-3 gap-4"> <a href="{{ route('kingdoms.index') }}" class="block p-3 bg-viking-stone/50 hover:bg-viking-stone/70 rounded ..."><i class="fa-solid fa-map mr-1"></i> Realms</a> <a href="#" class="block p-3 opacity-50 cursor-not-allowed ..."><i class="fa-solid fa-book-skull mr-1"></i> Sagas (NYI)</a> <a href="#" class="block p-3 opacity-50 cursor-not-allowed ..."><i class="fa-solid fa-house-fire mr-1"></i> Longhouse (NYI)</a> <a href="#" class="block p-3 opacity-50 cursor-not-allowed ..."><i class="fa-solid fa-calendar-days mr-1"></i> Feasts (NYI)</a> <a href="{{ route('profile.edit') }}" class="block p-3 ..."><i class="fa-solid fa-user-pen mr-1"></i> Profile</a> </div> </div>
                        </div>

                    </div> {{-- End Left Column --}}

                    {{-- Right Column: Management Actions --}}
                    <div class="lg:col-span-1 space-y-6">
                         {{-- Kingdom Management Panel --}}
                         {{-- Use the $kingsKingdom variable passed from the route closure --}}
                         @if($user->isKing() && isset($kingsKingdom) && $kingsKingdom)
                             <div class="bg-viking-stone/90 border border-viking-steel/40 shadow-lg rounded-lg overflow-hidden relative">
                                 <div class="p-6">
                                     <h4 class="text-md font-semibold text-viking-parchment mb-4 border-b border-viking-steel/30 pb-2 flex items-center space-x-2"> <i class="fa-solid fa-crown text-viking-gold w-5 text-center"></i> <span>Jarl's Decree ({{$kingsKingdom->name}})</span> </h4>
                                     <div class="space-y-3">
                                         {{-- Link to separate Kingdom request page --}}
                                         {{-- Use $pendingKingdomJoinRequests passed from route --}}
                                         <a href="{{ route('kingdom.management.requests.index') }}" class="w-full text-left block px-3 py-2 bg-viking-dark/30 hover:bg-viking-dark/50 rounded text-sm text-viking-parchment transition duration-150 ease-in-out flex items-center justify-between shadow hover:shadow-md focus:outline-none focus:ring-2 focus:ring-viking-gold">
                                             <span class="flex items-center space-x-2"><i class="fa-solid fa-scroll fa-fw text-viking-blue"></i><span>Manage Kingdom Petitions</span></span>
                                             {{-- Check variable exists before calling count() --}}
                                             <span class="text-xs bg-viking-blood text-white rounded-full px-1.5 py-0.5">{{ isset($pendingKingdomJoinRequests) ? $pendingKingdomJoinRequests->count() : '0' }}</span>
                                         </a>
                                         {{-- Button triggers editKingdom modal --}}
                                         @can('update', $kingsKingdom)
                                              {{-- Pass data via string concatenation within Alpine expression --}}
                                             <button @click="openModal = 'editKingdom'; editKingdomData = {{ Js::from($kingsKingdom->only(['id', 'name', 'description', 'is_active'])) }};" type="button"
                                                     class="w-full text-left px-3 py-2 bg-viking-dark/30 hover:bg-viking-dark/50 rounded text-sm text-viking-parchment transition duration-150 ease-in-out flex items-center space-x-2 shadow hover:shadow-md focus:outline-none focus:ring-2 focus:ring-viking-gold">
                                                 <i class="fa-solid fa-pencil fa-fw text-viking-gold"></i><span>Shape Kingdom</span>
                                             </button>
                                         @endcan
                                         <a href="{{ route('tribes.index') }}?kingdom={{ $kingsKingdom->id }}" class="w-full text-left block px-3 py-2 bg-viking-dark/30 hover:bg-viking-dark/50 ..."> <i class="fa-solid fa-users fa-fw text-viking-steel"></i><span>Oversee Tribes</span> </a>
                                     </div>
                                 </div>
                             </div>
                         @endif {{-- End isKing check --}}

                         {{-- Tribe Management Panel --}}
                         {{-- Use the $thanesTribe variable passed from the route closure --}}
                          @if($user->isThaneOfAnyTribe() && isset($thanesTribe) && $thanesTribe)
                              <div class="bg-viking-stone/90 border border-viking-steel/40 shadow-lg rounded-lg overflow-hidden relative">
                                  <div class="p-6">
                                      <h4 class="text-md font-semibold text-viking-parchment mb-4 border-b border-viking-steel/30 pb-2 flex items-center space-x-2"> <i class="fa-solid fa-users-rays text-viking-gold w-5 text-center"></i> <span>Thane's Hearth ({{$thanesTribe->name}})</span> </h4>
                                      <div class="space-y-3">
                                          {{-- Link to separate Tribe request page --}}
                                          <a href="{{ route('tribe-requests.index') }}" class="w-full text-left block px-3 py-2 bg-viking-dark/30 hover:bg-viking-dark/50 rounded text-sm text-viking-parchment transition duration-150 ease-in-out flex items-center justify-between shadow hover:shadow-md focus:outline-none focus:ring-2 focus:ring-viking-gold">
                                              <span class="flex items-center space-x-2"><i class="fa-solid fa-user-plus fa-fw text-viking-blue"></i><span>Manage Tribe Petitions</span></span>
                                              {{-- Use $pendingTribeRequests passed from route --}}
                                              <span class="text-xs bg-viking-blood text-white rounded-full px-1.5 py-0.5">{{ isset($pendingTribeRequests) ? $pendingTribeRequests->count() : '0' }}</span>
                                          </a>
                                           @can('update', $thanesTribe)
                                             <a href="{{ route('tribes.edit', $thanesTribe) }}" class="w-full text-left block px-3 py-2 bg-viking-dark/30 hover:bg-viking-dark/50 ..."> <i class="fa-solid fa-pencil ..."></i><span>Shape Tribe</span> </a>
                                           @endcan
                                      </div>
                                  </div>
                              </div>
                          @endif {{-- End isThaneOfAnyTribe check --}}

                         {{-- Admin Management Panel --}}
                         @if($user->isSiteAdmin())
                             {{-- ... Admin panel content using modal trigger for Create Kingdom ... --}}
                              <div class="bg-viking-stone/90 ..."> <div class="p-6"> <h4 ...> Allfather's Sight </h4> <div class="space-y-3"> <button @click="openModal = 'createKingdom'" type="button" class="..."> Found New Realm </button> <a href="{{ route('tribes.create') }}" class="..."> Establish New Tribe </a> <a href="#" class="... opacity-50 cursor-not-allowed"> Manage Warriors (NYI) </a> <a href="#" class="... opacity-50 cursor-not-allowed"> Site Settings (NYI) </a> </div> </div> </div>
                         @endif

                    </div> {{-- End Right Column --}}
                </div> {{-- End Main Grid --}}

            </div> {{-- End Max Width Container --}}
        </div> {{-- End py-12 Padding --}}


        {{-- MODAL DEFINITIONS --}}
        {{-- Leave Kingdom Modal --}}
        <div x-show="openModal === 'leaveKingdom'" x-transition ... > ... </div>
        {{-- Edit Kingdom Modal --}}
        <div x-show="openModal === 'editKingdom'" x-transition ... > ... </div>
        {{-- Create Kingdom Modal --}}
        <div x-show="openModal === 'createKingdom'" x-transition ... > ... </div>
        {{-- Tribe Join Requests Modal REMOVED --}}

    </div> {{-- End Alpine x-data scope --}}
</x-app-layout>