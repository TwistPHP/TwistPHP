var gulp = require( 'gulp' ),
		babel = require( 'gulp-babel' ),
		rollup = require( 'rollup' ),
		rollupUglify = require( 'rollup-plugin-uglify' ),
		rollupBabel = require( 'rollup-plugin-babel' ),
		rollupESLint = require( 'rollup-plugin-eslint' ),
		eslint = require( 'gulp-eslint' ),
		rollupCJS = require( 'rollup-plugin-commonjs' ),
		sass = require( 'gulp-sass' ),
		concat = require( 'gulp-concat' ),
		uglify = require( 'gulp-uglify' ),
		jsdoc = require( 'gulp-jsdoc3' ),
		sourcemaps = require( 'gulp-sourcemaps' ),
		esOptions = {
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
				'valid-jsdoc': 1,
				'no-dupe-keys': 1,
				'valid-typeof': 2,
				'no-unreachable': 2,
				'no-alert': 2,
				'no-eval': 2,
				//quotes: ['warn', 'single']
			},
			envs: ['browser']
		},
		strTwistSource = './src/Core/Resources/twist/',
		strTwistDestination = './dist/twist/Core/Resources/twist/';

gulp.task( 'ajax-js', () => {
	return rollup.rollup( {
		entry: strTwistSource + 'ajax/js/twistajax.js',
		plugins: [
			rollupCJS( {
				namedExports: {'./node_modules/form-serialize/index.js': ['serialize']}
			} ),
			rollupBabel( {
				presets: [['es2015', {modules: false}]],
				sourceMaps: true,
				babelrc: false
			} ),
			rollupESLint( esOptions ),
			rollupUglify()
		],
	} )
			.then( bundle => {
				return bundle.write( {
					format: 'umd',
					moduleName: 'twistajax',
					dest: strTwistDestination + 'ajax/js/twistajax.js',
					sourceMap: true
				} );
			} );
} );


gulp.task( 'ajax-css', () => {
	return gulp.src( strTwistSource + 'ajax/scss/twistajax.scss' )
			.pipe( sourcemaps.init() )
			.pipe( sass( {errLogToConsole: true, outputStyle: 'compressed'} ) )
			.pipe( sourcemaps.write( './' ) )
			.pipe( gulp.dest( strTwistDestination + 'ajax/css' ) );
} );

gulp.task( 'cssreset', () => {
	return gulp.src( strTwistSource + 'cssreset/scss/twistcssreset.scss' )
			.pipe( sourcemaps.init() )
			.pipe( sass( {errLogToConsole: true, outputStyle: 'compressed'} ) )
			.pipe( sourcemaps.write( './' ) )
			.pipe( gulp.dest( strTwistDestination + 'cssreset/css' ) );
} );

gulp.task( 'debug-js', () => {
	return gulp.src( strTwistSource + 'debug/js/twistdebug.js' )
			.pipe( uglify( {preserveComments: 'license'} ) )
			.pipe( gulp.dest( strTwistDestination + 'debug/js' ) );
} );

gulp.task( 'debug-css', () => {
	return gulp.src( strTwistSource + 'debug/scss/twistdebug.scss' )
			.pipe( sourcemaps.init() )
			.pipe( sass( {errLogToConsole: true, outputStyle: 'compressed'} ) )
			.pipe( sourcemaps.write( './' ) )
			.pipe( gulp.dest( strTwistDestination + 'debug/css' ) );
} );

gulp.task( 'fileupload-js', () => {
	return gulp.src( strTwistSource + 'fileupload/js/twistfileupload.js' )
			.pipe( uglify( {preserveComments: 'license'} ) )
			.pipe( gulp.dest( strTwistDestination + 'fileupload/js' ) );
} );

gulp.task( 'fileupload-css', () => {
	return gulp.src( strTwistSource + 'fileupload/scss/twistfileupload.scss' )
			.pipe( sourcemaps.init() )
			.pipe( sass( {errLogToConsole: true, outputStyle: 'compressed'} ) )
			.pipe( sourcemaps.write( './' ) )
			.pipe( gulp.dest( strTwistDestination + 'fileupload/css' ) );
} );

gulp.task( 'manager-js', () => {
	return gulp.src( strTwistSource + 'manager/js/twistmanager.js' )
			.pipe( uglify( {preserveComments: 'license'} ) )
			.pipe( gulp.dest( strTwistDestination + 'manager/js' ) );
} );

gulp.task( 'manager-css', () => {
	return gulp.src( strTwistSource + 'manager/scss/twistmanager.scss' )
			.pipe( sourcemaps.init() )
			.pipe( sass( {errLogToConsole: true, outputStyle: 'compressed'} ) )
			.pipe( sourcemaps.write( './' ) )
			.pipe( gulp.dest( strTwistDestination + 'manager/css' ) );
} );

gulp.task( 'setup-js', () => {
	return gulp.src( strTwistSource + 'setup/js/twistsetup.js' )
			.pipe( uglify( {preserveComments: 'license'} ) )
			.pipe( gulp.dest( strTwistDestination + 'setup/js' ) );
} );

gulp.task( 'setup-css', () => {
	return gulp.src( strTwistSource + 'setup/scss/twistsetup.scss' )
			.pipe( sourcemaps.init() )
			.pipe( sass( {errLogToConsole: true, outputStyle: 'compressed'} ) )
			.pipe( sourcemaps.write( './' ) )
			.pipe( gulp.dest( strTwistDestination + 'setup/css' ) );
} );

gulp.task( 'docs', () => {
	return gulp.src( strTwistSource + '**/*.js' )
			.pipe( jsdoc( {opts: {destination: './docs/resources/'}} ) );
} );

gulp.task( 'ajax', ['ajax-js', 'ajax-css'] );
gulp.task( 'debug', ['debug-js', 'debug-css'] );
gulp.task( 'fileupload', ['fileupload-js', 'fileupload-css'] );
gulp.task( 'manager', ['manager-js', 'manager-css'] );
gulp.task( 'setup', ['setup-js', 'setup-css'] );

gulp.task( 'test', () => {
	return gulp.src( strTwistSource + '**/*.js' )
			.pipe( eslint( esOptions ) )
			.pipe( eslint.format() )
			.pipe( eslint.failAfterError() );
} );

gulp.task( 'default', ['test', 'ajax', 'cssreset', 'debug', 'fileupload', 'manager', 'setup'] );
