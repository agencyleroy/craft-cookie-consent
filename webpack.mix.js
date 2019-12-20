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

// Temporary workaround for https://github.com/JeffreyWay/laravel-mix/issues/2235
Mix.listen('configReady', webpackConfig => {
  webpackConfig.module.rules.forEach(rule => {
    if (Array.isArray(rule.use)) {
      rule.use.forEach(ruleUse => {
        if (ruleUse.loader === 'resolve-url-loader') {
          ruleUse.options.engine = 'postcss';
        }
      });
    }
  });
});
