<?php
$content  = file_get_contents('php://input');
$input = json_decode($content, true);
var_dump($input);

