<?php
header("Content-type: text/html; charset=utf-8");
require '../../class/class_core.php';
require '../../function/function_forum.php';
$discuz = C::app();
$discuz->init();
loadcache('plugin');
$eshanghu = $_G['cache']['plugin']['eshanghu'];

checkSign($_POST);

$orderid = $_REQUEST['out_trade_no'];

$order = DB::fetch_first("select * from " . DB::table('forum_order') . " where orderid='" . $orderid . "' and status=1");
if ($order) {
    // 更新订单状态
    $data  = ['status' => 2, 'confirmdate' => time(),];
    $where = ['orderid' => $orderid];
    DB::update('forum_order', $data, $where);

    // 更新用户积分
    updatemembercount($order['uid'], [$_G['setting']['creditstrans'] => $order['amount']], true, '', 1, '', '微信支付充值');

    // 积分消息提醒
    notification_add($order['uid'], 'system', 'addfunds', [
        'orderid'     => $order['orderid'],
        'price'       => $order['price'],
        'from_id'     => 0,
        'from_idtype' => 'buycredit',
        'value'       => $_G['setting']['extcredits'][$_G['setting']['creditstrans']]['title'] . ' ' . $order['amount'] . ' ' . $_G['setting']['extcredits'][$_G['setting']['creditstrans']]['unit'],
    ], 1);
}

echo 'success';

function checkSign($arr)
{
    global $eshanghu;
    $user_sign = $arr['sign'];
    unset($arr['sign']);
    array_filter($arr);
    ksort($arr);
    $rows = array();
    foreach ($arr as $key => $value) {
        $rows[] = "{$key}={$value}";
    }
    $s = implode('&', $rows).$eshanghu['app_secret'];
    $check_sign = strtoupper(md5($s));

    if ($user_sign != $check_sign)
        die('签名错误');
}

?>
