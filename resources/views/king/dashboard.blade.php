<!-- 
  =====================
  KING'S DASHBOARD 3.0
  =====================

  This version focuses on:
  1) A more visually appealing layout
  2) Sorting & searching tribes (client-side via Alpine)
  3) Inlined modals for editing the kingdom & creating tribes
  4) A refined aesthetic with consistent spacing and theming

  NOTES:
  - We'll create placeholders for the actual logic of update/create since we haven't updated the controllers
  - We'll rely heavily on Alpine.js to manage open/close states for modals and sorting
  - This code is the FULL content of resources/views/king/dashboard.blade.php
-->

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-viking-parchment leading-tight font-cinzel flex items-center space-x-2">
                <i class="fa-solid fa-crown text-viking-gold"></i>
                <span>Jarl's Command Center</span>
            </h2>
            <!-- Button to open Edit Kingdom modal -->
            @can('update', $kingsKingdom)
                <button
                    @click="openModal = (openModal === 'editKingdom' ? null : 'editKingdom')"
                    class="inline-flex items-center px-3 py-2 bg-viking-wood text-viking-parchment hover:bg-viking-stone hover:text-viking-gold text-sm rounded shadow"
                >
                    <i class="fa-solid fa-pencil mr-1"></i> Shape Realm
                </button>
            @endcan
        </div>
    </x-slot>

    <div class="py-8 bg-viking-dark" x-data="
    {
        openModal: null,
        tribeSearch: '',
        tribeSort: 'nameAsc',
        get sortedTribes() {
            // Make a shallow clone
            let items = JSON.parse(JSON.stringify({{ $kingsKingdom->tribes->toJson() }}));

            // Filter by name
            if (this.tribeSearch.trim().length > 0) {
                const term = this.tribeSearch.toLowerCase();
                items = items.filter(t => t.name.toLowerCase().includes(term));
            }

            // Sort
            switch (this.tribeSort) {
                case 'nameAsc':
                    items.sort((a,b) => a.name.localeCompare(b.name));
                    break;
                case 'nameDesc':
                    items.sort((a,b) => b.name.localeCompare(a.name));
                    break;
                case 'activeFirst':
                    items.sort((a,b) => {
                        if (a.is_active && !b.is_active) return -1;
                        if (!a.is_active && b.is_active) return 1;
                        return a.name.localeCompare(b.name);
                    });
                    break;
                case 'inactiveFirst':
                    items.sort((a,b) => {
                        if (!a.is_active && b.is_active) return -1;
                        if (a.is_active && !b.is_active) return 1;
                        return a.name.localeCompare(b.name);
                    });
                    break;
            }

            return items;
        }
    }
    ">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            <!-- KINGDOM OVERVIEW -->
            <div class="bg-viking-wood border border-viking-steel rounded-lg shadow p-6">
                <div class="flex flex-col md:flex-row md:justify-between md:items-start">
                    <div>
                        <h3 class="text-2xl font-cinzel text-viking-parchment font-bold">
                            {{ $kingsKingdom->name }}
                            <span class="ml-2 text-sm px-2 py-1 rounded-full {{ $kingsKingdom->is_active ? 'bg-viking-green' : 'bg-viking-blood' }} text-white">
                                {{ $kingsKingdom->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </h3>
                        <p class="text-sm text-viking-steel italic mt-1">
                            {{ $kingsKingdom->description ?: 'No saga written yet.' }}
                        </p>
                    </div>
                    <!-- Emblem or Banner placeholder -->
                    <div class="mt-4 md:mt-0 w-24 h-24 bg-viking-stone rounded-md flex items-center justify-center text-viking-steel">
                        <i class="fa-solid fa-shield text-4xl"></i>
                    </div>
                </div>
            </div>

            <!-- TRIBE MANAGEMENT -->
            <div class="bg-viking-wood border border-viking-steel rounded-lg shadow p-6" x-data="{ openTribes: true }">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-cinzel text-viking-parchment flex items-center space-x-2">
                        <i class="fa-solid fa-users-rays text-viking-gold"></i>
                        <span>Tribes of {{ $kingsKingdom->name }}</span>
                    </h3>
                    <div class="flex items-center space-x-2">
                        <!-- Sorting -->
                        <select x-model="tribeSort"
                            class="text-sm bg-viking-dark text-viking-parchment border border-viking-steel rounded px-2 py-1 focus:outline-none"
                        >
                            <option value="nameAsc">Name (A-Z)</option>
                            <option value="nameDesc">Name (Z-A)</option>
                            <option value="activeFirst">Active First</option>
                            <option value="inactiveFirst">Inactive First</option>
                        </select>
                        <!-- Search -->
                        <input
                            type="search"
                            x-model="tribeSearch"
                            placeholder="Search tribes..."
                            class="text-sm bg-viking-dark text-viking-parchment border border-viking-steel rounded px-2 py-1 focus:outline-none"
                        >
                        <!-- Expand/Collapse Button -->
                        <button @click="openTribes = !openTribes" class="text-xs text-viking-steel hover:text-viking-gold">
                            <i class="fa-solid fa-chevron-down" :class="{'rotate-180': openTribes}"></i>
                        </button>
                    </div>
                </div>
                <div x-show="openTribes" class="space-y-4" x-transition>
                    <template x-for="tribe in sortedTribes" :key="tribe.id">
                        <div class="bg-viking-dark/40 border border-viking-steel rounded-md p-4 flex justify-between items-center">
                            <div>
                                <p class="text-md text-viking-parchment font-semibold" x-text="tribe.name"></p>
                                <p class="text-xs text-viking-steel">
                                    <template x-if="!tribe.is_active">
                                        <span>(Inactive)</span>
                                    </template>
                                    Thane:
                                    <span x-text="tribe.leader ? tribe.leader.name : 'Unassigned'"></span>
                                </p>
                            </div>
                            <div class="flex gap-2">
                                <!-- Manage Button (Tribe Edit) -->
                                <a
                                    x-bind:href="'/tribes/' + tribe.id + '/edit'"
                                    class="px-3 py-1 bg-viking-blue text-white text-xs rounded shadow hover:bg-viking-blue/80"
                                >
                                    <i class="fa-solid fa-gear"></i> Manage
                                </a>
                                <!-- Petitions Button -->
                                <a
                                    href="{{ route('tribe-requests.index') }}"
                                    class="px-3 py-1 bg-viking-stone text-viking-parchment text-xs rounded shadow hover:bg-viking-stone/80"
                                >
                                    <i class="fa-solid fa-inbox"></i> Petitions
                                </a>
                            </div>
                        </div>
                    </template>
                    <div x-show="sortedTribes.length === 0" class="text-viking-steel italic">
                        No matching tribes.
                    </div>
                </div>
            </div>

            <!-- JOIN PETITIONS -->
            <div class="bg-viking-stone/90 border border-viking-steel rounded-lg shadow p-6" x-data="{ openJoin: true }">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-cinzel text-viking-parchment flex items-center space-x-2">
                        <i class="fa-solid fa-scroll text-viking-gold"></i>
                        <span>Join Petitions</span>
                    </h3>
                    <button @click="openJoin = !openJoin" class="text-xs text-viking-steel hover:text-viking-gold">
                        <i class="fa-solid fa-chevron-down" :class="{'rotate-180': openJoin}"></i>
                    </button>
                </div>
                <div x-show="openJoin" x-transition class="space-y-4">
                    @forelse ($pendingKingdomJoinRequests as $request)
                        <div class="bg-viking-dark/30 border border-viking-steel/30 rounded-md p-3">
                            <p class="text-sm text-viking-parchment font-semibold">
                                {{ $request->user->name }}
                                <span class="text-xs text-viking-steel">requested {{ $request->created_at->diffForHumans() }}</span>
                            </p>
                            <div class="mt-2 flex justify-end gap-2">
                                <form method="POST" action="{{ route('kingdom.management.requests.approve', $request) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-2 py-1 bg-viking-green text-white text-xs rounded hover:opacity-80">
                                        <i class="fa-solid fa-check"></i>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('kingdom.management.requests.reject', $request) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="px-2 py-1 bg-viking-blood text-white text-xs rounded hover:opacity-80">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-viking-steel italic">No join petitions at this time.</p>
                    @endforelse
                </div>
            </div>

            <!-- RELATIONS PLACEHOLDER -->
            <div class="bg-viking-wood border border-viking-steel rounded-lg shadow p-6">
                <h3 class="text-lg font-cinzel text-viking-parchment mb-2 flex items-center space-x-2">
                    <i class="fa-solid fa-handshake text-viking-gold"></i>
                    <span>Kingdom Relations</span>
                </h3>
                <p class="text-viking-steel text-sm italic">
                    Coming soon: alliances, rivalries, and diplomacy.
                </p>
            </div>

            <!-- EVENTS PLACEHOLDER -->
            <div class="bg-viking-wood border border-viking-steel rounded-lg shadow p-6">
                <h3 class="text-lg font-cinzel text-viking-parchment mb-2 flex items-center space-x-2">
                    <i class="fa-solid fa-calendar-days text-viking-gold"></i>
                    <span>Upcoming Events</span>
                </h3>
                <p class="text-viking-steel text-sm italic">
                    Feasts and raids will be displayed here soon.
                </p>
            </div>

            <!-- DEBUG SECTION -->
            <div x-data="{ openDebug: false }">
                <button @click="openDebug = !openDebug" class="text-xs text-viking-steel hover:text-viking-parchment">
                    <i class="fa-solid fa-bug"></i> Toggle Debug Info
                </button>
                <div x-show="openDebug" x-transition class="mt-2 p-4 bg-viking-dark border border-viking-steel/50 rounded text-xs text-viking-parchment overflow-x-auto">
                    <pre>
User ID: {{ auth()->id() }}
Kingdom ID: {{ $kingsKingdom->id }}
Tribe Count: {{ $kingsKingdom->tribes->count() }}
Pending Requests: {{ $pendingKingdomJoinRequests->count() }}
                    </pre>
                </div>
            </div>
        </div>

        <!-- ================ MODALS ================ -->
        <!-- Edit Kingdom Modal -->
        @can('update', $kingsKingdom)
        <div
            x-show="openModal === 'editKingdom'"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            x-transition
            style="display: none;"
        >
            <div class="bg-viking-wood text-viking-parchment w-full max-w-md rounded-lg shadow p-6 relative">
                <button @click="openModal = null" class="absolute top-2 right-2 text-xl text-viking-steel hover:text-viking-blood">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <h2 class="text-xl font-cinzel mb-4 flex items-center space-x-2">
                    <i class="fa-solid fa-pencil"></i>
                    <span>Shape Realm</span>
                </h2>
                <!-- Form to update the kingdom -->
                <form method="POST" action="{{ route('kingdoms.update', $kingsKingdom) }}">
                    @csrf
                    @method('PATCH')
                    <div class="mb-4">
                        <label for="name" class="block text-sm mb-1">Kingdom Name</label>
                        <input type="text" id="name" name="name" 
                               value="{{ old('name', $kingsKingdom->name) }}"
                               class="w-full text-sm px-3 py-2 bg-viking-dark text-viking-parchment border border-viking-steel rounded focus:outline-none"/>
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-sm mb-1">Description</label>
                        <textarea id="description" name="description" rows="3"
                                  class="w-full text-sm px-3 py-2 bg-viking-dark text-viking-parchment border border-viking-steel rounded focus:outline-none">{{ old('description', $kingsKingdom->description) }}</textarea>
                    </div>
                    <div class="flex items-center mb-4">
                        <input type="checkbox" id="is_active" name="is_active" value="1" class="mr-2"
                               @if(old('is_active', $kingsKingdom->is_active)) checked @endif />
                        <label for="is_active" class="text-sm">Active</label>
                    </div>

                    <div class="mt-6 text-right">
                        <button type="submit"
                                class="px-4 py-2 bg-viking-blue text-white text-sm rounded hover:bg-viking-blue/80 mr-2">
                            Save Changes
                        </button>
                        <button type="button" @click="openModal = null"
                                class="px-4 py-2 bg-viking-stone text-sm rounded hover:bg-viking-stone/80">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endcan

        <!-- (Optional) Create Tribe Modal -->
        @can('create', App\Models\Tribe::class)
        <div
            x-show="openModal === 'createTribe'"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
            x-transition
            style="display: none;"
        >
            <div class="bg-viking-wood text-viking-parchment w-full max-w-md rounded-lg shadow p-6 relative">
                <button @click="openModal = null" class="absolute top-2 right-2 text-xl text-viking-steel hover:text-viking-blood">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <h2 class="text-xl font-cinzel mb-4 flex items-center space-x-2">
                    <i class="fa-solid fa-sitemap"></i>
                    <span>Establish New Tribe</span>
                </h2>
                <form method="POST" action="{{ route('tribes.store') }}">
                    @csrf
                    <input type="hidden" name="kingdom_id" value="{{ $kingsKingdom->id }}" />

                    <div class="mb-4">
                        <label for="tribe_name" class="block text-sm mb-1">Tribe Name</label>
                        <input type="text" id="tribe_name" name="name" value="{{ old('name') }}"
                               class="w-full text-sm px-3 py-2 bg-viking-dark text-viking-parchment border border-viking-steel rounded focus:outline-none"/>
                    </div>
                    <div class="mb-4">
                        <label for="tribe_description" class="block text-sm mb-1">Description</label>
                        <textarea id="tribe_description" name="description" rows="3"
                                  class="w-full text-sm px-3 py-2 bg-viking-dark text-viking-parchment border border-viking-steel rounded focus:outline-none">{{ old('description') }}</textarea>
                    </div>

                    <div class="mt-6 text-right">
                        <button type="submit"
                                class="px-4 py-2 bg-viking-blue text-white text-sm rounded hover:bg-viking-blue/80 mr-2">
                            Create Tribe
                        </button>
                        <button type="button" @click="openModal = null"
                                class="px-4 py-2 bg-viking-stone text-sm rounded hover:bg-viking-stone/80">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endcan

    </div>
</x-app-layout>
