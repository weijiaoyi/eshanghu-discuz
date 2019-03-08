<?php

class Eshanghu
{

    private $url = 'https://1shanghu.com/api/wechat/native';

    public function pay()
    {
        global $_G, $eshanghu;
        if ($_POST['money'] <= 0) return 'money error';

        $out_trade_no = date('YmdHis') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        $data         = [
            'app_key' => $eshanghu['app_key'];
            'subject' => '积分充值',
            'total_fee'    => $_POST['money'] * 100,
            'out_trade_no' => $out_trade_no,
            'extra' => 'wait',
            'notify_url'   => trim($_G['siteurl'] . 'source/plugin/eshanghu/notify.php'),
        ];
        $data['sign'] = $this->sign($data);

        $this->insert($data);
        $result = $this->httpPost($this->url, $data);
        return $result;
    }

    public function insert($arr)
    {
        global $_G, $eshanghu;
        $data = [
            'orderid'    => $arr['out_trade_no'],
            'status'     => 1,
            'uid'        => $_G['uid'],
            'amount'     => $arr["total_fee"] / 100 * $eshanghu['integral_proportion'],
            'price'      => $arr["total_fee"] / 100,
            'submitdate' => time(),
            'ip'         => $_SERVER['REMOTE_ADDR'],
        ];

        C::t('forum_order')->insert($data);
        return;
    }

    public function check()
    {
        $orderid = $_GET['orderid'];
        $order   = DB::fetch_first("select * from " . DB::table('forum_order') . " where orderid='" . $orderid . "' and status=2");
        if ($order) {
            return 'paid';
        } else {
            return 'notpaid';
        }
    }

    public function sign($arr)
    {
        global $eshanghu;
        array_filter($arr);
        ksort($arr);
        $rows = array();
        foreach ($arr as $key => $value) {
            $rows[] = "{$key}={$value}";
        }
        $s = implode('&', $rows).$eshanghu['app_secret'];
        $sign = strtoupper(md5($s));
        return $sign;
    }

    public function httpPost($url, $data)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Discuz Plugin CLIENT');
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

}

?>
