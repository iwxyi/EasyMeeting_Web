<?php
namespace app\common\model;
use think\Model;

class User extends Model
{
	static public function login($username,  $password)
	{
		$map = array('username' => $username);
		$User = self::get($map); // 同时赋值给自己？

		if (!is_null($User) && $User->checkPassword($password)) {
			session('user_id', $User->getData('user_id'));
			return true;
		}

		return false;
	}

	private function checkPassword($password)
	{
		return ($this->getData('password') === $this::encryptPassword($password)
			|| $this->getData('password') === $password); // 调试，允许不加密的密码
	}

	static private function encryptPassword($password)
	{
		if (!is_string($password))
			throw new \RuntimeException("传入变量类型非字符串");
		return sha1(md5($password) . 'smartmeeting');
	}

	static public function logOut()
	{
		session('user_id', null);
		return true;
	}

	static public function isLogin()
	{
		$user_id = session('user_id');
		return (empty($user_id));
	}

	public function getName()
	{
		if (!empty($this->getData('nickname')))
			return $this->getData('nickname');
		return $this->getData('username');
	}

	/**
	 * 使用属性进行设置特殊的显示方式
	 * @param  int    $value 信用度数值
	 * @return string        信用度星级
	 */
	public function getCreditAttr($value)
	{
		if ($value < 0)
			return '无星';
		else if ($value <= 200)
			return '一星';
		else if ($value < 1000)
			return '二星';
		else if ($value < 2000)
			return '三星';
		else if ($value < 5000)
			return '四星';
		else
			return '五星';
	}

}