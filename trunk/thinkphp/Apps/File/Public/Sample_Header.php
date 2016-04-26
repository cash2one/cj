<?php
require_once __DIR__ . '/PhpWord/Autoloader.php';

date_default_timezone_set('UTC');

/**
 * Header file
 */
use PhpOffice\PhpWord\Autoloader;
use PhpOffice\PhpWord\Settings;

error_reporting(E_ALL);
define('CLI', (PHP_SAPI == 'cli') ? true : false);
define('EOL', CLI ? PHP_EOL : '<br />');
define('SCRIPT_FILENAME', basename($_SERVER['SCRIPT_FILENAME'], '.php'));
define('IS_INDEX', SCRIPT_FILENAME == 'index');

Autoloader::register();
Settings::loadConfig();

// Set writers
$writers = array('HTML' => 'html');

// Return to the caller script when runs by CLI
if (CLI) {
    return;
}

// Set titles and names
$pageHeading = str_replace('_', ' ', SCRIPT_FILENAME);
$pageHeading = IS_INDEX ? '' : "<h1>{$pageHeading}</h1>";

// Populate samples
$files = '';
if ($handle = opendir('.')) {
    while (false !== ($file = readdir($handle))) {
        if (preg_match('/^Sample_\d+_/', $file)) {
            $name = str_replace('_', ' ', preg_replace('/(Sample_|\.php)/', '', $file));
            $files .= "<li><a href='{$file}'>{$name}</a></li>";
        }
    }
    closedir($handle);
}

/**
 * Write documents
 *
 * @param \PhpOffice\PhpWord\PhpWord $phpWord
 * @param string $filename
 * @param array $writers
 *
 * @return string
 */
function write($phpWord, $filename, $writers)
{;
    $result = '';
	$hostarr = explode('.', $_SERVER['HTTP_HOST']);
	$domain = rawurlencode($hostarr[0]);
	$md5 = md5($domain);
	$document_root = $_SERVER['DOCUMENT_ROOT'];
	$root_path = substr($document_root, 0, strrpos($document_root, '/'));
	$sitedir = '..'.cfg('STATICDIR').substr($md5, 0, 1).'/'.substr($md5, -1).'/'.$domain.'/'.date("Y").'/'.date("m").'/results/';
	if (!file_exists($sitedir)) {
		rmkdir($sitedir);
	}
    // Write documents
    foreach ($writers as $format => $extension) {
        if (null !== $extension) {
            $targetFile = $sitedir."{$filename}.{$extension}";
            //var_dump($targetFile);exit;
            $phpWord->save($targetFile, $format);
        }
    }
    return $result;
}
?>
