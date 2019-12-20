let mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for your application, as well as bundling up your JS files.
 |
 */

mix
    .js('src/assetbundles/src/js/cookieconsent.js', 'src/assetbundles/dist/js/')
    .sass('src/assetbundles/src/scss/cookieconsent.scss', 'src/assetbundles/dist/css/');
