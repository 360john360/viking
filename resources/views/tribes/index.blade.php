<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-viking-parchment leading-tight">
            {{ __('Known Tribes') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-viking-wood/90 shadow-lg rounded-lg overflow-hidden">
                <div class="p-6 text-viking-parchment">

                    @can('create', App\Models\Tribe::class)
                        <div class="mb-4 text-right">
                            <a href="{{ route('tribes.create') }}"
                               class="inline-flex items-center px-4 py-2 bg-viking-green border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-viking-green/80 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 dark:focus:ring-offset-viking-dark transition ease-in-out duration-150 shadow hover:shadow-md active:scale-95">
                                <i class="fa-solid fa-plus mr-1"></i> Establish New Tribe
                            </a>
                        </div>
                    @endcan

                    @if ($tribes->count())
                        <div class="overflow-x-auto relative border border-viking-steel/30 rounded-md">
                            <table class="w-full text-sm text-left text-viking-parchment">
                                <thead class="text-xs text-viking-steel uppercase bg-viking-dark/50">
                                    <tr>
                                        <th scope="col" class="py-3 px-6">Tribe Name</th>
                                        <th scope="col" class="py-3 px-6">Parent Kingdom</th>
                                        <th scope="col" class="py-3 px-6">Leader (Thane)</th>
                                        <th scope="col" class="py-3 px-6 text-right">Actions</th> {{-- Align right --}}
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($tribes as $tribe)
                                        {{-- Only show active tribes? Controller query handles this via kingdom status --}}
                                        {{-- Add check for tribe->is_active if index should hide inactive --}}
                                         <tr class="bg-viking-wood/80 border-b border-viking-steel/30 hover:bg-viking-stone/50 {{ !$tribe->is_active ? 'opacity-50' : '' }}"> {{-- Fade inactive --}}
                                            <th scope="row" class="py-4 px-6 font-medium whitespace-nowrap {{ !$tribe->is_active ? 'line-through' : '' }}">
                                                 <a href="{{ route('tribes.show', $tribe) }}" class="hover:underline text-viking-gold">
                                                    {{ $tribe->name }}
                                                </a>
                                                 @if(!$tribe->is_active) <span class="text-xs text-red-500 italic ml-1">(Inactive)</span> @endif
                                            </th>
                                            <td class="py-4 px-6">
                                                 @if($tribe->kingdom)
                                                     <a href="{{ route('kingdoms.show', $tribe->kingdom) }}" class="hover:underline text-viking-blue">
                                                         {{ $tribe->kingdom->name }}
                                                     </a>
                                                 @else
                                                     <span class="text-viking-steel italic">Unknown</span>
                                                 @endif
                                            </td>
                                             <td class="py-4 px-6">
                                                 {{ $tribe->leader?->name ?? 'None Assigned' }}
                                             </td>
                                            <td class="py-4 px-6 text-right space-x-2">
                                                {{-- View Link --}}
                                                @can('view', $tribe) {{-- Check policy before showing link --}}
                                                    <a href="{{ route('tribes.show', $tribe) }}" class="font-medium text-viking-blue hover:underline text-xs">View</a>
                                                @else
                                                    <span class="text-viking-steel text-xs italic">Cannot View</span>
                                                @endcan

                                                 {{-- Edit Link --}}
                                                 @can('update', $tribe)
                                                     <a href="{{ route('tribes.edit', $tribe) }}" class="font-medium text-viking-gold hover:underline text-xs">Edit</a>
                                                 @endcan

                                                 {{-- Deactivate/Delete Placeholder --}}
                                                  @can('delete', $tribe)
                                                     {{-- <form method="POST" action="{{ route('tribes.destroy', $tribe) }}" class="inline"> @csrf @method('DELETE') <button...>...</button> </form> --}}
                                                  @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-6">
                            {{ $tribes->links() }}
                        </div>

                    @else
                        <p class="text-center text-viking-steel italic py-6">No active tribes found in active kingdoms.</p>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-app-layout>