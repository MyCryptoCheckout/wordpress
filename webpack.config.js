const path = require('path');
// Import the default config
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const TerserPlugin = require('terser-webpack-plugin');

module.exports = {
    // 1. Keep the default settings (Spread them first)
    ...defaultConfig,

    // 2. Your custom settings
    mode: 'production', 
    devtool: false,

    entry: {
        index: path.resolve(__dirname, 'src/ecommerce/woocommerce/react/index.js'),
    },

    output: {
        ...defaultConfig.output,
        path: path.resolve(__dirname, 'src/ecommerce/woocommerce/js'),
        filename: 'index.js'
    },

    optimization: {
        // 3. Keep other optimization defaults (like runtime chunks if any)
        ...defaultConfig.optimization,

        minimize: true,
        minimizer: [
            // This string tells Webpack to "EXTEND" defaults, not replace them.
            // Without this, you lose the CSS minimizer!
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