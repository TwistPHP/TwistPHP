var gulp = require( 'gulp' ),
		babel = require( 'gulp-babel' ),
		jshint = require( 'gulp-jshint' ),
		sass = require( 'gulp-sass' ),
		concat = require( 'gulp-concat' ),
		uglify = require( 'gulp-uglify' ),
		rename = require( 'gulp-rename' ),
		jsdoc = require( 'gulp-jsdoc3' ),
		sourcemaps = require( 'gulp-sourcemaps' ),
		strTwistSource = './src/Core/Resources/twist/',
		strTwistDestination = './dist/twist/Core/Resources/twist/';

gulp.task( 'ajax-js', ['ajax-test'],
	function() {


//		return gulp.src( strTwistSource + 'ajax/js/*.js' )
//				.pipe( babel() )
//				.pipe( gulp.dest( strTwistDestination + 'ajax/js' ) );

		return gulp.src( strTwistSource + 'ajax/js/twistajax.js' )
				//.pipe( concat( 'twistajax.min.js' ) )
				.pipe( babel( {
					presets: ['es2015']
				} ) )
				.pipe( uglify( { preserveComments: 'license' } ) )
				.pipe( gulp.dest( strTwistDestination + 'ajax/js' ) );
	}
);

gulp.task( 'ajax-css',
	function() {
		return gulp.src( strTwistSource + 'ajax/scss/twistajax.scss' )
				.pipe( sourcemaps.init() )
				.pipe( sass( { errLogToConsole: true, outputStyle: 'compressed' } ) )
				.pipe( rename( 'twistajax.min.css' ) )
				.pipe( sourcemaps.write( './' ) )
				.pipe( gulp.dest( strTwistDestination + 'ajax/css' ) );
	}
);

gulp.task( 'ajax-test',
	function() {
		return gulp.src( strTwistSource + 'ajax/js/twistajax.js' )
				.pipe( jshint( {
					esversion: 6
				} ) )
				.pipe( jshint.reporter( 'default' ) );
	}
);

gulp.task( 'cssreset',
	function() {
		return gulp.src( strTwistSource + 'cssreset/scss/twistcssreset.scss' )
				.pipe( sourcemaps.init() )
				.pipe( sass( { errLogToConsole: true, outputStyle: 'compressed' } ) )
				.pipe( rename( 'twistcssreset.min.css' ) )
				.pipe( sourcemaps.write( './' ) )
				.pipe( gulp.dest( strTwistDestination + 'cssreset/css' ) );
	}
);

gulp.task( 'debug-js',
	function() {
		return gulp.src( strTwistSource + 'debug/js/twistdebug.js' )
				.pipe( jshint() )
				.pipe( jshint.reporter( 'default' ) )
				.pipe( concat( 'twistdebug.min.js' ) )
				.pipe( uglify( { preserveComments: 'license' } ) )
				.pipe( gulp.dest( strTwistDestination + 'debug/js' ) );
	}
);

gulp.task( 'debug-css',
	function() {
		return gulp.src( strTwistSource + 'debug/scss/twistdebug.scss' )
				.pipe( sourcemaps.init() )
				.pipe( sass( { errLogToConsole: true, outputStyle: 'compressed' } ) )
				.pipe( rename( 'twistdebug.min.css' ) )
				.pipe( sourcemaps.write( './' ) )
				.pipe( gulp.dest( strTwistDestination + 'debug/css' ) );
	}
);

gulp.task( 'debug-test',
		function() {
			return gulp.src( strTwistSource + 'debug/js/twistdebug.js' )
					.pipe( jshint() )
					.pipe( jshint.reporter( 'default' ) );
		}
);

gulp.task( 'fileupload-js',
	function() {
		return gulp.src( strTwistSource + 'fileupload/js/twistfileupload.js' )
				.pipe( jshint() )
				.pipe( jshint.reporter( 'default' ) )
				.pipe( concat( 'twistfileupload.js' ) )
				.pipe( gulp.dest( strTwistDestination + 'fileupload/js' ) )
				.pipe( rename( 'twistfileupload.min.js' ) )
				.pipe( uglify( { preserveComments: 'license' } ) )
				.pipe( gulp.dest( strTwistDestination + 'fileupload/js' ) );
	}
);

gulp.task( 'fileupload-css',
	function() {
		return gulp.src( strTwistSource + 'fileupload/scss/twistfileupload.scss' )
				.pipe( sourcemaps.init() )
				.pipe( sass( { errLogToConsole: true, outputStyle: 'compressed' } ) )
				.pipe( rename( 'twistfileupload.min.css' ) )
				.pipe( sourcemaps.write( './' ) )
				.pipe( gulp.dest( strTwistDestination + 'fileupload/css' ) );
	}
);

gulp.task( 'fileupload-test',
	function() {
		return gulp.src( strTwistSource + 'fileupload/js/twistfileupload.js' )
				.pipe( jshint() )
				.pipe( jshint.reporter( 'default' ) );
	}
);

gulp.task( 'manager-js',
	function() {
		return gulp.src( strTwistSource + 'manager/js/twistmanager.js' )
				.pipe( jshint() )
				.pipe( jshint.reporter( 'default' ) )
				.pipe( concat( 'twistmanager.min.js' ) )
				.pipe( uglify( { preserveComments: 'license' } ) )
				.pipe( gulp.dest( strTwistDestination + 'manager/js' ) );
	}
);

gulp.task( 'manager-css',
	function() {
		return gulp.src( strTwistSource + 'manager/scss/twistmanager.scss' )
				.pipe( sourcemaps.init() )
				.pipe( sass( { errLogToConsole: true, outputStyle: 'compressed' } ) )
				.pipe( rename( 'twistmanager.min.css' ) )
				.pipe( sourcemaps.write( './' ) )
				.pipe( gulp.dest( strTwistDestination + 'manager/css' ) );
	}
);

gulp.task( 'manager-test',
	function() {
		return gulp.src( strTwistSource + 'manager/js/twistmanager.js' )
				.pipe( jshint() )
				.pipe( jshint.reporter( 'default' ) );
	}
);

gulp.task( 'setup-js',
	function() {
		return gulp.src( strTwistSource + 'setup/js/twistsetup.js' )
				.pipe( jshint() )
				.pipe( jshint.reporter( 'default' ) )
				.pipe( concat( 'twistsetup.min.js' ) )
				.pipe( uglify( { preserveComments: 'license' } ) )
				.pipe( gulp.dest( strTwistDestination + 'setup/js' ) );
	}
);

gulp.task( 'setup-css',
	function() {
		return gulp.src( strTwistSource + 'setup/scss/twistsetup.scss' )
				.pipe( sourcemaps.init() )
				.pipe( sass( { errLogToConsole: true, outputStyle: 'compressed' } ) )
				.pipe( rename( 'twistsetup.min.css' ) )
				.pipe( sourcemaps.write( './' ) )
				.pipe( gulp.dest( strTwistDestination + 'setup/css' ) );
	}
);

gulp.task( 'setup-test',
	function() {
		return gulp.src( strTwistSource + 'setup/js/twistsetup.js' )
				.pipe( jshint() )
				.pipe( jshint.reporter( 'default' ) );
	}
);

gulp.task( 'docs',
	function() {
		return gulp.src( strTwistSource + '**/*.js' )
				.pipe( jsdoc( { opts: { destination: './docs/resources/' } } ) );
	}
);

gulp.task( 'watch-docs',
	function() {
		return gulp.watch( strTwistSource + '**/*.js', ['docs'] );
	}
);

gulp.task( 'ajax', ['ajax-js', 'ajax-css'] );
gulp.task( 'debug', ['debug-js', 'debug-css'] );
gulp.task( 'fileupload', ['fileupload-js', 'fileupload-css'] );
gulp.task( 'manager', ['manager-js', 'manager-css'] );
gulp.task( 'setup', ['setup-js', 'setup-css'] );

gulp.task( 'test', ['ajax-test', 'debug-test', 'fileupload-test', 'manager-test', 'setup-test'] );
gulp.task( 'default', ['ajax', 'cssreset', 'debug', 'fileupload', 'manager', 'setup'] );
