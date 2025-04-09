<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Edit Kingdom') }}: {{ $kingdom->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('kingdoms.update', $kingdom) }}">
                        @csrf
                        @method('PUT') {{-- Or PATCH --}}

                        <div>
                            <x-input-label for="name" :value="__('Kingdom Name')" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $kingdom->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="description" :value="__('Description (Optional)')" />
                            <textarea id="description" name="description" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">{{ old('description', $kingdom->description) }}</textarea>
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>

                         <div class="mt-4">
                            <x-input-label :value="__('Status')" />
                            <div class="mt-1 space-x-4">
                                 <label for="status_active" class="inline-flex items-center">
                                     <input type="radio" id="status_active" name="is_active" value="1" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" {{ old('is_active', $kingdom->is_active) == 1 ? 'checked' : '' }}>
                                     <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Active') }}</span>
                                 </label>
                                 <label for="status_inactive" class="inline-flex items-center">
                                     <input type="radio" id="status_inactive" name="is_active" value="0" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" {{ old('is_active', $kingdom->is_active) == 0 ? 'checked' : '' }}>
                                      <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Inactive') }}</span>
                                 </label>
                            </div>
                             <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
                        </div>


                        <div class="flex items-center justify-end mt-4">
                             <a href="{{ route('kingdoms.show', $kingdom) }}" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 mr-4">
                                {{ __('Cancel') }}
                            </a>
                            <x-primary-button>
                                {{ __('Update Kingdom') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>