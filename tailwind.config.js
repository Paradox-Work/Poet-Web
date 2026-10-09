import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import headlessui from '@headlessui/tailwindcss';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: [
                    'Inter',
                    ...defaultTheme.fontFamily.sans,
                ],

                serif: [
                    'Playfair Display',
                    'Georgia',
                    'Cambria',
                    'Times New Roman',
                    'serif',
                ],
            },
        },
    },

    plugins: [
        forms,
        headlessui({ prefix: 'ui' }),
    ],
};
