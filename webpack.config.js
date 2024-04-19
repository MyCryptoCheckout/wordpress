const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
    ...defaultConfig,
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
};