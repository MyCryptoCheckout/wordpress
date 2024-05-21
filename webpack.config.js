const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const TerserPlugin = require('terser-webpack-plugin');

module.exports = {
    mode: 'production', // Add this line
    ...defaultConfig,
    devtool: false,
    entry: {
        // Change the entry point to your specific file
        index: path.resolve(__dirname, 'src/ecommerce/woocommerce/react/index.js'),
    },
    output: {
        ...defaultConfig.output,
        // Change the output directory and filename
        path: path.resolve(__dirname, 'src/ecommerce/woocommerce/js'),
        filename: 'index.js'
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