<?php
/**
 */
date_default_timezone_set('PRC');
$dir = dirname(__FILE__);
//全局配置文件目录
define('APP_ROOT', realpath($dir . '/../../'));
require_once(APP_ROOT.'/../bfw/Main.php');
require_once(APP_ROOT."/application/config/config.inc.php");
Main::setConfig($appConfig);
Main::init();


$model = new DBModel('default');

//100个
for ($i = 0;$i<10;$i++) {
    $num = mt_rand(3,9);
    $list = generateCode(10, "", $num);
    foreach ($list as $hostname) {
        $sql = "select * from sitelist where hostname='{$hostname}'";
        $hostinfo = $model->_db->query($sql)->fetch();
        if ($hostinfo) {
            continue;
        }

        $upsql = "insert into sitelist (hostname,)";
        $model->_db->exec($sql);


    }
}


/**
 * 生成vip激活码
 * @param int $nums             生成多少个优惠码
 * @param array $exist_array     排除指定数组中的优惠码
 * @param int $code_length         生成优惠码的长度
 * @param int $prefix              生成指定前缀
 * @return array                 返回优惠码数组
 */
function generateCode( $nums, $exist_array='', $code_length = 6, $prefix = '' ) {

    $characters = "0123456789abcdefghijklmnpqrstuvwxyz";
    $promotion_codes = array();//这个数组用来接收生成的优惠码

    for($j = 0 ; $j < $nums; $j++) {

        $code = '';

        for ($i = 0; $i < $code_length; $i++) {

            $code .= $characters[mt_rand(0, strlen($characters)-1)];

        }

        //如果生成的4位随机数不再我们定义的$promotion_codes数组里面
        if( !in_array($code,$promotion_codes) ) {

            if( is_array($exist_array) ) {

                if( !in_array($code,$exist_array) ) {//排除已经使用的优惠码

                    $promotion_codes[$j] = $prefix.$code; //将生成的新优惠码赋值给promotion_codes数组

                } else {

                    $j--;

                }

            } else {

                $promotion_codes[$j] = $prefix.$code;//将优惠码赋值给数组

            }

        } else {
            $j--;
        }
    }

    return $promotion_codes;
}






