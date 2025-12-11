const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const TerserPlugin = require('terser-webpack-plugin');

module.exports = {
    // 1. Keep defaults first
    ...defaultConfig,
    
    mode: 'production',
    devtool: false,
    
    entry: {
        web3: path.resolve(__dirname, 'src/static/js/sol-web3/index.js'),
    },
    
    output: {
        ...defaultConfig.output,
        path: path.resolve(__dirname, 'src/static/js/sol-web3/dist'),
        filename: 'index.js'
    },
    
    // 2. KEEP THIS: Essential for Solana Web3 to work in the browser
    resolve: {
        ...defaultConfig.resolve,
        extensions: ['.js', '.json', '.jsx', '.ts', '.tsx'], // Expanded to cover WP defaults
        fallback: {
            "crypto": require.resolve("crypto-browserify"),
            "vm": require.resolve("vm-browserify"),
            "stream": require.resolve("stream-browserify"),
            ...defaultConfig.resolve.fallback, // Keep any defaults WP might add
        }
    },

    // 3. REMOVED: "module" block
    // @wordpress/scripts ALREADY handles babel-loader for .js files.
    // Manually adding it again often breaks the build with "Duplicate declaration" errors.
    
    optimization: {
        ...defaultConfig.optimization,
        minimize: true,
        minimizer: [
            // 4. CRITICAL: Keep default minimizers (like CSS)
            '...', 
            
            new TerserPlugin({
                terserOptions: {
                    compress: {
                        drop_console: true,
                    },
                },
            }),
        ],
    },
};