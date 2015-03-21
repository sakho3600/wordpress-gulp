var gulp = require('gulp');
var gulpIf = require('gulp-if');
var uglify = require('gulp-uglify');
var minifyCss = require('gulp-minify-css');

gulp.task('copy', function () {
  return gulp.src([
    'src/**/*',
    '!src/assets/styles/**/*'
  ], {
    dot: true
  })
  .pipe(gulpIf('*.js', uglify()))
  .pipe(gulpIf('*.css', minifyCss()))
  .pipe(gulp.dest('dist'));
});