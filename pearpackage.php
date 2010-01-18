#!/usr/bin/env php
<?php

/**
 * PEAR package.xml generator.
 *
 */
require_once 'PEAR/PackageFileManager2.php';
require_once dirname(__FILE__) . '/../lib/Sabre/Cache/Version.php';
PEAR::setErrorHandling(PEAR_ERROR_DIE);

$apiVersion     = Sabre_Cache_Version::VERSION;
$apiState       = Sabre_Cache_Version::STABILITY;

$releaseVersion = Sabre_Cache_Version::VERSION; 
$releaseState   = Sabre_Cache_Version::STABILITY; 

$description = <<<TEXT
   SabreCache is a lightweight caching layer for PHP.
   It offers abstractions for APC, Memcached and the Filesystem.

   Using an abstraction layer for caching allows you to provide a portable caching api, so you can
   easily swap out different engines, without rewriting code.
TEXT;

$package = new PEAR_PackageFileManager2();

$package->setOptions(
    array(
        'filelistgenerator'          => 'svn',
        'simpleoutput'               => true,
        'baseinstalldir'             => '/',
        'packagedirectory'           => './',
        'dir_roles'                  => array(
            'lib/Sabre'              => 'php',
        ),
        'exceptions'                 => array(
            'ChangeLog'              => 'doc',
            'LICENCE'                => 'doc',
        ),
        'ignore'                     => array(
        )
    )
);

$package->setPackage('Sabre_Cache');
$package->setSummary('SabreCache is an lightweight caching layer.');

$package->setDescription($description);

// We're generating 2 different packages. One for pearfarm, and one for plain download
if (isset($argv) && in_array('pearfarm',$argv)) {
    $package->setChannel('evert.pearfarm.org');
} else {
    $package->setUri('http://sabredav.googlecode.com/files/Sabre_Cache-' . $releaseVersion);
}

$package->setPackageType('php');
$package->setLicense('BSD', 'http://code.google.com/p/sabredav/wiki/License');

$package->setNotes('See ChangeLog for details.');
$package->setReleaseVersion($releaseVersion);
$package->setReleaseStability($releaseState);
$package->setAPIVersion($apiVersion);
$package->setAPIStability($apiState);

$package->addMaintainer(
    'lead',
    'evert',
    'Evert Pot',
    'http://www.rooftopsolutions.nl/'
);

$package->setPhpDep('5.2.1');
$package->setPearinstallerDep('1.4.0');
$package->generateContents();

$package->addRelease();

/*
 * Files get installed without the lib/ directory so they fit in PEAR's
 * file naming scheme.
 */
function getDirectory($path)
{
    $files = array();

    $ignore = array('.', '..', '.hg','.DS_Store');

    $d = opendir($path);

    while (false !== ($file = readdir($d))) {
        $newPath = $path . '/' . $file;
        if (!in_array($file, $ignore)) {
            if (is_dir($newPath)) {
                $files = array_merge($files, getDirectory($newPath));
            } else {
                $files[] = $newPath;
            }
        }
    }

    closedir($d);
    return $files;
}
$files = getDirectory('lib');
foreach ($files as $file) {
    // strip off 'lib/' dir
    $package->addInstallAs($file, substr($file, 4));
}

if (isset($_GET['make'])
    || (isset($_SERVER['argv']) && @in_array('make',$_SERVER['argv']))
) {
    $package->writePackageFile();

} else {
    $package->debugPackageFile();
}

?>
