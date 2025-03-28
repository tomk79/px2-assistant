const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix
	.webpackConfig({
		module: {
			rules:[
				{
					test: /\.txt$/i,
					use: ['raw-loader'],
				},
				{
					test: /\.(jsx|tsx)$/,
					exclude: /node_modules/,
					use: {
						loader: 'babel-loader',
						options: {
							presets: [
								'@babel/preset-env',
								'@babel/preset-react',
								'@babel/preset-typescript', // Ensure TypeScript support for .tsx
							]
						}
					}
				}
			]
		},
		resolve: {
			extensions: ['.js', '.jsx', '.ts', '.tsx'], // Add support for JSX/TSX extensions
			fallback: {
				"fs": false,
				"path": false,
				"crypto": false,
				"stream": false,
			}
		}
	})


	// --------------------------------------
	// assistantCceFront.js
	.js('cce/src/assistantCceFront.js', 'cce/front/')
	.sass('cce/src/assistantCceFront.scss', 'cce/front/')
;
