<?php

namespace App\Components;

use App\Http\Models\Config;
use App\Http\Models\CouponLog;
use App\Http\Models\Level;
use App\Http\Models\NotificationLog;
use App\Http\Models\SsConfig;
use App\Http\Models\User;
use App\Http\Models\UserBalanceLog;
use App\Http\Models\UserSubscribe;
use App\Http\Models\UserTrafficModifyLog;

class Helpers {
	// 不生成的端口
	private static $denyPorts = [
		1068, 1109, 1434, 3127, 3128,
		3129, 3130, 3332, 4444, 5554,
		6669, 8080, 8081, 8082, 8181,
		8282, 9996, 17185, 24554, 35601,
		60177, 60179
	];

	// 加密方式
	public static function methodList() {
		return SsConfig::type(1)->get();
	}

	// 协议
	public static function protocolList() {
		return SsConfig::type(2)->get();
	}

	// 混淆
	public static function obfsList() {
		return SsConfig::type(3)->get();
	}

	// 等级
	public static function levelList() {
		return Level::query()->get()->sortBy('level');
	}

	// 生成用户的订阅码
	public static function makeSubscribeCode() {
		$code = makeRandStr(5);
		if(UserSubscribe::query()->whereCode($code)->exists()){
			$code = self::makeSubscribeCode();
		}

		return $code;
	}

	/**
	 * 添加用户
	 *
	 * @param  string  $email            用户邮箱
	 * @param  string  $password         用户密码
	 * @param  string  $transfer_enable  可用流量
	 * @param  int     $data             可使用天数
	 * @param  int     $referral_uid     邀请人
	 *
	 * @return int
	 */
	public static function addUser($email, $password, $transfer_enable, $data, $referral_uid = 0) {
		$user = new User();
		$user->username = $email;
		$user->email = $email;
		$user->password = $password;
		// 生成一个可用端口
		$user->port = self::systemConfig()['is_rand_port']? Helpers::getRandPort() : Helpers::getOnlyPort();
		$user->passwd = makeRandStr();
		$user->vmess_id = createGuid();
		$user->enable = 1;
		$user->method = Helpers::getDefaultMethod();
		$user->protocol = Helpers::getDefaultProtocol();
		$user->protocol_param = '';
		$user->obfs = Helpers::getDefaultObfs();
		$user->obfs_param = '';
		$user->usage = 1;
		$user->transfer_enable = $transfer_enable;
		$user->enable_time = date('Y-m-d');
		$user->expire_time = date('Y-m-d', strtotime("+".$data." days"));
		$user->reg_ip = getClientIp();
		$user->referral_uid = $referral_uid;
		$user->reset_time = null;
		$user->status = 0;
		$user->save();

		return $user->id;
	}

	// 获取系统配置
	public static function systemConfig() {
		$config = Config::query()->get();
		$data = [];
		foreach($config as $vo){
			$data[$vo->name] = $vo->value;
		}

		$data['is_onlinePay'] = ($data['is_AliPay'] || $data['is_QQPay'] || $data['is_WeChatPay'] || $data['is_otherPay'])?: 0;

		return $data;
	}

	// 获取一个随机端口
	public static function getRandPort() {
		$config = self::systemConfig();
		$port = mt_rand($config['min_port'], $config['max_port']);

		$exists_port = User::query()->pluck('port')->toArray();
		if(in_array($port, $exists_port) || in_array($port, self::$denyPorts)){
			$port = self::getRandPort();
		}

		return $port;
	}

	// 获取一个随机端口
	public static function getOnlyPort() {
		$config = self::systemConfig();
		$port = $config['min_port'];

		$exists_port = User::query()->where('port', '>=', $port)->pluck('port')->toArray();
		while(in_array($port, $exists_port) || in_array($port, self::$denyPorts)){
			$port = $port + 1;
		}

		return $port;
	}

	// 获取默认加密方式
	public static function getDefaultMethod() {
		$config = SsConfig::default()->type(1)->first();

		return $config? $config->name : 'aes-256-cfb';
	}

	// 获取默认协议
	public static function getDefaultProtocol() {
		$config = SsConfig::default()->type(2)->first();

		return $config? $config->name : 'origin';
	}

	// 获取默认混淆
	public static function getDefaultObfs() {
		$config = SsConfig::default()->type(3)->first();

		return $config? $config->name : 'plain';
	}

	/**
	 * 添加通知推送日志
	 *
	 * @param  string  $title    标题
	 * @param  string  $content  内容
	 * @param  int     $type     发送类型
	 * @param  string  $address  收信方
	 * @param  int     $status   投递状态
	 * @param  string  $error    投递失败时记录的异常信息
	 *
	 * @return int
	 */
	public static function addNotificationLog($title, $content, $type, $address = 'admin', $status = 1, $error = '') {
		$log = new NotificationLog();
		$log->type = $type;
		$log->address = $address;
		$log->title = $title;
		$log->content = $content;
		$log->status = $status;
		$log->error = $error;
		$log->save();

		return $log->id;
	}

	/**
	 * 添加优惠券操作日志
	 *
	 * @param  int     $couponId  优惠券ID
	 * @param  int     $goodsId   商品ID
	 * @param  int     $orderId   订单ID
	 * @param  string  $desc      备注
	 *
	 * @return int
	 */
	public static function addCouponLog($couponId, $goodsId, $orderId, $desc = '') {
		$log = new CouponLog();
		$log->coupon_id = $couponId;
		$log->goods_id = $goodsId;
		$log->order_id = $orderId;
		$log->desc = $desc;

		return $log->save();
	}

	/**
	 * 记录余额操作日志
	 *
	 * @param  int     $userId  用户ID
	 * @param  string  $oid     订单ID
	 * @param  int     $before  记录前余额
	 * @param  int     $after   记录后余额
	 * @param  int     $amount  发生金额
	 * @param  string  $desc    描述
	 *
	 * @return int
	 */
	public static function addUserBalanceLog($userId, $oid, $before, $after, $amount, $desc = '') {
		$log = new UserBalanceLog();
		$log->user_id = $userId;
		$log->order_id = $oid;
		$log->before = $before;
		$log->after = $after;
		$log->amount = $amount;
		$log->desc = $desc;
		$log->created_at = date('Y-m-d H:i:s');

		return $log->save();
	}

	/**
	 * 记录流量变动日志
	 *
	 * @param  int     $userId  用户ID
	 * @param  string  $oid     订单ID
	 * @param  int     $before  记录前的值
	 * @param  int     $after   记录后的值
	 * @param  string  $desc    描述
	 *
	 * @return int
	 */
	public static function addUserTrafficModifyLog($userId, $oid, $before, $after, $desc = '') {
		$log = new UserTrafficModifyLog();
		$log->user_id = $userId;
		$log->order_id = $oid;
		$log->before = $before;
		$log->after = $after;
		$log->desc = $desc;

		return $log->save();
	}
}
