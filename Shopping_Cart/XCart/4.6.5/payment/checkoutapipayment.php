<?php

include 'includes/autoload.php';

$methodObject = new Model();
//$module_params = func_get_pm_params('checkoutapipayment.php');
if (!isset($REQUEST_METHOD))
    $REQUEST_METHOD = $_SERVER['REQUEST_METHOD'];


if ($REQUEST_METHOD == 'GET' )
{
    $methodObject->handleResponse();
}

else
{
    if (!defined('XCART_START'))
    {
        header("Location: ../");
        die("Access denied");
    }

    $methodObject->handleRequest();

die('here');
}

