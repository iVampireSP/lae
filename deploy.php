<?php
namespace Deployer;

require 'recipe/laravel.php';

// Config

try {
    set('repository', 'git@github.com:EdgeStanding/lae.git');
} catch (Exception\Exception $e) {
    exit($e->getMessage());
}

add('shared_files', []);
add('shared_dirs', []);
add('writable_dirs', []);

// Hosts

host('')
    ->set('remote_user', 'deployer')
    ->set('deploy_path', '~/lae');

// Hooks

after('deploy:failed', 'deploy:unlock');
