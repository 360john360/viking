<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        {{-- Head content as before (meta, title, fonts, FA, vite) --}}
         <meta charset="utf-8">
         <meta name="viewport" content="width=device-width, initial-scale=1">
         <meta name="csrf-token" content="{{ csrf_token() }}">
         <title>{{ config('app.name', 'Laravel') }}</title>
         <link rel="preconnect" href="https://fonts.bunny.net">
         <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
         <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
         @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-viking-dark text-viking-parchment">
        <div class="min-h-screen flex flex-col"> {{-- Changed to flex flex-col --}}
            @include('layouts.navigation')

            @if (isset($header))
                <header class="bg-viking-wood shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main class="flex-grow"> {{-- Added flex-grow to push footer down --}}
                {{-- Flashed Session Messages Section --}}
                <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 pt-6">
                    @if (session('status')) <div class="mb-4 p-4 bg-blue-100 dark:bg-blue-800 border border-blue-300 dark:border-blue-700 text-blue-700 dark:text-blue-200 rounded-lg shadow" role="alert"> {{ session('status') }} </div> @endif
                    @if (session('success')) <div class="mb-4 p-4 bg-green-100 dark:bg-green-800 border border-green-300 dark:border-green-700 text-green-700 dark:text-green-200 rounded-lg shadow" role="alert"> {{ session('success') }} </div> @endif
                    @if (session('error')) <div class="mb-4 p-4 bg-red-100 dark:bg-red-800 border border-red-300 dark:border-red-700 text-red-700 dark:text-red-200 rounded-lg shadow" role="alert"> {{ session('error') }} </div> @endif
                    @if (session('warning')) <div class="mb-4 p-4 bg-yellow-100 dark:bg-yellow-800 border border-yellow-300 dark:border-yellow-700 text-yellow-700 dark:text-yellow-200 rounded-lg shadow" role="alert"> {{ session('warning') }} </div> @endif
                    @if (session('info')) <div class="mb-4 p-4 bg-gray-200 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-800 dark:text-gray-200 rounded-lg shadow" role="alert"> {{ session('info') }} </div> @endif
                </div>
                 {{-- === END Flashed Message Section === --}}

                {{ $slot }}
            </main>

             {{-- ADDED: Basic Footer --}}
             <footer class="bg-viking-wood mt-auto border-t border-viking-steel/50 shadow-inner"> {{-- mt-auto pushes footer down --}}
                  <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 text-center text-xs text-viking-steel">
                     &copy; {{ date('Y') }} {{ config('app.name', 'Viking Stronghold') }}. All Rights Reserved. Skål!
                     {{-- Add other footer links if needed later --}}
                  </div>
             </footer>
             {{-- END ADDED Footer --}}

        </div> {{-- End min-h-screen --}}
    </body>
</html>