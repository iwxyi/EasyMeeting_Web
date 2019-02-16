<?php
use app\common\validate;
use think\Validate;

class Room extends Validate
{
	protected $rule = [
		'max' => 'rquire|num',
	];
}