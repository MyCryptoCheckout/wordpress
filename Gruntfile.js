module.exports = function(grunt) {

	// Configuration
	grunt.initConfig({
		sass: {
    		dist: {
     			options: {
        			style: 'nested',
        			precision: 5,
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
		watch: {
  			scripts: {
				files: ['src/static/css/scss/*.scss'],
				tasks: ['sass', 'postcss'],
  			},
		},
	});

	// Load plugins
	grunt.loadNpmTasks('grunt-contrib-sass');
	grunt.loadNpmTasks('@lodder/grunt-postcss');
	grunt.loadNpmTasks('grunt-contrib-watch');

	// Register Tasks
	grunt.registerTask('compile-sass', ['sass']);
	grunt.registerTask('prefix-css', ['postcss']);
	
	// Run All Tasks
	grunt.registerTask('all', ['compile-sass', 'prefix-css']);

};