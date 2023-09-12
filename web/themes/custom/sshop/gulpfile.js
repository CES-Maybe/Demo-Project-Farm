'use strict';

// The streaming build system.
var gulp = require('gulp');

// Gulp plugin for sass.
var sass = require('gulp-sass');

// // Source map support for Gulp.js.
// var sourcemaps = require('gulp-sourcemaps');

// Prefix CSS.
var autoprefixer = require('gulp-autoprefixer');

// Prevent pipe breaking caused by errors from gulp plugins.
var plumber = require('gulp-plumber');

// Live CSS Reload & Browser Syncing.
var browserSync = require('browser-sync').create();

// Rename files
var rename = require("gulp-rename");

// Allows you to use glob syntax in imports (i.e. @import "dir/*.sass"). Use as a custom importer for node-sass.
var importer = require('node-sass-globbing');

// Drupal Breakpoints.
const drupalBreakpoints = require('drupal-breakpoints-scss')
gulp.task('breakpoints', function () {
  return gulp.src('./sshop.breakpoints.yml')
    .pipe(drupalBreakpoints.ymlToScss())
    .pipe(rename('_breakpoints.scss'))
    .pipe(gulp.dest('./styles/scss'))
})

// Default settings.
var src = {
  root: '.',
  scss: './scss/*.scss',
  css: './css',
  watch: './scss/**',
  proxy: 'sshop.loc'
};

// List of browsers. Opera 15 for prefix -webkit.
var browsers = ['last 3 versions', 'Opera 15'];

// For developers. Contain better outputStyle for reading.
gulp.task('dev', function () {
  gulp.src(src.scss)
    .pipe(plumber())
    // .pipe(sourcemaps.init())
    .pipe(sass({
      importer: importer,
      includePaths: [
        './node_modules/breakpoint-sass/stylesheets/',
        './node_modules/compass-mixins/lib/'
      ],
      outputStyle: 'expanded',
      sourceComments: false
    }).on('error', sass.logError))
    .pipe(autoprefixer({
      browsers: browsers
    }))
    // .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(src.css))
    .pipe(browserSync.stream());
});

// Minifed css styles.
gulp.task('prod', function () {
  gulp.src(src.scss)
    .pipe(plumber())
    .pipe(sass({
      importer: importer,
      includePaths: [
        './node_modules/breakpoint-sass/stylesheets/',
        './node_modules/compass-mixins/lib/'
      ],
      outputStyle: 'compressed'
    }).on('error', sass.logError))
    .pipe(autoprefixer({
      browsers: browsers,
      cascade: false
    }))
    // .pipe(rename({suffix: '.min'}))
    .pipe(gulp.dest(src.css));
});

// Watch task.
gulp.task('watch', function () {
  gulp.watch(src.watch, ['dev']);
});

// Default task.
gulp.task('default', ['dev'], function () {

  browserSync.init({
    // Using a vhost-based url.
    proxy: src.proxy
  });

  gulp.watch(src.watch, ['dev']).on('change', browserSync.reload);
});
