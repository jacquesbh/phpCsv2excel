<?php

// The script name
$scriptName = array_shift($argv);

// Usage
if (count($argv) != 2) {
    goto usage;
} else {
    goto go;
}

usage:
echo <<<USAGE
Usage:
    php -f $scriptName "<files_path>" <zip_archive>
    php -f $scriptName <zip_archive> <destination_directory>

USAGE;
exit(65);

go:

// The args
$arg1 = array_shift($argv);
$arg2 = array_shift($argv);

// Check direction
if (substr($arg1, -4) == '.zip') {
    if (!is_dir($arg2)) {
        goto usage;
    }
    goto zipExcel2csv;
}


csv2excel:
// ---------------------------------------------------------

// Files path
$path = $arg1;

// The archive filename
$archive = $arg2;

// Files
$files = glob($path);

// If we have no files
if (!count($files)) {
    echo "Please add correct <files_path> option.\n";
    exit(0);
}

// Php Excel (With few changes)
require_once 'lib/php-excel-v1.1/php-excel.class.php';

// The archive
$zip = new ZipArchive;
if ($zip->open($archive, ZipArchive::CREATE) !== true) {
    echo "Can't open the archive.\n";
    exit(0);
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

goto yeah;


zipExcel2csv:
// ---------------------------------------------------------

// We need to extract files from the .zip
$zipfile = $arg1;
$destDir = $arg2;

$zip = new ZipArchive;
if (true !== $zip->open($zipfile)) {
    echo "Can't open the archive.";
    exit(0);
}

// Make temp dir
if (!@mkdir($tmpDestDir = sys_get_temp_dir() . '/phpCsv2xml_' . uniqid())) {
    echo "Temporary directory not writable.\n";
    exit(0);
}

// Extract into temp dir
if (!$zip->extractTo($tmpDestDir)) {
    echo "Can't extract the archive.\n";
    exit(0);
}

$files = glob($tmpDestDir . '/' . str_replace('.zip', '', basename($zipfile)) . '/*.xml');
foreach ($files as $file) {
    $newFilename = $destDir . '/' . str_replace('.xml', '.csv', basename($file));
    $csv = fopen($newFilename, 'a');
    echo "Processing $newFilename...\n";
    $excel = simplexml_load_file($file);
    foreach ($excel->Worksheet->Table as $rows) {
        foreach ($rows as $cells) {
            $row = array();
            foreach ($cells as $data) {
                $row[] = '"' . str_replace('"', '""', html_entity_decode($data->Data)) . '"';
            }
            fputs($csv, implode(',', $row) . "\n");
        }
    }
    fclose($csv);
    unlink($file);
}

// Remove temp dir
@rmdir($tmpDestDir);

yeah:
// ---------------------------------------------------------
echo "Done.\n";
exit(1);
