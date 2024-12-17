import defaultTheme from 'tailwindcss/defaultTheme';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
    ],
    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
        keyframes: {
            slide: {
              '0%': { flex: '30%' },
              '100%': { flex: '60%' }
            },
            slideOut: {
              '0%': { flex: '70%' },
              '100%': { flex: '40%' }
            }
          },
          animation: {
            slide: 'slide 1s ease forwards',
            slideOut: 'slideOut 1s ease forwards'
          }
    },
    plugins: [],
};
