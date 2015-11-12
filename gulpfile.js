var source = require('vinyl-source-stream');
var gulp = require('gulp');
var gutil = require('gulp-util');
var fs = require('fs');
var browserify = require('browserify');
var babelify = require('babelify');
var watchify = require('watchify');
var bowerResolve = require('bower-resolve');
var nodeResolve = require('resolve');
var less = require('gulp-less');
var importify = require('gulp-importify');
var minifyCss = require('gulp-minify-css');

var production = (process.env.NODE_ENV === 'production');

var JS_BUILD_DIR = './public/build/js/';
var CSS_BUILD_DIR = './public/build/css/';
var MAIN_FILE = './resources/assets/js/main.js';
var appCssFiles = './resources/assets/less/**/*.less';

var commonCss = [
  './bower_components/bootstrap/dist/css/bootstrap.css',
];

function getBundler(watch) {
  var props = {
    debug: !production,
    transform: [babelify],
  };

  return watch ? watchify(browserify(props)) : browserify(props);
}

function getNPMPackages() {
  var pkg;
  try {
    pkg = require('./package.json');
  } catch (e) { /* pass */ }

  return Object.keys(pkg.dependencies) || [];
}

function getBowerPackages() {
  var pkg;
  try {
    pkg = require('./bower.json');
  } catch (e) { /* pass */ }

  return Object.keys(pkg.dependencies) || [];
}

function bundleCss(src, name) {
  return gulp.src(src)
    .pipe(importify(name+'.less', {cssPreproc: 'less'}))
    .pipe(less())
    .pipe(minifyCss())
    .pipe(gulp.dest(CSS_BUILD_DIR));
}

gulp.task('common', function(){
  var bundler = getBundler(false);

  getBowerPackages().forEach(function (pkg) {
    var resolvedPath = bowerResolve.fastReadSync(pkg);
    try {
      fs.accessSync(resolvedPath, fs.R_OK);
      bundler.require(resolvedPath, {expose: pkg});
    } catch (e) {
      // no op: no readable file exists
    }
  });

  getNPMPackages().forEach(function (pkg) {
    bundler.require(nodeResolve.sync(pkg), {expose: pkg});
  });

  var stream = bundler.bundle()
    .on('error', function(err){
      console.log(err.message);
      this.emit('end');
    })
    .pipe(source('common.js'))
    // Do thingly like uglify here
    .pipe(gulp.dest(JS_BUILD_DIR));

  return stream;
});


gulp.task('build', function() {
  var bundler = getBundler(false);
  bundler.add(MAIN_FILE);

  getBowerPackages().forEach(function (pkg) {
    bundler.external(pkg);
  });

  getNPMPackages().forEach(function (pkg) {
    bundler.external(pkg);
  });

  var stream = bundler.bundle()
    .on('error', function(err){
      console.log(err.message);
      this.emit('end');
    })
    .pipe(source('main.js'))
    // Do thingly like uglify here
    .pipe(gulp.dest(JS_BUILD_DIR));

  return stream;
});


gulp.task('watch-js', function() {
  var bundler = getBundler(true);
  bundler.add(MAIN_FILE);

  getBowerPackages().forEach(function (pkg) {
    bundler.external(pkg);
  });

  getNPMPackages().forEach(function (pkg) {
    bundler.external(pkg);
  });

  function rebundle() {
    var stream = bundler.bundle();
    return stream
      .on('error', function(err){
        console.log(err.message);
        this.emit('end');
      })
      .pipe(source('main.js'))
      .pipe(gulp.dest(JS_BUILD_DIR));
  }

  // listen for an update and run rebundle
  bundler.on('update', function() {
    rebundle();
    gutil.log('Rebundle...');
  });

  return rebundle();
});

gulp.task('watch-css', function () {
  gulp.watch(appCssFiles, ['css']);
});

gulp.task('watch', ['watch-js', 'watch-css']);

gulp.task('css', function() {
  return bundleCss(appCssFiles, 'main');
});

gulp.task('common-css', function() {
  return bundleCss(commonCss, 'common');
});

gulp.task('build-all', ['common' ,'build', 'common-css' ,'css']);

// run 'scripts' task first, then watch for future changes
gulp.task('default', ['build']);
