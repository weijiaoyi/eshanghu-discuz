<?php
if (!defined('IN_DISCUZ')) exit('Access Denied');
include_once("sdk.class.php");

$ac    = isset($_REQUEST['ac']) ? $_REQUEST['ac'] : '';
$eshanghu = $_G['cache']['plugin']['eshanghu'];

$app = new Eshanghu();

switch ($ac) {
    case 'pay':
        $rst = $app->pay();
        break;

    case 'check':
        $rst = $app->check();
        break;
}

echo $rst;


