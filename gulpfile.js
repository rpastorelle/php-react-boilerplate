var source = require('vinyl-source-stream');
var gulp = require('gulp');
var gutil = require('gulp-util');
var browserify = require('browserify');
var babelify = require('babelify');
var watchify = require('watchify');
var bowerResolve = require('bower-resolve');
var nodeResolve = require('resolve');

var production = (process.env.NODE_ENV === 'production');

var commonPkgs = [
  'react',
  'react-dom',
];

var BUILD_DIR = './www/build/';
var MAIN_FILE = './www/js/main.js';

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

gulp.task('common', function(){
  var bundler = getBundler(false);

  getBowerPackages().forEach(function (pkg) {
    bundler.require(bowerResolve.fastReadSync(pkg), {expose: pkg});
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
    .pipe(gulp.dest(BUILD_DIR));

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
    .pipe(gulp.dest(BUILD_DIR));

  return stream;
});


gulp.task('watch', function() {
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
      .pipe(gulp.dest(BUILD_DIR));
  }

  // listen for an update and run rebundle
  bundler.on('update', function() {
    rebundle();
    gutil.log('Rebundle...');
  });

  return rebundle();
});

gulp.task('build-all', ['common' ,'build']);

// run 'scripts' task first, then watch for future changes
gulp.task('default', ['build']);
