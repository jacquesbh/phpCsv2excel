<?php

// The script name
$scriptName = array_shift($argv);

// Usage
if (count($argv) != 2) {
    echo "Usage: php -f $scriptName \"<files_path>\" <zip_archive>\n";
    exit;
}

// Files path
$path = array_shift($argv);

// The archive filename
$archive = array_shift($argv);

// Files
$files = glob($path);

// If we have no files
if (!count($files)) {
    echo "Please add correct <files_path> option.\n";
    exit;
}

// Php Excel (With few changes)
require_once 'lib/php-excel-v1.1/php-excel.class.php';

// The archive
$zip = new ZipArchive();
if ($zip->open($archive, ZipArchive::CREATE) !== true) {
    echo "Can't open the archive.\n";
    exit;
}

// We have files... just read them, then process for xml files
foreach ($files as $filename) {
    echo "Processing $filename...\n";
    $xls = new Excel_XML;
    $fp = fopen($filename, 'r');
    while ($line = fgetcsv($fp)) {
        $xls->addRow($line);
    }
    $newFilename = str_replace('.zip', '', basename($archive)) . '/';
    $newFilename .= basename(str_replace('.csv', '.xml', $filename));
    $zip->addFromString($newFilename, $xls->generateXML());
}

$zip->close();

echo "Done.\n";
