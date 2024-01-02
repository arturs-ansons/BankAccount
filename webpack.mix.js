const mix = require('laravel-mix');
const tailwindcss = require('tailwindcss'); /* Add this line at the top */

require('laravel-mix-artisan-serve');

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .options({
        postCss: [ tailwindcss('./tailwind.config.js') ],
    })
    .serve({
        host: '0.0.0.0',
        port: 8000
    });
