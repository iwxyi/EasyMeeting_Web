<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use app\common\model\Admin;
use app\common\model\User;

class LoginController extends Controller
{
	public function index()
	{
		return $this->fetch();
	}

	public function login()
	{
		// 接收 post 信息
		$post = Request::instance()->post();

		if (isset($post['register'])) {
			return $this->fetch('userRegister');
		}

		// 直接调用 M 层方法，进行登录
		if (Admin::login($post['username'], $post['password'])) {
			return $this->success('管理员登录成功', url('Room/index'));
		}
		else if (User::login($post['username'], $post['password'])) {
			return $this->success('用户登录成功', url('Client/index'));
		}
		else {
			return $this->error('用户名不存在或密码错误', Request::instance()->header('referer'));
		}
	}

	public function logOut()
	{
		$result1 = Admin::logOut();
		$result2 = User::logOut();
		if ($result1 || $result2) {
			return $this->success('注销成功', url('index'));
		} else {
			return $this->error('退出失败', url('index'));
		}
	}

	/**
	 * 进入到用户注册页面
	 */
	public function userRegister()
	{
		return $this->fetch();
	}

	/**
	 * 用户进行注册操作
	 */
	public function userAdd()
	{
		$post = Request::instance()->post();
		$user = new User;
		$user->username = $post['username'];
		$user->password = $post['password'];
		$user->nickname = $post['nickname'];

		$map = [
			'username' => $user->username,
		];
		$temp_user = User::get($map);
		if (!is_null($temp_user)) {
			return $this->error('用户账号已存在', url('Login/userRegister'));
		}

		$state = $user->validate(true)->save();
		if($state)
			return $this->success('注册成功', url('Client/index'));
		else
			return $this->error('注册失败' . $user->getError(), url('Login/userRegister'));

	}
}