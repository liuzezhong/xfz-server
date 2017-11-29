<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/10/1
 * Time: 23:25
 */

function curlGet($url) {
    //初始化
    $ch = curl_init();
    //设置选项，包括URL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    //执行并获取HTML文档内容
    $output = curl_exec($ch);
    //释放curl句柄
    curl_close($ch);
    //打印获得的数据
    return $output;
}

function createNonceStr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
}

/**
 * 冒泡排序
 * @param $numbers
 * @return mixed
 */
function bubbleSort($numbers) {
    $cnt = count($numbers);
    for ($i = 0; $i < $cnt; $i++) {
        for ($j = 0; $j < $cnt - $i - 1; $j++) {
            if ($numbers[$j] > $numbers[$j + 1]) {
                $temp = $numbers[$j];
                $numbers[$j] = $numbers[$j + 1];
                $numbers[$j + 1] = $temp;
            }
        }
    }
    return $numbers;
}