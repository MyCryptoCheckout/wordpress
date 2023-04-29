module.exports = function(grunt) {

	// Configuration
	grunt.initConfig({
		'dart-sass': {
			target: {
				options: {
					sourceMap: false,
				},
				files: {
					'src/static/css/mycryptocheckout.css': 'src/static/css/scss/mycryptocheckout.scss',
				}
			}
		},
    	postcss: {
  			options: {
    			processors: [
      				require('autoprefixer')({
						overrideBrowserslist: ['> 0.5%, last 2 versions, Firefox ESR, not dead']
					})
    			]
  			},
  			dist: {
    			src: 'src/static/css/mycryptocheckout.css'
  			}
		},
		copy: {
			main: {
				files: [
					// copies web3.js file
					{ 
						expand: true,
						flatten: true,
						src: ['node_modules/web3/dist/web3.min.js'], 
						dest: 'src/static/js/',
						filter: 'isFile'
					},
					// copies bignumber.js file
					{ 
						expand: true,
						flatten: true,
						src: ['node_modules/bignumber.js/bignumber.js'], 
						dest: 'src/static/js/js.d/',
						filter: 'isFile',
						// Rename the file to include '40.' prefix 
						rename: function (dest, matchedSrcPath) {
							if (matchedSrcPath.substring(0, 1) !== '4') {
								return dest + '40.' + matchedSrcPath;
							}
						}
					},
					// copies clipboard.js file
					{ 
						expand: true,
						flatten: true,
						src: ['node_modules/clipboard/dist/clipboard.js'], 
						dest: 'src/static/js/js.d/',
						filter: 'isFile',
						// Rename the file to include '40.' prefix 
						rename: function (dest, matchedSrcPath) {
							if (matchedSrcPath.substring(0, 1) !== '4') {
								return dest + '40.' + matchedSrcPath;
							}
						}
					},
					// copies qrcode.js file
					{ 
						expand: true,
						flatten: true,
						src: ['node_modules/qrcode/build/qrcode.js'], 
						dest: 'src/static/js/js.d/',
						filter: 'isFile',
						// Rename the file to include '40.' prefix 
						rename: function (dest, matchedSrcPath) {
							if (matchedSrcPath.substring(0, 1) !== '4') {
								return dest + '40.' + matchedSrcPath;
							}
						}
					},
				],
			},
		},
	});

	// Load plugins
	grunt.loadNpmTasks('grunt-dart-sass');
	grunt.loadNpmTasks('@lodder/grunt-postcss');
	grunt.loadNpmTasks('grunt-contrib-copy');
	
	// Run All Tasks
	grunt.registerTask('all', ['dart-sass', 'postcss', 'copy']);

};