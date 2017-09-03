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
		jsdoc = require( 'gulp-jsdoc3' ),
		sourcemaps = require( 'gulp-sourcemaps' );


var strTwistSource = './src/',
		strTwistDestination = './dist/twist/Core/Resources/twist/',
		esOptions = {
			parserOptions: {
				ecmaVersion: 6,
				sourceType: 'module',
				ecmaFeatures: {
					impliedStrict: false
				}
			},
			rules: {
				'eqeqeq': 1,
				'no-inner-declarations': 2,
				'no-irregular-whitespace': 1,
				'valid-jsdoc': 1,
				'no-dupe-keys': 1,
				'valid-typeof': 2,
				'no-unreachable': 1,
				'no-alert': 2,
				'no-eval': 2,
				//quotes: ['warn', 'single']
			},
			envs: ['browser']
		},
		rollupConfig = entry => {
			return {
				entry: entry,
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
			};
		},
		rollupExport = ( bundle, dest, umd ) => {
			return bundle.write( {
				format: 'umd',
				moduleName: umd,
				dest: dest,
				sourceMap: true
			} );
		};

gulp.task( 'ajax-js', () => {
	return rollup.rollup( rollupConfig( strTwistSource + 'ajax/js/twistajax.js' ) )
			.then( bundle => rollupExport( bundle, strTwistDestination + 'ajax/js/twistajax.js', 'twistajax' ) );
} );

gulp.task( 'debug-js', () => {
	return rollup.rollup( rollupConfig( strTwistSource + 'debug/js/twistdebug.js' ) )
			.then( bundle => rollupExport( bundle, strTwistDestination + 'debug/js/twistdebug.js', 'twistdebug' ) );
} );

gulp.task( 'debug-catch-js', () => {
	return rollup.rollup( {
		entry: strTwistSource + 'debug/js/twistdebugcatcher.js',
		plugins: [
			rollupBabel( {
				presets: [['es2015', {modules: false}]],
				sourceMaps: false,
				babelrc: false
			} ),
			rollupESLint( esOptions ),
			rollupUglify()
		]
	} )
			.then( bundle => {
				return bundle.write( {
					format: 'iife',
					moduleName: 'twistdebugcatcher',
					dest: strTwistDestination + 'debug/js/twistdebugcatcher.js',
					sourceMap: false
				} );
			} );
} );

gulp.task( 'debug-css', () => {
	return gulp.src( strTwistSource + 'debug/scss/twistdebug.scss' )
			.pipe( sourcemaps.init() )
			.pipe( sass( {errLogToConsole: true, outputStyle: 'compressed'} ) )
			.pipe( sourcemaps.write( './' ) )
			.pipe( gulp.dest( strTwistDestination + 'debug/css' ) );
} );

gulp.task( 'fileupload-js', () => {
	return rollup.rollup( rollupConfig( strTwistSource + 'fileupload/js/twistfileupload.js' ) )
			.then( bundle => rollupExport( bundle, strTwistDestination + 'fileupload/js/twistfileupload.js', 'twistfileupload' ) );
} );

gulp.task( 'manager-css', () => {
	return gulp.src( strTwistSource + 'manager/scss/twistmanager.scss' )
			.pipe( sourcemaps.init() )
			.pipe( sass( {errLogToConsole: true, outputStyle: 'compressed'} ) )
			.pipe( sourcemaps.write( './' ) )
			.pipe( gulp.dest( strTwistDestination + 'manager/css' ) );
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

gulp.task( 'ajax', ['ajax-js'] );
gulp.task( 'debug', ['debug-js', 'debug-css', 'debug-catch-js'] );
gulp.task( 'fileupload', ['fileupload-js'] );
gulp.task( 'manager', ['manager-css'] );
gulp.task( 'setup', ['setup-css'] );
gulp.task( 'css', ['debug-css', 'manager-css', 'setup-css'] );

gulp.task( 'test', ['default'], () => {
	return gulp.src( strTwistSource + '**/*.js' )
			.pipe( eslint( esOptions ) )
			.pipe( eslint.format() )
			.pipe( eslint.failAfterError() );
} );

gulp.task( 'watch', () => {
	gulp.watch( strTwistSource + 'ajax/js/twistajax.js', ['ajax-js'] );
	gulp.watch( strTwistSource + 'debug/js/twistdebug.js', ['debug-js'] );
	gulp.watch( strTwistSource + 'debug/js/twistdebugcatcher.js', ['debug-catch-js'] );
	gulp.watch( strTwistSource + 'fileupload/js/twistfileupload.js', ['fileupload-js'] );
	gulp.watch( strTwistSource + 'debug/scss/**/*.scss', ['debug-css'] );
	gulp.watch( strTwistSource + 'manager/scss/**/*.scss', ['manager-css'] );
	gulp.watch( strTwistSource + 'setup/scss/**/*.scss', ['setup-css'] );
	gulp.watch( strTwistSource + '_common.scss', ['css'] );
} );

gulp.task( 'dev', ['default', 'watch'] );

gulp.task( 'default', ['ajax', 'debug', 'fileupload', 'manager', 'setup'] );
