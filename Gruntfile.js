module.exports = function (grunt) {
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),
        // Deploy configuration
        sshconfig: {
            'server': {
                host: process.env.SSH_HOST,
                username: process.env.SSH_USER,
                password: process.env.SSH_PASSWORD,
                port: process.env.SSH_PORT
            }
        },
        sshexec: {
            deploy: {
                command: [
                    'cd /var/www/riplive_admin',
                    'git pull origin master'
                ].join(' && '),
                options: {
                    config: 'server'
                }
            }
        },
    });

    grunt.loadNpmTasks('grunt-ssh');

    grunt.registerTask('deploy', [
        'sshexec:deploy'
    ]);
};
