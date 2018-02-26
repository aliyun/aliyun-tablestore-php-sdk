<?php

// just create from the proto file a pb_prot[NAME].php file
require_once(__DIR__ . '/pb_parser.php');


$parser = new PBParser();
$parser->parse('ots.proto');

var_dump('File parsing done!');
?>
