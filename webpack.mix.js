const mix = require('laravel-mix');
require('mix-env-file');
const fs = require('fs');

mix.env(process.env.ENV_FILE);

const ARGS = process.argv.slice(2);
const IS_WITH_ESLINT = ARGS.includes('with-eslint');
const IS_WITHOUT_PRETTIER = ARGS.includes('without-prettier');
const IS_ONLY_CRM = ARGS.includes('only-crm');

const ESLINT_EXCLUDE_FILES = [
  'node_modules',
  'resources/js/vendor',
  'resources/js/plugins',
  'resources/crm',
  'resources/whitelabel/js',
  'resources/wordpress/themes/base/js/Lightbox.js',
  'resources/wordpress/themes/base/js/Webfont.js',
  'resources/lotto-platform/js',
];

if (!mix.inProduction()) {
  const ESLintPlugin = require('eslint-webpack-plugin');

  if (IS_WITHOUT_PRETTIER) {
    mix.webpackConfig({
      plugins: [
        new ESLintPlugin({
          exclude: ESLINT_EXCLUDE_FILES,
        }),
      ],
    });
  } else {
    const PrettierPlugin = require('./resources/js/plugins/custom/prettier-webpack-plugin');

    mix.webpackConfig({
      plugins: [
        new ESLintPlugin({
          exclude: ESLINT_EXCLUDE_FILES,
        }),
        new PrettierPlugin(),
      ],
    });
  }
} else {
  if (IS_WITH_ESLINT) {
    const ESLintPlugin = require('eslint-webpack-plugin');
    mix.webpackConfig({
      plugins: [
        new ESLintPlugin({
          exclude: ESLINT_EXCLUDE_FILES,
        }),
      ],
    });
  }
}

mix.setPublicPath('./');
mix.options({
  processCssUrls: false,
  sassOptions: {
    outputStyle: 'expanded',
  },
  terser: {
		extractComments: false,
		terserOptions: {
			output: {
				comments: false,
			},
		},
	},
});

const WP_RESOURCES_THEMES_PATH = 'resources/wordpress/themes/';
const WP_RESOURCES_WIDGETS_PATH = 'resources/widgets/themes/';
const WP_BUILD_PATH = 'wordpress/wp-content/themes/';

const LOTTO_PLATFORM_RESOURCES_PATH = 'resources/lotto-platform';
const LOTTO_PLATFORM_BUILD_PATH =
  'wordpress/wp-content/plugins/lotto-platform/public';
const JS_MODULES_PATH = 'resources/lotto-platform/js/modules';

const WHITELABEL_RESOURCES_PATH = 'resources/whitelabel';
const WHITELABEL_BUILD_PATH = 'platform/public/assets';

const PLUGINS_JS_RESOURCES_PATH = 'resources/js/plugins';
const VENDOR_JS_RESOURCES_PATH = 'resources/js/vendor';

const PLUGINS_SCSS_RESOURCES_PATH = 'resources/scss/plugins';
const VENDOR_SCSS_RESOURCES_PATH = 'resources/scss/vendor';

const CRM_RESOURCES_PATH = 'resources/crm';

const BUILD_ALL_SLUG = 'all';

let themesList = fs.readdirSync(WP_RESOURCES_THEMES_PATH).filter((file) => {
  return fs.statSync(WP_RESOURCES_THEMES_PATH + file).isDirectory();
});

let widgetThemeList = fs
  .readdirSync(WP_RESOURCES_WIDGETS_PATH)
  .filter((file) => {
    return fs.statSync(WP_RESOURCES_WIDGETS_PATH + file).isDirectory();
  });

const isThemeExists = (theme) => {
  if (theme !== BUILD_ALL_SLUG) {
    if (!themesList.includes(theme)) {
      console.error("Provided name of theme doesn't exists.");
      return false;
    }
    themesList = [theme];
    return true;
  }
  return true;
};

const isWidgetExists = (widget) => {
  if (widget !== BUILD_ALL_SLUG) {
    if (!widgetThemeList.includes(widget)) {
      console.error("Provided name of widget doesn't exists.");
      return false;
    }
    widgetThemeList = [widget];
    return true;
  }
  return true;
};

const buildWordpressTemplateScss = (theme) => {
  if (!isThemeExists(theme)) return;

  for (let themeName of themesList) {
    let cssPath = WP_BUILD_PATH + themeName + '/css/style.min.css';
    let scssPath = WP_RESOURCES_THEMES_PATH + themeName + '/style.scss';

    if (themeName === 'whitelotto-2019') {
      mix.copy(
        WP_RESOURCES_THEMES_PATH + themeName + '/css/bootstrap.css',
        WP_BUILD_PATH + themeName + '/css/bootstrap.min.css',
      );
      mix.sass(scssPath, WP_BUILD_PATH + themeName + '/css/app.min.css');
      continue;
    }

    if (themeName === 'base') {
      mix.sass(scssPath, cssPath);
      continue;
    }

    if (!fs.existsSync(scssPath)) {
      continue;
    }

    mix
      .sass(scssPath, cssPath)
      .styles(
        [
          WP_RESOURCES_THEMES_PATH + 'base/css/font-source.css',
          WP_RESOURCES_THEMES_PATH + 'base/css/color-default.css',
          'wordpress/wp-content/themes/base/css/style.min.css',
          cssPath,
        ],
        WP_BUILD_PATH + themeName + '/css/app.min.css',
      )
      .sourceMaps()
      .webpackConfig({
        devtool: 'source-map',
      });
  }
};

const buildWidgetScss = (widget) => {
  if (!isWidgetExists(widget)) return;

  for (let themeName of widgetThemeList) {
    const widgetNames = fs
      .readdirSync(WP_RESOURCES_WIDGETS_PATH + themeName)
      .filter((file) => file.includes('widget'));

    for (let widgetName of widgetNames) {
      let scssPath = WP_RESOURCES_WIDGETS_PATH + themeName + '/' + widgetName;
      let cssPath =
        WP_BUILD_PATH +
        themeName +
        '/css/widgets/' +
        widgetName.replace('scss', 'css');

      if (!fs.existsSync(scssPath)) {
        continue;
      }

      mix.sass(scssPath, cssPath);
    }
  }
};

function buildCrmJs() {
  mix
    .js(
      CRM_RESOURCES_PATH + '/index.js',
      WHITELABEL_BUILD_PATH + '/crm/js/app.min.js',
    )
    .react();

  mix.copy(
    VENDOR_JS_RESOURCES_PATH + '/bootstrap.min.js',
    WHITELABEL_BUILD_PATH + '/crm/js/libs/bootstrap.min.js',
  );
  mix.copy(
    VENDOR_JS_RESOURCES_PATH + '/jquery-1.12.3.min.js',
    WHITELABEL_BUILD_PATH + '/crm/js/libs/jquery-1.12.3.min.js',
  );
}

function buildJs() {
  // crm
  buildCrmJs();

  // wordpress
  for (let themeName of themesList) {
    let jsBuildPath = WP_BUILD_PATH + themeName + '/js';

    if (themeName == 'whitelotto-2019') {
      mix.copy(
        VENDOR_JS_RESOURCES_PATH + '/bootstrap4.1.3.min.js',
        jsBuildPath + '/bootstrap.min.js',
      );
      mix.js(
        WP_RESOURCES_THEMES_PATH + themeName + '/js/main.js',
        jsBuildPath + '/main.min.js',
      );
      continue;
    }

    if (themeName !== 'base') {
      // vendor
      mix.copy(
        WP_RESOURCES_THEMES_PATH + 'base/js/Webfont.js',
        jsBuildPath + '/vendor.min.js',
      );

      // app
      mix
        .js(
          [
            WP_RESOURCES_THEMES_PATH + 'base/js/Theme.js',
            LOTTO_PLATFORM_RESOURCES_PATH + '/js/modules/Lotto.js',
          ],
          jsBuildPath + '/app.min.js',
        )
        .version();

      // raffle
      mix.js(
        [
          LOTTO_PLATFORM_RESOURCES_PATH + '/js/modules/Raffle/Results.js',
        ],
        jsBuildPath + '/raffle.min.js',
      );

      // promote
      mix.js(
          [
            LOTTO_PLATFORM_RESOURCES_PATH + '/js/modules/Promote.js',
          ],
          jsBuildPath + '/promote.min.js',
      );
    }
  }

  // whitelabel
  mix.js(
    WHITELABEL_RESOURCES_PATH + '/js/admin.js',
    WHITELABEL_BUILD_PATH + '/js/admin.min.js',
  );
  mix.js(
      WHITELABEL_RESOURCES_PATH + '/js/InvoiceGenerator.js',
      WHITELABEL_BUILD_PATH + '/js/InvoiceGenerator.min.js',
  );
  mix.js(
      JS_MODULES_PATH + '/SeoWidgets/PickNumbersWidget.js',
      WHITELABEL_BUILD_PATH + '/js/SeoWidgets/PickNumbersWidget.min.js',
  );
  mix.js(
    WHITELABEL_RESOURCES_PATH + '/js/whitelabel.js',
    WHITELABEL_BUILD_PATH + '/js/whitelabel.min.js',
  );

  // base theme
  const BASE_THEME_JS_PATH = WP_RESOURCES_THEMES_PATH + 'base/js/';
  fs.readdirSync(BASE_THEME_JS_PATH).forEach((item) => {
    if (fs.statSync(BASE_THEME_JS_PATH + item).isFile()) {
      const finalFileName = item.replace('.js', '.min.js');

      mix.js(
        WP_RESOURCES_THEMES_PATH + 'base/js/' + item,
        WP_BUILD_PATH + 'base/js/' + finalFileName,
      );
    } else if (fs.statSync(BASE_THEME_JS_PATH + item).isDirectory()) {
      // directories and files in directories
      const BASE_THEME_JS_PATH_2 = BASE_THEME_JS_PATH + item;
      fs.readdirSync(BASE_THEME_JS_PATH_2).forEach((item2) => {
        if (fs.statSync(BASE_THEME_JS_PATH_2 + '/' + item2).isFile()) {
          const finalFileName = item2.replace('.js', '.min.js');

          mix.js(
            WP_RESOURCES_THEMES_PATH + 'base/js/' + item + '/' + item2,
            WP_BUILD_PATH + 'base/js/' + item + '/' + finalFileName,
          );
        }
      });
    }
  });

  // lotto-platform
  mix.copy(
    LOTTO_PLATFORM_RESOURCES_PATH + '/js/admin',
    LOTTO_PLATFORM_BUILD_PATH + '/js/admin',
  );
  mix.copy(
    PLUGINS_JS_RESOURCES_PATH + '/tinymce',
    WP_BUILD_PATH + 'base/js/tinymce',
  );

  // vendor
  mix.copy(
    VENDOR_JS_RESOURCES_PATH + '/bootstrap.min.js',
    WHITELABEL_BUILD_PATH + '/js/bootstrap.min.js',
  );

  mix.copy(
    VENDOR_JS_RESOURCES_PATH + '/jquery-1.12.3.min.js',
    WHITELABEL_BUILD_PATH + '/js/jquery-1.12.3.min.js',
  );

  // lotto-platform
  mix
    .js(
      LOTTO_PLATFORM_RESOURCES_PATH + '/js/admin/widgets.js',
      LOTTO_PLATFORM_BUILD_PATH + '/js/admin/widgets.min.js',
    )
    .js(
      LOTTO_PLATFORM_RESOURCES_PATH + '/js/admin/scripts.js',
      LOTTO_PLATFORM_BUILD_PATH + '/js/admin/scripts.min.js',
    );

  // vendor
  mix.copy(
    VENDOR_JS_RESOURCES_PATH + '/bootstrap.min.js',
    WHITELABEL_BUILD_PATH + '/js/bootstrap.min.js',
  );
  mix.copy(
    VENDOR_JS_RESOURCES_PATH + '/jquery-1.12.3.min.js',
    WHITELABEL_BUILD_PATH + '/js/jquery-1.12.3.min.js',
  );

  // plugins
  mix.copy(
    PLUGINS_JS_RESOURCES_PATH + '/jquery.tablesorter.min.js',
    LOTTO_PLATFORM_BUILD_PATH + '/js/jquery.tablesorter.min.js',
  );
  mix.copy(
    PLUGINS_JS_RESOURCES_PATH + '/bootstrap-datepicker.min.js',
    WHITELABEL_BUILD_PATH + '/js/bootstrap-datepicker.min.js',
  );
  mix.copy(
    PLUGINS_JS_RESOURCES_PATH + '/tinymce',
    WHITELABEL_BUILD_PATH + '/js/tinymce',
  );
  mix.copy(
    PLUGINS_JS_RESOURCES_PATH + '/Slick.min.js',
    WP_BUILD_PATH + 'base/js/slick.min.js',
  );
  mix.copy(
    PLUGINS_JS_RESOURCES_PATH + '/Lightbox.js',
    WP_BUILD_PATH + 'base/js/Lightbox.min.js',
  );

  // MiniGames
  mix.js(
    [
      LOTTO_PLATFORM_RESOURCES_PATH + '/js/modules/MiniGames/GgWorldCoinFlip.js',
    ],
    WP_BUILD_PATH + 'base/js/MiniGames/GgWorldCoinFlip.min.js'
  );
  mix.js(
    [
      LOTTO_PLATFORM_RESOURCES_PATH + '/js/modules/MiniGames/GgWorldTicTacBoo.js',
    ],
    WP_BUILD_PATH + 'base/js/MiniGames/GgWorldTicTacBoo.min.js'
  );
  mix.js(
    [
      LOTTO_PLATFORM_RESOURCES_PATH + '/js/modules/MiniGames/GgWorldSantaInDaHouse.js',
    ],
    WP_BUILD_PATH + 'base/js/MiniGames/GgWorldSantaInDaHouse.min.js'
  );
  mix.js(
    [
      LOTTO_PLATFORM_RESOURCES_PATH + '/js/modules/MiniGames/GgWorldRedOrBlue.js',
    ],
    WP_BUILD_PATH + 'base/js/MiniGames/GgWorldRedOrBlue.min.js'
  );
}

function buildCrmScss() {
  mix.sass(
    CRM_RESOURCES_PATH + '/assets/scss/style.scss',
    WHITELABEL_BUILD_PATH + '/crm/css/style.min.css',
  );
}

const buildScss = () => {
  // crm
  buildCrmScss();

  // admin
  mix.sass(
    LOTTO_PLATFORM_RESOURCES_PATH + '/scss/admin/style.scss',
    LOTTO_PLATFORM_BUILD_PATH + '/css/admin/style.min.css',
  );

  // whitelabel
  mix.sass(
    WHITELABEL_RESOURCES_PATH + '/scss/admin.scss',
    WHITELABEL_BUILD_PATH + '/css/admin.min.css',
  );
  mix.sass(
      WHITELABEL_RESOURCES_PATH + '/scss/SeoWidgets/PickNumbers.scss',
      WHITELABEL_BUILD_PATH + '/css/SeoWidgets/PickNumbers.min.css',
  );

  // vendor
  mix.sass(
    VENDOR_SCSS_RESOURCES_PATH + '/bootstrap.scss',
    WHITELABEL_BUILD_PATH + '/css/bootstrap.min.css',
  );
  mix.sass(
    VENDOR_SCSS_RESOURCES_PATH + '/bootstrap-theme.scss',
    WHITELABEL_BUILD_PATH + '/css/bootstrap-theme.min.css',
  );

  // plugins
  mix.sass(
    PLUGINS_SCSS_RESOURCES_PATH + '/bootstrap-datepicker3.min.scss',
    WHITELABEL_BUILD_PATH + '/css/bootstrap-datepicker3.min.css',
  );
  mix.sass(
    PLUGINS_SCSS_RESOURCES_PATH + '/slick.scss',
    WP_BUILD_PATH + 'base/css/slick.min.css',
  );
  mix.sass(
    PLUGINS_SCSS_RESOURCES_PATH + '/Lightbox.scss',
    WP_BUILD_PATH + 'base/css/Lightbox.min.css',
  );
  mix.sass(
    PLUGINS_SCSS_RESOURCES_PATH + '/jquery-ui.scss',
    WP_BUILD_PATH + 'base/css/jquery-ui.min.css',
  );

  // MiniGames
  mix.sass(
    WP_RESOURCES_THEMES_PATH + 'base/scss/MiniGames/GgWorldCoinFlip/theme.scss',
    WP_BUILD_PATH + 'base/css/MiniGames/GgWorldCoinFlip.min.css'
  );
  mix.sass(
    WP_RESOURCES_THEMES_PATH + 'base/scss/MiniGames/GgWorldTicTacBoo/theme.scss',
    WP_BUILD_PATH + 'base/css/MiniGames/GgWorldTicTacBoo.min.css'
  );
  mix.sass(
    WP_RESOURCES_THEMES_PATH + 'base/scss/MiniGames/GgWorldSantaInDaHouse/theme.scss',
    WP_BUILD_PATH + 'base/css/MiniGames/GgWorldSantaInDaHouse.min.css'
  );
  mix.sass(
    WP_RESOURCES_THEMES_PATH + 'base/scss/MiniGames/GgWorldRedOrBlue/theme.scss',
    WP_BUILD_PATH + 'base/css/MiniGames/GgWorldRedOrBlue.min.css'
  );
};

const copyImages = () => {
  // lottoplatform
  mix.copy(
    LOTTO_PLATFORM_RESOURCES_PATH + '/images',
    LOTTO_PLATFORM_BUILD_PATH + '/images',
  );

  // whitelabel
  mix.copy(
    WHITELABEL_RESOURCES_PATH + '/images',
    WHITELABEL_BUILD_PATH + '/images',
  );

  // wordpress
  for (let themeName of themesList) {
    let resurcesImagePath = WP_RESOURCES_THEMES_PATH + themeName + '/images';
    if (fs.existsSync(resurcesImagePath)) {
      mix.copy(resurcesImagePath, WP_BUILD_PATH + themeName + '/images');
    }
  }

  // marketing tools
  mix.copy(
    'resources/bannerTemplates',
    WHITELABEL_BUILD_PATH + '/images/bannerTemplates',
  );
};

const copyFonts = () => {
  // font-awesome
  mix.copy(
    'node_modules/@fortawesome/fontawesome-free/webfonts',
    WP_BUILD_PATH + 'base/webfonts',
  );

  // whitelabel
  mix.copy(
    WHITELABEL_RESOURCES_PATH + '/fonts',
    WHITELABEL_BUILD_PATH + '/fonts',
  );

  // wordpress (only base)
  mix.copy(
    WP_RESOURCES_THEMES_PATH + 'base/fonts',
    WP_BUILD_PATH + 'base/fonts',
  );
};

const copyAudio = () => {
  // wordpress (only base)
  mix.copy(
    WP_RESOURCES_THEMES_PATH + 'base/audio',
    WP_BUILD_PATH + 'base/audio',
  );
};

const copyCrmAssets = () => {
  mix.copy(
    CRM_RESOURCES_PATH + '/assets/css',
    WHITELABEL_BUILD_PATH + '/crm/css',
  );
  mix.copy(
    CRM_RESOURCES_PATH + '/assets/icons',
    WHITELABEL_BUILD_PATH + '/crm/icons',
  );
  mix.copy(
    CRM_RESOURCES_PATH + '/libs',
    WHITELABEL_BUILD_PATH + '/crm/js/libs',
  );
};

if (!IS_ONLY_CRM) {
  buildWordpressTemplateScss(BUILD_ALL_SLUG);
  buildWidgetScss(BUILD_ALL_SLUG);
  buildJs();
  buildScss();
  copyImages();
  copyFonts();
  copyAudio();
  copyCrmAssets();
} else {
  buildCrmScss();
  buildCrmJs();
  copyCrmAssets();
}


mix.version();
