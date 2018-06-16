<?php
/**
 * 短信
 */
class SmsAction extends Basic{

	public static $optPrefix = '20180115send_sms_';

	public static $smsTimes = 20;

	public static $intervalTime = 86400;

	public function send() {
		$result = array(
			"code" => "400",
			"message" => "操作失败"
		);

		$params = $this->getParams();
		$phone = $_POST['smsphone'];
		$smsType = $_POST['smstype'];

		//验证号码是否为正确的手机号
		if (!Validator::checkPhone($phone)) {
			$result['message'] = "手机号码不正确";
			echo json_encode($result);
			exit;
		}

		$redisCache = new BizBasicCache();
		//默认一个手机号50秒才能发送一次
		$cachekey_phone_60 = self::$optPrefix.'smssend_50'.$phone;
		if ($redisCache->get($cachekey_phone_60)) {
			$result['message'] = "短信已发送，请注意查收!";
			echo json_encode($result);
			exit;
		}

		switch ($smsType) {
			//发送验证码
			case 1:

				//检查手机号是否已被注册
				$rs = UserService::getUserInfoByPhone($phone);
				if ($rs && $rs['userid']) {
					$result['message'] = "该手机号码已注册！";
					echo json_encode($result);
					exit;
				}



				$optkey = self::$optPrefix.'activesmssend_'.$phone;
				$limitTimes = self::$smsTimes;
				$intervalTime = self::$intervalTime;
				if(!OperateMonitor::canExecute($optkey, $limitTimes, $intervalTime)){
					$status['message'] = "一天内只能发送{$limitTimes}次短信";
					return $status;
				}

				break;
			default:
				$result['message'] = "请选择短信类型";
				echo json_encode($result);
				exit;
				break;
		}

		#开始走发送流程
		$smsCode = rand(100001, 999999);
		//新短信接入商，采用模板格式
		if($smsType == 1){
			$message = "@1@={$smsCode}";
		}else{
			$message = "@1@={$smsCode}";
		}

		$res = SmsService::sendMsg($phone, $message, $error);
		if($res == false){
			$result['message'] = '下发短信失败';
			echo json_encode($result);
			exit;
		}

		$time = time();
		$data = array(
			'phone' => $phone,
			'code' => $smsCode,
			'content' => "您的验证码{$smsCode}",
			'inserttime' => $time,
		);

		$smsModel = new SmsModule();
		$rs = $smsModel->insert(SmsModule::$_table, $data);

		if (!$rs) {
			$result['message'] = '短信写入失败';
			echo json_encode($result);
			exit;
		}

		$redisCache->setex($cachekey_phone_60, 1, 50);

		$result['code'] = "200";
		$result['message'] = "请注意查收短信";
		$result['unixtime'] = $time;//时间验证
		echo json_encode($result);
		exit;
	}


}
