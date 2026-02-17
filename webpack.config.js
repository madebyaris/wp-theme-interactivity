const defaultConfig = require( '@wordpress/scripts/config/webpack.config' );
const path = require( 'path' );
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );

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
	output: {
		...defaultConfig.output,
		path: path.resolve( __dirname, 'build' ),
	},
	plugins: [
		...( defaultConfig.plugins || [] ),
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
