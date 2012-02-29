<?php
$p = new Phar('phpCsv2xml.phar');
$p->startBuffering();
$p->buildFromDirectory('./phpCsv2xml');
$p->stopBuffering();
