var gulp =  require('gulp');
var del =   require('del');
var paths = require('../paths');

gulp.task('clean', function () {
  del([paths.styles.tmpDir, paths.distDir]).then(function () {
      console.log('Temp and dist directories deleted.');
  });
});
