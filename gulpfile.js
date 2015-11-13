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
var rename = require('gulp-rename');
var argv = require('yargs').argv;

var production = (process.env.NODE_ENV === 'production');

var JS_BUILD_DIR = './public/build/js/';
var CSS_BUILD_DIR = './public/build/css/';
var MAIN_FILE = './resources/js/main.js';
var appCssFiles = './resources/less/**/*.less';
var assetManifest = require('./asset-manifest.json');

var commonCss = [
  './bower_components/bootstrap/dist/css/bootstrap.css',
];


var VERSION = Math.floor(Date.now() / 1000);

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


function updateManifest(sourceFile, outputFile) {
  if (! argv.production) return;

  if (assetManifest.hasOwnProperty(sourceFile)) {
    assetManifest[sourceFile] = outputFile;
  }

  fs.writeFileSync('./asset-manifest.json', JSON.stringify(assetManifest, null, 4));
}


function bundleCss(src, name) {
  var sourceFile = outputFile = name+'.css';
  if (argv.production) outputFile = name+'-'+VERSION+'.css';

  var stream = gulp.src(src)
    .pipe(importify(name+'.less', {cssPreproc: 'less'}))
    .pipe(less())
    .pipe(minifyCss())
    .pipe(rename(outputFile))
    .pipe(gulp.dest(CSS_BUILD_DIR));

  updateManifest(sourceFile, outputFile);

  return stream;
}

gulp.task('common', function(){
  var sourceFile = outputFile = 'common.js';
  if (argv.production) outputFile = 'common-'+VERSION+'.js';

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
    .pipe(source(sourceFile))
    // Do thingly like uglify here
    .pipe(rename(outputFile))
    .pipe(gulp.dest(JS_BUILD_DIR));

  updateManifest(sourceFile, outputFile);

  return stream;
});


gulp.task('build', function() {
  var sourceFile = outputFile = 'main.js';
  if (argv.production) outputFile = 'main-'+VERSION+'.js';

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
    .pipe(source(sourceFile))
    // Do thingly like uglify here
    .pipe(rename(outputFile))
    .pipe(gulp.dest(JS_BUILD_DIR));

    updateManifest(sourceFile, outputFile);

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
