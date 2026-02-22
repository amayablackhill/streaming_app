const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            colors: {
                cc: {
                    bg: {
                        primary: '#141414',
                        surface: '#1C1C1C',
                        elevated: '#222222',
                    },
                    text: {
                        primary: '#E8E6E3',
                        secondary: '#B8B5B0',
                        muted: '#8A8782',
                    },
                    accent: {
                        DEFAULT: '#3E4A3F',
                        olive: '#3E4A3F',
                        burgundy: '#5C2E2E',
                        petrol: '#1F3A44',
                        terracotta: '#6B3F2B',
                    },
                    border: 'rgba(255,255,255,0.06)',
                },
            },
            borderRadius: {
                sm: '2px',
                DEFAULT: '3px',
                md: '4px',
                lg: '4px',
                xl: '4px',
            },
            spacing: {
                'cc-1': '0.25rem',
                'cc-2': '0.5rem',
                'cc-3': '0.75rem',
                'cc-4': '1rem',
                'cc-6': '1.5rem',
                'cc-8': '2rem',
                'cc-10': '2.5rem',
                'cc-12': '3rem',
            },
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                serif: ['"Cormorant Garamond"', '"Iowan Old Style"', '"Times New Roman"', 'serif'],
            },
            fontSize: {
                'cc-display': ['2.5rem', { lineHeight: '1.1', letterSpacing: '-0.02em' }],
                'cc-title': ['1.75rem', { lineHeight: '1.2', letterSpacing: '-0.01em' }],
                'cc-subtitle': ['1.25rem', { lineHeight: '1.3' }],
                'cc-body': ['1rem', { lineHeight: '1.6' }],
                'cc-caption': ['0.75rem', { lineHeight: '1.4', letterSpacing: '0.08em' }],
            },
            lineHeight: {
                editorial: '1.6',
                tightish: '1.25',
            },
            letterSpacing: {
                editorial: '-0.01em',
                label: '0.08em',
            },
            transitionDuration: {
                150: '150ms',
                200: '200ms',
                250: '250ms',
            },
            transitionTimingFunction: {
                soft: 'cubic-bezier(0.16, 1, 0.3, 1)',
            },
            boxShadow: {
                subtle: '0 1px 0 rgba(255,255,255,0.04)',
                inset: 'inset 0 1px 0 rgba(255,255,255,0.02)',
            },
        },
    },

    plugins: [require('@tailwindcss/forms')],
};
