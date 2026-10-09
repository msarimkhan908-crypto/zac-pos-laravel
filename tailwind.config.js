import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

const cssVar = (name) => `rgb(var(${name}) / <alpha-value>)`;

const colorScale = (prefix) => ({
    50:  cssVar(`--color-${prefix}-50`),
    100: cssVar(`--color-${prefix}-100`),
    200: cssVar(`--color-${prefix}-200`),
    300: cssVar(`--color-${prefix}-300`),
    400: cssVar(`--color-${prefix}-400`),
    500: cssVar(`--color-${prefix}-500`),
    600: cssVar(`--color-${prefix}-600`),
    700: cssVar(`--color-${prefix}-700`),
    800: cssVar(`--color-${prefix}-800`),
    900: cssVar(`--color-${prefix}-900`),
    contrast: cssVar(`--color-${prefix}-contrast`),
});

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
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
                primary: colorScale('primary'),
                accent:  colorScale('accent'),
            },
        },
    },

    plugins: [forms],
};
