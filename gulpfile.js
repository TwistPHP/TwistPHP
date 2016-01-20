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
		return gulp.src( strTwistSource + 'ajax/js/twist-ajax.js' )
				.pipe( using() )
				.pipe( jshint() )
				.pipe( jshint.reporter( 'default' ) )
				.pipe( concat( 'twist-ajax.min.js' ) )
				.pipe( uglify() )
				.pipe( gulp.dest( strTwistDestination + 'ajax/js' ) );
	}
);

gulp.task( 'ajax-css',
	function() {
		return gulp.src( strTwistSource + 'ajax/scss/twist-ajax.scss' )
				.pipe( using() )
				.pipe( sourcemaps.init() )
				.pipe( sass( { errLogToConsole: true, outputStyle: 'compressed' } ) )
				.pipe( rename( 'twist-ajax.min.css' ) )
				.pipe( sourcemaps.write( './' ) )
				.pipe( gulp.dest( strTwistDestination + 'ajax/css' ) );
	}
);

gulp.task( 'debug-js',
	function() {
		return gulp.src( strTwistSource + 'debug/js/twist-debug.js' )
				.pipe( using() )
				.pipe( jshint() )
				.pipe( jshint.reporter( 'default' ) )
				.pipe( concat( 'twist-debug.min.js' ) )
				.pipe( uglify() )
				.pipe( gulp.dest( strTwistDestination + 'debug/js' ) );
	}
);

gulp.task( 'debug-css',
	function() {
		return gulp.src( strTwistSource + 'debug/scss/twist-debug.scss' )
				.pipe( using() )
				.pipe( sourcemaps.init() )
				.pipe( sass( { errLogToConsole: true, outputStyle: 'compressed' } ) )
				.pipe( rename( 'twist-debug.min.css' ) )
				.pipe( sourcemaps.write( './' ) )
				.pipe( gulp.dest( strTwistDestination + 'debug/css' ) );
	}
);

gulp.task( 'error-monitor-js',
	function() {
		return gulp.src( strTwistSource + 'error-monitor/js/twist-errormonitor.js' )
				.pipe( using() )
				.pipe( jshint() )
				.pipe( jshint.reporter( 'default' ) )
				.pipe( concat( 'twist-errormonitor.min.js' ) )
				.pipe( uglify() )
				.pipe( gulp.dest( strTwistDestination + 'error-monitor/js' ) );
	}
);

gulp.task( 'error-monitor-css',
	function() {
		return gulp.src( strTwistSource + 'error-monitor/scss/twist-errormonitor.scss' )
				.pipe( using() )
				.pipe( sourcemaps.init() )
				.pipe( sass( { errLogToConsole: true, outputStyle: 'compressed' } ) )
				.pipe( rename( 'twist-errormonitor.min.css' ) )
				.pipe( sourcemaps.write( './' ) )
				.pipe( gulp.dest( strTwistDestination + 'error-monitor/css' ) );
	}
);

gulp.task( 'file-upload-js',
	function() {
		return gulp.src( strTwistSource + 'file-upload/js/twist-fileupload.js' )
				.pipe( using() )
				.pipe( jshint() )
				.pipe( jshint.reporter( 'default' ) )
				.pipe( concat( 'twist-fileupload.min.js' ) )
				.pipe( uglify() )
				.pipe( gulp.dest( strTwistDestination + 'file-upload/js' ) );
	}
);

gulp.task( 'file-upload-css',
	function() {
		return gulp.src( strTwistSource + 'file-upload/scss/twist-fileupload.scss' )
				.pipe( using() )
				.pipe( sourcemaps.init() )
				.pipe( sass( { errLogToConsole: true, outputStyle: 'compressed' } ) )
				.pipe( rename( 'twist-fileupload.min.css' ) )
				.pipe( sourcemaps.write( './' ) )
				.pipe( gulp.dest( strTwistDestination + 'file-upload/css' ) );
	}
);

gulp.task( 'manager-js',
	function() {
		return gulp.src( strTwistSource + 'manager/js/twist-manager.js' )
				.pipe( using() )
				.pipe( jshint() )
				.pipe( jshint.reporter( 'default' ) )
				.pipe( concat( 'twist-manager.min.js' ) )
				.pipe( uglify() )
				.pipe( gulp.dest( strTwistDestination + 'manager/js' ) );
	}
);

gulp.task( 'manager-css',
	function() {
		return gulp.src( strTwistSource + 'manager/scss/twist-manager.scss' )
				.pipe( using() )
				.pipe( sourcemaps.init() )
				.pipe( sass( { errLogToConsole: true, outputStyle: 'compressed' } ) )
				.pipe( rename( 'twist-manager.min.css' ) )
				.pipe( sourcemaps.write( './' ) )
				.pipe( gulp.dest( strTwistDestination + 'manager/css' ) );
	}
);

gulp.task( 'setup-js',
	function() {
		return gulp.src( strTwistSource + 'setup/js/twist-setup.js' )
				.pipe( using() )
				.pipe( jshint() )
				.pipe( jshint.reporter( 'default' ) )
				.pipe( concat( 'twist-setup.min.js' ) )
				.pipe( uglify() )
				.pipe( gulp.dest( strTwistDestination + 'setup/js' ) );
	}
);

gulp.task( 'setup-css',
	function() {
		return gulp.src( strTwistSource + 'setup/scss/twist-setup.scss' )
				.pipe( using() )
				.pipe( sourcemaps.init() )
				.pipe( sass( { errLogToConsole: true, outputStyle: 'compressed' } ) )
				.pipe( rename( 'twist-setup.min.css' ) )
				.pipe( sourcemaps.write( './' ) )
				.pipe( gulp.dest( strTwistDestination + 'setup/css' ) );
	}
);

gulp.task( 'ajax', ['ajax-js', 'ajax-css'] );
gulp.task( 'debug', ['debug-js', 'debug-css'] );
gulp.task( 'error-monitor', ['error-monitor-js', 'error-monitor-css'] );
gulp.task( 'file-upload', ['file-upload-js', 'file-upload-css'] );
gulp.task( 'manager', ['manager-js', 'manager-css'] );
gulp.task( 'setup', ['setup-js', 'setup-css'] );

gulp.task( 'default', ['ajax', 'debug', 'error-monitor', 'file-upload', 'manager', 'setup'] );
