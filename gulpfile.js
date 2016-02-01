var $      = require('gulp-load-plugins')();
var argv   = require('yargs').argv;
var gulp   = require('gulp');
var rimraf = require('rimraf');
var sequence = require('run-sequence');
var sassGlob = require('gulp-sass-glob');
var copy = require('gulp-copy');
var uglify = require('gulp-uglify');
var minifyCss = require('gulp-cssnano');
var rename = require('gulp-rename');
var replace = require('gulp-replace');

// LOCAL Variable MUST be set to a your development instance of Wordpress
// Recommended default WP Install location, one directory above Cartopress Repo
var LOCAL = "../wordpress/wp-content/plugins/"

// Port to use for the development server.
var PORT = 80;

// Browsers to target when prefixing CSS.
var COMPATIBILITY = ['last 2 versions', 'ie >= 9'];

// File paths to various assets are defined here.
var PATHS = {
  assets: [
    'src/cartopress/admin/{images,css,fonts}/**/*'
    'src/cartopress/readme.txt'
  ],
  WPCode: [
    'src/cartopress/**/*.php'
  ],
  sass: [
    // No independent SASS libraries currently in function
  ],
  javascript: [
    'bower_components/Leaflet.awesome-markers/dist/leaflet.awesome-markers.min.js',
    'src/cartopress/admin/js/*.js',
  ],
  wpDev: {
    local: LOCAL,
    css: LOCAL + 'cartopress/admin/css',
    javascript: LOCAL + 'cartopress/admin/js',
    images: LOCAL + 'cartopress/admin/images'
  },
  output: "dist/"
};


// Delete the "dist" folder
// This happens every time a build starts
gulp.task('clean', function(done) {
  rimraf(PATHS.ouput, function(){
  	rimraf(PATHS.wpDev.local + 'cartopress', done);
  });
});

// Copy files out of the assets folder
// This task specifically addresses any items in the images, css, & fonts folders.
gulp.task('copy', function() {
  // Captures Assets
  gulp.src(PATHS.assets)
    .pipe(gulp.dest(PATHS.wpDev.local+'/cartopress/admin'))
    .pipe(gulp.dest(PATHS.output+'cartopress/admin'));
  // Captures PHP Files
  gulp.src(PATHS.WPCode)
    .pipe(gulp.dest(PATHS.wpDev.local+'cartopress'))
    .pipe(gulp.dest(PATHS.output+'cartopress'))
});

// Compile Sass into CSS
// In production, the CSS is compressed
gulp.task('sass', function() {
//  var uncss = $.if(isProduction, $.uncss({
//    html: ['src/**/*.html'],
//    ignore: [
//      new RegExp('^meta\..*'),
//      new RegExp('^\.is-.*')
//    ]
//  }));

//  var minifycss = $.if(isProduction, $.minifyCss());

  return gulp.src('src/assets/scss/app.scss')
    .pipe(sassGlob())
    .pipe($.sourcemaps.init())
    .pipe($.sass({
      //includePaths: //PATHS.sass
    })
      .on('error', $.sass.logError))
    .pipe($.autoprefixer({
      browsers: COMPATIBILITY
    }))
    //.pipe(uncss) Causing issues when in production
    //.pipe(minifycss)
    //.pipe($.if(!isProduction, $.sourcemaps.write()))
    .pipe(gulp.dest(PATHS.wpDev.css))
    .pipe(gulp.dest(PATHS.output + 'cartopress/admin/css'))
});

// Combine JavaScript into one file
// In production, the file is minified
gulp.task('javascript', function() {
//  var uglify = $.if(isProduction, $.uglify()
//    .on('error', function (e) {
//    }));

  return gulp.src(PATHS.javascript)
    .pipe($.sourcemaps.init())
    .pipe($.concat('app.js'))
    //.pipe(uglify)
    //.pipe($.if(!isProduction, $.sourcemaps.write()))
    .pipe(gulp.dest(PATHS.wpDev.javascript))
    .pipe(gulp.dest(PATHS.output + 'cartopress/admin/js'))
});

// Build the dist, and watch for file changes
gulp.task('watch', function() {
  gulp.watch(['./src/cartopress/admin/scss/**/*.scss'], ['sass']);
  gulp.watch(['./src/cartopress/admin/js/**/*.js'], ['javascript']);
});


// WRAP GULP FUNCTIONS TOGETHER IN QUEUE
gulp.task('default', function(done){
  sequence('copy', ['sass', 'javascript'], 'watch', done);
});


gulp.task('minify-js', function() {
  gulp.src('dist/assets/js/app.js')
    .pipe(uglify())
    .pipe(rename({
      suffix: '.min'
    }))
    .pipe(gulp.dest(PATHS.output.javascript))
});

gulp.task('minify-css', function() {
  return gulp.src('dist/assets/css/app.css')
    .pipe(minifyCss({compatibility: 'ie8'}))
    .pipe(rename({
      suffix: '.min'
    }))
    .pipe(gulp.dest(PATHS.output.css));
});
