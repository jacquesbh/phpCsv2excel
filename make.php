<?php
$p = new Phar('phpCsv2excel.phar');
$p->startBuffering();
$p->buildFromDirectory('./phpCsv2excel');
$p->stopBuffering();
