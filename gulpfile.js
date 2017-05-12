var gulp = require( 'gulp' ),
		babel = require( 'gulp-babel' ),
		eslint = require( 'gulp-eslint' ),
		sass = require( 'gulp-sass' ),
		concat = require( 'gulp-concat' ),
		uglify = require( 'gulp-uglify' ),
		rename = require( 'gulp-rename' ),
		jsdoc = require( 'gulp-jsdoc3' ),
		sourcemaps = require( 'gulp-sourcemaps' ),
		strTwistSource = './src/Core/Resources/twist/',
		strTwistDestination = './dist/twist/Core/Resources/twist/';

gulp.task( 'ajax-js',
	function() {
		return gulp.src( strTwistSource + 'ajax/js/twistajax.js' )
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

gulp.task( 'fileupload-js',
	function() {
		return gulp.src( strTwistSource + 'fileupload/js/twistfileupload.js' )
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

gulp.task( 'manager-js',
	function() {
		return gulp.src( strTwistSource + 'manager/js/twistmanager.js' )
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

gulp.task( 'setup-js',
	function() {
		return gulp.src( strTwistSource + 'setup/js/twistsetup.js' )
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

gulp.task( 'docs',
	function() {
		return gulp.src( strTwistSource + '**/*.js' )
				.pipe( jsdoc( { opts: { destination: './docs/resources/' } } ) );
	}
);

gulp.task( 'ajax', ['ajax-js', 'ajax-css'] );
gulp.task( 'debug', ['debug-js', 'debug-css'] );
gulp.task( 'fileupload', ['fileupload-js', 'fileupload-css'] );
gulp.task( 'manager', ['manager-js', 'manager-css'] );
gulp.task( 'setup', ['setup-js', 'setup-css'] );

gulp.task( 'test',
	function() {
		return gulp.src( strTwistSource + '**/*.js' )
		.pipe(
			eslint(
				{
					parserOptions: {
						ecmaVersion: 6,
						sourceType: 'module',
						ecmaFeatures: {
							impliedStrict: false
						}
					},
					rules: {
						'eqeqeq': 2,
						'no-inner-declarations': 2,
						'no-irregular-whitespace': 1,
						//'valid-jsdoc': 1,
						'no-dupe-keys': 1,
						'valid-typeof': 2,
						'no-unreachable': 2,
						'no-alert': 2,
						'no-eval': 2,
						quotes: ['error', 'single']
					},
					//globals: ['jQuery', '$'],
					envs: ['browser']
				}
			)
		)
		.pipe( eslint.format() )
		.pipe( eslint.failAfterError() );
	}
);

gulp.task( 'default', ['test', 'ajax', 'cssreset', 'debug', 'fileupload', 'manager', 'setup'] );
