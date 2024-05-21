const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const TerserPlugin = require('terser-webpack-plugin');

module.exports = {
    mode: 'production', // Add this line
    ...defaultConfig,
    devtool: false,
    entry: {
        web3: path.resolve(__dirname, 'src/static/js/sol-web3/index.js'),
    },
    output: {
        ...defaultConfig.output,
        path: path.resolve(__dirname, 'src/static/js/sol-web3/dist'),
        filename: 'index.js'
    },
    resolve: {
        extensions: ['.js'],
        ...defaultConfig.resolve,
        fallback: {
            "crypto": require.resolve("crypto-browserify"),
            "vm": require.resolve("vm-browserify"),
            "stream": require.resolve("stream-browserify"),
        }
    },
    module: {
        rules: [
            ...defaultConfig.module?.rules || [],
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: {
                    loader: 'babel-loader',
                    options: {
                        presets: ['@babel/preset-env'],
                        plugins: ['@babel/plugin-transform-runtime']
                    }
                }
            }
        ]
    },
    optimization: {
        minimize: true, // Ensure this is true if not already set by default in production mode
        minimizer: [
            new TerserPlugin({
                terserOptions: {
                    // You can specify additional options here according to your needs
                    compress: {
                        drop_console: true, // Removes console logs for production
                    },
                },
            }),
        ],
    },
};