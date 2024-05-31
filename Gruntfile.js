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
								return dest + '10.' + matchedSrcPath;
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
		concat: {
			basic_and_extras: {
				files: {
					'src/static/js/mycryptocheckout.js':
					[
						'src/static/js/js.d/10.bignumber.js',
						'src/static/js/js.d/10.mcc_make_clipboard.js',
						'src/static/js/js.d/10.new_currency.js',
						'src/static/js/js.d/10.plainview_auto_tabs.js',
						'src/static/js/js.d/10.sort_wallets.js',
						'src/static/js/js.d/20.header.js',
						'src/static/js/js.d/40.clipboard.js',
						'src/static/js/js.d/40.qrcode.js',
						'src/static/js/js.d/50.checkout.js',
						'src/static/js/js.d/50.donations.js',
						'src/static/js/js.d/98.init.js',
						'src/static/js/js.d/99.footer.js',
					],
				},
			},
		},
		uglify: {
			options: {
				mangle: false,
			},
			my_target: {
				files: {
					'src/static/js/mycryptocheckout.min.js': ['src/static/js/mycryptocheckout.js'],
				}
			}
		},
	});

	// Load plugins
	grunt.loadNpmTasks('grunt-dart-sass');
	grunt.loadNpmTasks('@lodder/grunt-postcss');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	
	// Run All Tasks
	grunt.registerTask('all', ['dart-sass', 'postcss', 'copy', 'concat', 'uglify']);

};