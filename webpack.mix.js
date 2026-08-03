const mix = require('laravel-mix');

mix.js('resources/js/app.js', 'public/js').vue({ version: 2 })
    .js('resources/js/admin.js', 'public/js').vue({ version: 2 })
    .sass('resources/sass/app.scss', 'public/css')
    .sass('resources/sass/admin.scss', 'public/css')
    .version();
