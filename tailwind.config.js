import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                background: 'var(--mkt-bg)',
                surface: 'var(--mkt-surface)',
                'surface-alt': 'var(--mkt-surface-alt)',
                border: 'var(--mkt-border)',
                'app-text': 'var(--mkt-text)',
                'app-text-muted': 'var(--mkt-text-muted)',
                'input-bg': 'var(--mkt-input-bg)',
            },
        },
    },
    safelist: [
        {
        pattern: /(bg|text|border)-(blue|amber|cyan|indigo|violet|orange|rose|emerald|green)-(100|400|500|600)/,
        },
    ],
    plugins: [forms],
};
