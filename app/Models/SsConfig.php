<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 配置信息
 *
 * @property int    $id
 * @property string $name       配置名
 * @property int    $type       类型：1-加密方式、2-协议、3-混淆
 * @property int    $is_default 是否默认：0-不是、1-是
 * @property int    $sort       排序：值越大排越前
 * @method static Builder|SsConfig default()
 * @method static Builder|SsConfig newModelQuery()
 * @method static Builder|SsConfig newQuery()
 * @method static Builder|SsConfig query()
 * @method static Builder|SsConfig type($type)
 * @method static Builder|SsConfig whereId($value)
 * @method static Builder|SsConfig whereIsDefault($value)
 * @method static Builder|SsConfig whereName($value)
 * @method static Builder|SsConfig whereSort($value)
 * @method static Builder|SsConfig whereType($value)
 * @mixin \Eloquent
 */
class SsConfig extends Model {
	public $timestamps = false;
	protected $table = 'ss_config';

	// 筛选默认

	public function scopeDefault($query): void {
		$query->whereIsDefault(1);
	}

	// 筛选类型
	public function scopeType($query, $type): void {
		$query->whereType($type);
	}
}
