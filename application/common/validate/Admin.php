<?php
namespace app\common\validate;
use think\Validate;

class Admin extends Validate
{
	protected $rule = [
		'username' => 'require|chsDash|length:4,100',
		'password' => 'require|chsDash|length:1,100',
		'nickname' => 'chsDash|length:2,10', // 中文数字字母下划线
		'permission' => 'in:0,1,2,9'
	];
}