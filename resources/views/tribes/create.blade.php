<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-viking-parchment leading-tight">
            {{ __('Establish New Tribe') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-viking-wood/90 shadow-lg rounded-lg overflow-hidden">
                <div class="p-6 text-viking-parchment">
                    <form method="POST" action="{{ route('tribes.store') }}">
                        @csrf

                        <div class="mb-4">
                            <x-input-label for="kingdom_id" :value="__('Parent Kingdom')" />
                            <select id="kingdom_id" name="kingdom_id" class="block mt-1 w-full border-gray-300 dark:border-viking-steel/50 dark:bg-viking-dark dark:text-viking-parchment focus:border-viking-gold dark:focus:border-viking-gold focus:ring-viking-gold dark:focus:ring-viking-gold rounded-md shadow-sm" required>
                                <option value="">-- Select Kingdom --</option>
                                {{-- Loop through kingdoms passed from controller --}}
                                @foreach ($kingdoms as $id => $name)
                                    <option value="{{ $id }}" {{ old('kingdom_id') == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('kingdom_id')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Tribe Name')" />
                            <x-text-input id="name" class="block mt-1 w-full bg-viking-dark/30" type="text" name="name" :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description" :value="__('Description (Optional)')" />
                            <textarea id="description" name="description" rows="3" class="block mt-1 w-full bg-viking-dark/30 border-viking-steel/50 rounded-md shadow-sm text-viking-parchment focus:border-viking-gold focus:ring focus:ring-viking-gold focus:ring-opacity-50">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('tribes.index') }}" class="underline text-sm text-viking-steel hover:text-viking-parchment rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-viking-gold dark:focus:ring-offset-viking-dark mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button class="bg-viking-green hover:bg-viking-green/80 focus:ring-green-500">
                                {{ __('Establish Tribe') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>