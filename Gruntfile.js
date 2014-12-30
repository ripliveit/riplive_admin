module.exports = function(grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        rsync: {
            options: {
                args: ['--verbose'],
                exclude: [
                    '.git*',
                    '.htaccess',
                    '.htpasswd',
                    'node_modules',
                    'nbproject',
                    'wp-content/uploads/',
                    'wp-config.php',
                    '.DS_store'
                ],
                recursive: true
            },
            dist: {
                options: {
                    src: "./",
                    dest: "/var/www/test",
                    host: "rip@static.riplive.it",
                    port : 5430
                }
            }
        }
    });

    grunt.loadNpmTasks('grunt-rsync');

    grunt.registerTask('deploy', [
        'rsync:dist'
    ]);
};
