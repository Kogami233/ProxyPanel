<?php

namespace App\Http\Models;

use Auth;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\DatabaseNotificationCollection;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * 用户信息
 *
 * @property int                                                        $id
 * @property string                                                     $username        昵称
 * @property string                                                     $email           邮箱
 * @property string                                                     $password        密码
 * @property int                                                        $port            代理端口
 * @property string                                                     $passwd          SS密码
 * @property string                                                     $uuid
 * @property int                                                        $transfer_enable 可用流量，单位字节，默认1TiB
 * @property int                                                        $u               已上传流量，单位字节
 * @property int                                                        $d               已下载流量，单位字节
 * @property int                                                        $t               最后使用时间
 * @property string|null                                                $ip              最后连接IP
 * @property int                                                        $enable          SS状态
 * @property string                                                     $method          加密方式
 * @property string                                                     $protocol        协议
 * @property string                                                     $obfs            混淆
 * @property string|null                                                $obfs_param      混淆参数
 * @property int                                                        $speed_limit     用户限速，为0表示不限速，单位Byte
 * @property string|null                                                $wechat          微信
 * @property string|null                                                $qq              QQ
 * @property int                                                        $usage           用途：1-手机、2-电脑、3-路由器、4-其他
 * @property int                                                        $pay_way         付费方式：0-免费、1-季付、2-月付、3-半年付、4-年付
 * @property int                                                        $credit          余额，单位分
 * @property string|null                                                $enable_time     开通日期
 * @property string                                                     $expire_time     过期时间
 * @property int                                                        $ban_time        封禁到期时间
 * @property string|null                                                $remark          备注
 * @property int|null                                                   $group_id        所属分组ID
 * @property int                                                        $level           等级，默认0级，最高9级
 * @property int                                                        $is_admin        是否管理员：0-否、1-是
 * @property string                                                     $reg_ip          注册IP
 * @property int                                                        $last_login      最后登录时间
 * @property int                                                        $referral_uid    邀请人
 * @property string|null                                                $reset_time      流量重置日期，NULL表示不重置
 * @property int                                                        $invite_num      可生成邀请码数
 * @property int                                                        $status          状态：-1-禁用、0-未激活、1-正常
 * @property string|null                                                $remember_token
 * @property Carbon|null                                                $created_at
 * @property Carbon|null                                                $updated_at
 * @property mixed                                                      $balance
 * @property-read Collection|UserLabel[]                                $label
 * @property-read int|null                                              $label_count
 * @property-read Level|null                                            $levelList
 * @property-read DatabaseNotificationCollection|DatabaseNotification[] $notifications
 * @property-read int|null                                              $notifications_count
 * @property-read Collection|Payment[]                                  $payment
 * @property-read int|null                                              $payment_count
 * @property-read User|null                                             $referral
 * @property-read UserSubscribe|null                                    $subscribe
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User query()
 * @method static Builder|User uid()
 * @method static Builder|User whereBanTime($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereCredit($value)
 * @method static Builder|User whereD($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEnable($value)
 * @method static Builder|User whereEnableTime($value)
 * @method static Builder|User whereExpireTime($value)
 * @method static Builder|User whereGroupId($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereInviteNum($value)
 * @method static Builder|User whereIp($value)
 * @method static Builder|User whereIsAdmin($value)
 * @method static Builder|User whereLastLogin($value)
 * @method static Builder|User whereLevel($value)
 * @method static Builder|User whereMethod($value)
 * @method static Builder|User whereObfs($value)
 * @method static Builder|User whereObfsParam($value)
 * @method static Builder|User wherePasswd($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User wherePayWay($value)
 * @method static Builder|User wherePort($value)
 * @method static Builder|User whereProtocol($value)
 * @method static Builder|User whereQq($value)
 * @method static Builder|User whereReferralUid($value)
 * @method static Builder|User whereRegIp($value)
 * @method static Builder|User whereRemark($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereResetTime($value)
 * @method static Builder|User whereSpeedLimit($value)
 * @method static Builder|User whereStatus($value)
 * @method static Builder|User whereT($value)
 * @method static Builder|User whereTransferEnable($value)
 * @method static Builder|User whereU($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User whereUsage($value)
 * @method static Builder|User whereUsername($value)
 * @method static Builder|User whereUuid($value)
 * @method static Builder|User whereWechat($value)
 * @mixin Eloquent
 */
class User extends Authenticatable {
	use Notifiable;

	protected $table = 'user';
	protected $primaryKey = 'id';

	function scopeUid($query) {
		return $query->whereId(Auth::user()->id);
	}

	function levelList() {
		return $this->hasOne(Level::class, 'level', 'level');
	}

	function payment() {
		return $this->hasMany(Payment::class, 'user_id', 'id');
	}

	function label() {
		return $this->hasMany(UserLabel::class, 'user_id', 'id');
	}

	function subscribe() {
		return $this->hasOne(UserSubscribe::class, 'user_id', 'id');
	}

	function referral() {
		return $this->hasOne(User::class, 'id', 'referral_uid');
	}

	function getBalanceAttribute($value) {
		return $value / 100;
	}

	function setBalanceAttribute($value) {
		return $this->attributes['balance'] = $value * 100;
	}
}
