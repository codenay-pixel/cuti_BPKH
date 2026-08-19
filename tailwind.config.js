import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: '#e8f0ee',
                    100: '#c5dad3',
                    200: '#9ec2b5',
                    300: '#76aa97',
                    400: '#589880',
                    500: '#3a866a',
                    600: '#1B4D3E',
                    700: '#154033',
                    800: '#103328',
                    900: '#08261d',
                },
                accent: {
                    400: '#d4b84a',
                    500: '#C9A227',
                    600: '#a8871f',
                },
            },
        },
    },

    plugins: [forms],
};