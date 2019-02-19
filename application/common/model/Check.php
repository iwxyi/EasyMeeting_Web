<?php
namespace app\common\model;
use think\Model;

class Check extends Model
{
	public function User()
	{
		$user_id = $this->getData('user_id');
		$user = User::get($user_id);
		if (is_null($user))
			throw new Exception('找不到对应的ID', 1);
		return $user;
	}

}