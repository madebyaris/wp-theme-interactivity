const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );

const blocks = [ 'navigation', 'search', 'counter', 'accordion' ];

const entry = {};
blocks.forEach( ( block ) => {
	entry[ `blocks/${ block }/view` ] = `./blocks/${ block }/view.ts`;
	entry[ `blocks/${ block }/index` ] = `./blocks/${ block }/index.ts`;
} );

const copyPatterns = [];
blocks.forEach( ( block ) => {
	[ 'block.json', 'render.php', 'style.css', 'editor.css' ].forEach(
		( file ) => {
			copyPatterns.push( {
				from: `blocks/${ block }/${ file }`,
				to: `blocks/${ block }/${ file }`,
				noErrorOnMissing: true,
			} );
		}
	);
} );

module.exports = {
	...defaultConfig,
	entry,
	// Build as ES modules so viewScriptModule bundles import @wordpress/interactivity
	// instead of referencing window.wp.interactivity.
	experiments: {
		...( defaultConfig.experiments || {} ),
		outputModule: true,
	},
	output: {
		...defaultConfig.output,
		path: path.resolve( __dirname, 'build' ),
		module: true,
		chunkFormat: 'module',
		environment: {
			...( defaultConfig.output?.environment || {} ),
			module: true,
		},
		library: {
			type: 'module',
		},
	},
	plugins: [
		...( defaultConfig.plugins || [] ).filter(
			( p ) => p.constructor.name !== 'DependencyExtractionWebpackPlugin'
		),
		new DependencyExtractionWebpackPlugin( {
			// WordPress core registers @wordpress/interactivity, not wp-interactivity.
			// Override so asset files use the correct script module ID.
			requestToHandle: ( request ) =>
				request === '@wordpress/interactivity'
					? '@wordpress/interactivity'
					: undefined,
		} ),
		new CopyWebpackPlugin( {
			patterns: copyPatterns,
		} ),
	],
	module: {
		...defaultConfig.module,
		rules: [
			...( defaultConfig.module?.rules || [] ),
			{
				test: /\.tsx?$/,
				exclude: /node_modules/,
				use: {
					loader: 'babel-loader',
					options: {
						presets: [ '@babel/preset-typescript' ],
					},
				},
			},
		],
	},
	resolve: {
		...( defaultConfig.resolve || {} ),
		extensions: [ '.tsx', '.ts', '.js', '.jsx' ],
	},
};
