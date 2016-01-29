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

gulp.task( 'css-reset',
	function() {
		return gulp.src( strTwistSource + 'css-reset/scss/twist-cssreset.scss' )
				.pipe( using() )
				.pipe( sourcemaps.init() )
				.pipe( sass( { errLogToConsole: true, outputStyle: 'compressed' } ) )
				.pipe( rename( 'twist-cssreset.min.css' ) )
				.pipe( sourcemaps.write( './' ) )
				.pipe( gulp.dest( strTwistDestination + 'css-reset/css' ) );
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

gulp.task( 'file-upload-js',
	function() {
		return gulp.src( strTwistSource + 'file-upload/js/twist-fileupload.js' )
				.pipe( using() )
				.pipe( jshint() )
				.pipe( jshint.reporter( 'default' ) )
				.pipe( concat( 'twist-fileupload.js' ) )
				.pipe( gulp.dest( strTwistDestination + 'file-upload/js' ) )
				.pipe( rename( 'twist-fileupload.min.js' ) )
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
gulp.task( 'file-upload', ['file-upload-js', 'file-upload-css'] );
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

gulp.task( 'watch-css-reset',
	function() {
		return gulp.watch( strTwistSource + 'css-reset/**/*', ['css-reset'] );
	}
);

gulp.task( 'watch-file-upload',
	function() {
		return gulp.watch( strTwistSource + 'file-upload/**/*', ['file-upload'] );
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

gulp.task( 'watch', ['default', 'watch-ajax', 'watch-css-reset', 'watch-debug', 'watch-file-upload', 'watch-manager', 'watch-setup'] );

gulp.task( 'default', ['ajax', 'css-reset', 'debug', 'file-upload', 'manager', 'setup'] );