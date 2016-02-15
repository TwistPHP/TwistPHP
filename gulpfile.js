var gulp = require( 'gulp' ),
		jshint = require( 'gulp-jshint' ),
		sass = require( 'gulp-sass' ),
		concat = require( 'gulp-concat' ),
		uglify = require( 'gulp-uglify' ),
		rename = require( 'gulp-rename' ),
		using = require( 'gulp-using' ),
		sourcemaps = require( 'gulp-sourcemaps' ),
		strTwistSource = './src/Core/Resources/twist/',
		strTwistDestination = './dist/twist/Core/Resources/twist/';

gulp.task( 'ajax-js',
	function() {
		return gulp.src( strTwistSource + 'ajax/js/twistajax.js' )
				.pipe( using() )
				.pipe( jshint() )
				.pipe( jshint.reporter( 'default' ) )
				.pipe( concat( 'twistajax.min.js' ) )
				.pipe( uglify( { preserveComments: 'license' } ) )
				.pipe( gulp.dest( strTwistDestination + 'ajax/js' ) );
	}
);

gulp.task( 'ajax-css',
	function() {
		return gulp.src( strTwistSource + 'ajax/scss/twistajax.scss' )
				.pipe( using() )
				.pipe( sourcemaps.init() )
				.pipe( sass( { errLogToConsole: true, outputStyle: 'compressed' } ) )
				.pipe( rename( 'twistajax.min.css' ) )
				.pipe( sourcemaps.write( './' ) )
				.pipe( gulp.dest( strTwistDestination + 'ajax/css' ) );
	}
);

gulp.task( 'cssreset',
	function() {
		return gulp.src( strTwistSource + 'cssreset/scss/twistcssreset.scss' )
				.pipe( using() )
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
				.pipe( using() )
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
				.pipe( using() )
				.pipe( sourcemaps.init() )
				.pipe( sass( { errLogToConsole: true, outputStyle: 'compressed' } ) )
				.pipe( rename( 'twistdebug.min.css' ) )
				.pipe( sourcemaps.write( './' ) )
				.pipe( gulp.dest( strTwistDestination + 'debug/css' ) );
	}
);

gulp.task( 'fileupload-js',
	function() {
		return gulp.src( strTwistSource + 'fileupload/js/twistfileupload.js' )
				.pipe( using() )
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
				.pipe( using() )
				.pipe( sourcemaps.init() )
				.pipe( sass( { errLogToConsole: true, outputStyle: 'compressed' } ) )
				.pipe( rename( 'twistfileupload.min.css' ) )
				.pipe( sourcemaps.write( './' ) )
				.pipe( gulp.dest( strTwistDestination + 'fileupload/css' ) );
	}
);

gulp.task( 'manager-js',
	function() {
		return gulp.src( strTwistSource + 'manager/js/twistmanager.js' )
				.pipe( using() )
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
				.pipe( using() )
				.pipe( sourcemaps.init() )
				.pipe( sass( { errLogToConsole: true, outputStyle: 'compressed' } ) )
				.pipe( rename( 'twistmanager.min.css' ) )
				.pipe( sourcemaps.write( './' ) )
				.pipe( gulp.dest( strTwistDestination + 'manager/css' ) );
	}
);

gulp.task( 'setup-js',
	function() {
		return gulp.src( strTwistSource + 'setup/js/twistsetup.js' )
				.pipe( using() )
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
				.pipe( using() )
				.pipe( sourcemaps.init() )
				.pipe( sass( { errLogToConsole: true, outputStyle: 'compressed' } ) )
				.pipe( rename( 'twistsetup.min.css' ) )
				.pipe( sourcemaps.write( './' ) )
				.pipe( gulp.dest( strTwistDestination + 'setup/css' ) );
	}
);

gulp.task( 'ajax', ['ajax-js', 'ajax-css'] );
gulp.task( 'debug', ['debug-js', 'debug-css'] );
gulp.task( 'fileupload', ['fileupload-js', 'fileupload-css'] );
gulp.task( 'manager', ['manager-js', 'manager-css'] );
gulp.task( 'setup', ['setup-js', 'setup-css'] );

gulp.task( 'watch-ajax',
	function() {
		return gulp.watch( strTwistSource + 'ajax/**/*', ['ajax'] );
	}
);

gulp.task( 'watch-debug',
	function() {
		return gulp.watch( strTwistSource + 'debug/**/*', ['debug'] );
	}
);

gulp.task( 'watch-cssreset',
	function() {
		return gulp.watch( strTwistSource + 'cssreset/**/*', ['cssreset'] );
	}
);

gulp.task( 'watch-fileupload',
	function() {
		return gulp.watch( strTwistSource + 'fileupload/**/*', ['fileupload'] );
	}
);

gulp.task( 'watch-manager',
	function() {
		return gulp.watch( strTwistSource + 'manager/**/*', ['manager'] );
	}
);

gulp.task( 'watch-setup',
	function() {
		return gulp.watch( strTwistSource + 'setup/**/*', ['setup'] );
	}
);

gulp.task( 'watch', ['default', 'watch-ajax', 'watch-cssreset', 'watch-debug', 'watch-fileupload', 'watch-manager', 'watch-setup'] );

gulp.task( 'default', ['ajax', 'cssreset', 'debug', 'fileupload', 'manager', 'setup'] );