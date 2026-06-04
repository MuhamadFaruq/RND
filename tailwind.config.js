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
                brand: {
                    DEFAULT: '#ED1C24',
                    50: '#FFF1F1',
                    100: '#FFE1E2',
                    200: '#FFC7C9',
                    300: '#FFA0A3',
                    400: '#FF6B70',
                    500: '#F7383F',
                    600: '#ED1C24',
                    700: '#C81018',
                    800: '#A50F16',
                    900: '#881318',
                    950: '#4B0609',
                },
            },
        },
    },
    safelist: [
        {
        pattern: /(bg|text|border)-(brand|amber|cyan|orange|rose|emerald|green|slate|indigo)-(50|100|200|400|500|600|700|800|900|950)/,
        },
    ],
    plugins: [forms],
};
