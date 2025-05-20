{{-- Applying viking-wood background, parchment text, steel borders --}}
<nav x-data="{ open: false }" class="bg-viking-wood border-b border-viking-steel/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        {{-- Placeholder for a proper logo later --}}
                        <x-application-logo class="block h-9 w-auto fill-current text-viking-parchment" />
                    </a>
                </div>

                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-viking-parchment hover:text-viking-gold focus:text-viking-gold border-viking-gold focus:border-viking-gold">
                        <i class="fa-solid fa-house-chimney mr-1"></i> {{ __('Dashboard') }}
                    </x-nav-link>
                    {{-- Add Kingdom List Link --}}
                    <x-nav-link :href="route('kingdoms.index')" :active="request()->routeIs('kingdoms.*')" class="text-viking-parchment hover:text-viking-gold focus:text-viking-gold border-viking-gold focus:border-viking-gold">
                         <i class="fa-solid fa-map mr-1"></i> {{ __('Realms') }}
                     </x-nav-link>
                     {{-- Add Tribe List Link --}}
                     <x-nav-link :href="route('tribes.index')" :active="request()->routeIs('tribes.*')" class="text-viking-parchment hover:text-viking-gold focus:text-viking-gold border-viking-gold focus:border-viking-gold">
                         <i class="fa-solid fa-users mr-1"></i> {{ __('Tribes') }}
                     </x-nav-link>
                     {{-- Add other primary navigation links later (Sagas, Longhouse, etc) --}}

                    @can('accessKingDashboard', App\Models\User::class)
                        <x-nav-link :href="route('king.dashboard')" :active="request()->routeIs('king.dashboard')" class="text-viking-parchment hover:text-viking-gold focus:text-viking-gold border-viking-gold focus:border-viking-gold">
                            <i class="fa-solid fa-chess-king mr-1"></i> {{ __('King Dashboard') }}
                        </x-nav-link>
                    @endcan
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-viking-parchment bg-viking-wood hover:text-viking-gold focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ml-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        {{-- Use themed dropdown colors --}}
                        <div class="bg-viking-wood border border-viking-steel/50 rounded-md shadow-lg py-1">
                            <x-dropdown-link :href="route('profile.edit')" class="text-viking-parchment hover:bg-viking-stone/50 hover:text-viking-gold">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();"
                                         class="text-viking-parchment hover:bg-viking-stone/50 hover:text-viking-gold">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </div>
                    </x-slot>
                </x-dropdown>
            </div>

            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-viking-steel hover:text-viking-parchment hover:bg-viking-stone focus:outline-none focus:bg-viking-stone focus:text-viking-parchment transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-viking-wood border-t border-viking-steel/50">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" class="text-viking-parchment hover:text-viking-gold focus:text-viking-gold border-viking-gold focus:border-viking-gold">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
             <x-responsive-nav-link :href="route('kingdoms.index')" :active="request()->routeIs('kingdoms.*')" class="text-viking-parchment hover:text-viking-gold focus:text-viking-gold border-viking-gold focus:border-viking-gold">
                 {{ __('Realms') }}
             </x-responsive-nav-link>
              <x-responsive-nav-link :href="route('tribes.index')" :active="request()->routeIs('tribes.*')" class="text-viking-parchment hover:text-viking-gold focus:text-viking-gold border-viking-gold focus:border-viking-gold">
                 {{ __('Tribes') }}
             </x-responsive-nav-link>

            @can('accessKingDashboard', App\Models\User::class)
                <x-responsive-nav-link :href="route('king.dashboard')" :active="request()->routeIs('king.dashboard')" class="text-viking-parchment hover:text-viking-gold focus:text-viking-gold border-viking-gold focus:border-viking-gold">
                    {{ __('King Dashboard') }}
                </x-responsive-nav-link>
            @endcan
        </div>

        <div class="pt-4 pb-1 border-t border-viking-steel/70">
            <div class="px-4">
                <div class="font-medium text-base text-viking-parchment">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-viking-steel">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" class="text-viking-parchment hover:text-viking-gold focus:text-viking-gold">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();"
                            class="text-viking-parchment hover:text-viking-gold focus:text-viking-gold">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>