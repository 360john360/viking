<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-viking-parchment leading-tight">
            {{ __('Edit Tribe') }}: {{ $tribe->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-viking-wood/90 shadow-lg rounded-lg overflow-hidden">
                <div class="p-6 text-viking-parchment">
                    <form method="POST" action="{{ route('tribes.update', $tribe) }}">
                        @csrf
                        @method('PUT') {{-- Or PATCH --}}

                         <div class="mb-4">
                            <x-input-label for="kingdom_id" :value="__('Parent Kingdom')" />
                            <select id="kingdom_id" name="kingdom_id" class="block mt-1 w-full border-gray-300 dark:border-viking-steel/50 dark:bg-viking-dark dark:text-viking-parchment focus:border-viking-gold dark:focus:border-viking-gold focus:ring-viking-gold dark:focus:ring-viking-gold rounded-md shadow-sm" required>
                                <option value="">-- Select Kingdom --</option>
                                @foreach ($kingdoms as $id => $name)
                                    <option value="{{ $id }}" {{ old('kingdom_id', $tribe->kingdom_id) == $id ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('kingdom_id')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="name" :value="__('Tribe Name')" />
                            <x-text-input id="name" class="block mt-1 w-full bg-viking-dark/30" type="text" name="name" :value="old('name', $tribe->name)" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="description" :value="__('Description (Optional)')" />
                            <textarea id="description" name="description" rows="3" class="block mt-1 w-full bg-viking-dark/30 border-viking-steel/50 rounded-md shadow-sm text-viking-parchment focus:border-viking-gold focus:ring focus:ring-viking-gold focus:ring-opacity-50">{{ old('description', $tribe->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="leader_user_id" :value="__('Tribe Leader (Thane)')" />
                            <select id="leader_user_id" name="leader_user_id" class="block mt-1 w-full border-gray-300 dark:border-viking-steel/50 dark:bg-viking-dark dark:text-viking-parchment focus:border-viking-gold dark:focus:border-viking-gold focus:ring-viking-gold dark:focus:ring-viking-gold rounded-md shadow-sm">
                                <option value="">-- None Assigned --</option>
                                {{-- Loop through potential leaders passed from controller --}}
                                @foreach ($potentialLeaders as $id => $name)
                                    <option value="{{ $id }}" {{ old('leader_user_id', $tribe->leader_user_id) == $id ? 'selected' : '' }}>
                                        {{ $name }} (ID: {{ $id }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('leader_user_id')" class="mt-2" />
                            <p class="mt-1 text-xs text-viking-steel">Only users currently in this tribe's parent kingdom can be assigned as leader.</p>
                        </div>
                        {{-- === END Tribe Leader Section === --}}

                         <div class="mb-4">
                            <x-input-label :value="__('Status')" />
                            <div class="mt-1 space-x-4">
                                 <label for="status_active" class="inline-flex items-center">
                                     <input type="radio" id="status_active" name="is_active" value="1" class="border-viking-steel/50 text-viking-blue focus:ring-viking-blue" {{ old('is_active', $tribe->is_active) == 1 ? 'checked' : '' }}>
                                     <span class="ml-2 text-sm text-viking-parchment">{{ __('Active') }}</span>
                                 </label>
                                 <label for="status_inactive" class="inline-flex items-center">
                                     <input type="radio" id="status_inactive" name="is_active" value="0" class="border-viking-steel/50 text-viking-blue focus:ring-viking-blue" {{ old('is_active', $tribe->is_active) == 0 ? 'checked' : '' }}>
                                      <span class="ml-2 text-sm text-viking-parchment">{{ __('Inactive') }}</span>
                                 </label>
                            </div>
                             <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center justify-end mt-6">
                            <a href="{{ route('tribes.show', $tribe) }}" class="underline text-sm text-viking-steel hover:text-viking-parchment rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-viking-gold dark:focus:ring-offset-viking-dark mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button class="bg-viking-blue hover:bg-viking-blue/80 focus:ring-blue-500">
                                {{ __('Update Tribe') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>