import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    // We are forcing dark mode via HTML class for now
    // darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                cinzel: ['Cinzel', 'serif'],
            },
            // ADDED: Custom Viking color palette
            colors: {
                'viking-dark': '#1a202c',    // Very dark gray/charcoal (like dark:bg-gray-900)
                'viking-wood': '#3a2e27',    // Dark wood brown for cards/accents
                'viking-stone': '#4a5568',   // Medium-dark stone gray
                'viking-steel': '#a0aec0',   // Lighter steel gray for text/borders
                'viking-parchment': '#e2e8f0',// Off-white/light gray for primary text
                'viking-blood': '#c53030',   // Dark red for danger/accents
                'viking-gold': '#d69e2e',    // Gold/Yellow for highlights
                'viking-green': '#2f855a',   // Forest green for success
                'viking-blue': '#2b6cb0',    // Deep blue for info/links
            }
            // END ADDED
        },
    },

    plugins: [forms],
};