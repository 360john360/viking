<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-viking-parchment leading-tight flex items-center space-x-3">
            <i class="fa-solid fa-crown text-viking-gold text-2xl"></i>
            <span>{{ __('Claim Throne for') }} {{ $kingdom->name }}</span>
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-viking-wood/90 border border-viking-steel/40 shadow-lg rounded-lg overflow-hidden">
                <div class="p-6 md:p-8">
                    <h3 class="text-2xl font-cinzel font-semibold text-viking-gold mb-6 text-center">
                        Stake Your Claim to {{ $kingdom->name }}
                    </h3>

                    <p class="text-md text-viking-parchment mb-4">
                        You are about to declare your intention to rule the kingdom of <strong class="text-viking-gold">{{ $kingdom->name }}</strong>.
                        Your claim will be recorded and awaits the judgment of fate (or administrators).
                    </p>
                    <p class="text-sm text-viking-steel mb-6">
                        Ensure your reasoning is sound, for your words may echo in the halls of this realm.
                    </p>

                    <form method="POST" action="{{ route('king_claims.store', $kingdom) }}">
                        @csrf

                        <div>
                            <x-input-label for="reasoning" :value="__('Your Reasoning (Optional)')" class="text-viking-parchment" />
                            <textarea id="reasoning" name="reasoning" rows="6" class="block mt-1 w-full bg-viking-dark/50 border-viking-steel text-viking-parchment rounded-md shadow-sm focus:border-viking-gold focus:ring focus:ring-viking-gold focus:ring-opacity-50">{{ old('reasoning') }}</textarea>
                            <x-input-error :messages="$errors->get('reasoning')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-8">
                            <a href="{{ route('kingdoms.show', $kingdom) }}" class="text-sm text-viking-steel hover:text-viking-parchment underline mr-4">
                                {{ __('Cancel') }}
                            </a>

                            <x-primary-button type="submit" class="bg-viking-gold hover:bg-viking-gold-dark text-viking-dark font-bold">
                                <i class="fa-solid fa-scroll-old mr-2"></i>
                                {{ __('Submit Claim') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
