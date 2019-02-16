<?php
namespace app\common\validate;
use think\Validate;

class Admin extends Validate
{
	protected $rule = [
		'admin_id' => 'require',
		'user_id' => 'require',
		'room_id' => 'require',
		'start_time' => 'require',
		'end_time' => 'require',
		'max' => 'require | num',
		'theme' => 'require',
		'admin_score' => 'num',
		'user_score' => 'num',
	];
}