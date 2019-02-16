<?php
namespace app\common\model;
use think\Model;

class Admin extends Model
{
	static public function login($username,  $password)
	{
		$map = array('username' => $username);
		$Admin = self::get($map); // 同时赋值给自己？

		if (!is_null($Admin) && $Admin->checkPassword($password)) {
			session('admin_id', $Admin->getData('admin_id'));
			return true;
		}

		return false;
	}

	private function checkPassword($password)
	{
		return ($this->getData('password') === $this::encryptPassword($password)
			|| $this->getData('password') == $password);
	}

	static private function encryptPassword($password)
	{
		if (!is_string($password))
			throw new \RuntimeException("传入变量类型非字符串");
		return sha1(md5($password) . 'smartmeeting');
	}

	static public function logOut()
	{
		session('admin_id', null);
		return true;
	}

	static public function isLogin()
	{
		$admin_id = session('admin_id');
		return (isset($admin_id));
	}

	public function getName()
	{
		if (!empty($this->getData('nickname')))
			return $this->getData('nickname');
		return $this->getData('username');
	}

}