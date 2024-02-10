<?php  // Moodle configuration file

unset($CFG);
global $CFG;
$CFG = new stdClass();

$CFG->dbtype    = 'mariadb';
$CFG->dblibrary = 'native';
$CFG->dbhost    = 'db';
$CFG->dbname    = 'moodle';
$CFG->dbuser    = 'moodle';
$CFG->dbpass    = 'secret';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array (
  'dbpersist' => 0,
  'dbport' => 3306,
  'dbsocket' => '',
  'dbcollation' => 'utf8mb4_bin',
);
$CFG->site_is_public = false;

$CFG->wwwroot   = 'http://localhost:8080';
$CFG->dataroot  = '/var/www/moodledata';
$CFG->admin     = 'admin';
$CFG->noreplyaddress = 'noreply@example.com';

// only for development environment 
$CFG->debug = (E_ALL | E_STRICT); // DEBUG_DEVELOPER
$CFG->debugdisplay = 1;
$CFG->debugstringids = 1; // Add strings=1 to url to get string ids.
$CFG->perfdebug = 15;
$CFG->debugpageinfo = 1;
$CFG->allowthemechangeonurl = 1;
$CFG->passwordpolicy = 0;
$CFG->cronclionly = 0;
$CFG->pathtophp = '/usr/local/bin/php';

$CFG->directorypermissions = 0777;

// PHPUnit
$CFG->phpunit_dataroot = '/var/www/phpu_moodledata';
$CFG->phpunit_prefix = 'phpu_';

// Behat
$CFG->behat_dataroot = '/var/www/behatdata';
$CFG->behat_wwwroot = 'http://127.0.0.1:8080';
$CFG->behat_prefix = 'bht_';
$CFG->behat_profiles = [
    'firefox' => [
        'browser' => 'firefox',
        'wd_host' => 'http://selenium:4444',
    ]
];
$CFG->behat_faildump_path = '/var/www/behatfaildumps';

require_once('/opt/moodle-browser-config/init.php');
require_once(__DIR__ . '/lib/setup.php');

