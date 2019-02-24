<?php
namespace app\android\controller;

use think\Controller;
use think\Request;
use app\common\model\User;
use app\common\model\Admin;
use app\common\model\Room;
use app\common\model\Lease;
use app\common\model\Check;

class ClientController extends Controller
{
	public function login()
	{
		$param = Request::instance()->param();
		if (!isset($param['username']) ||!isset($param['password']))
			return ;
		$username = $param['username'];
		$password = $param['password'];
		$kind = 0;

		if (User::Login($username, $password))
		{
			$user = User::get(session('user_id'));

			echo('<user_id>' . $user->getData('user_id') . '</user_id>');
			echo('<nickname>' . $user->getData('nickname') . '</nickname>');
			echo('<mobile>' . $user->getData('mobile') . '</mobile>');
			echo('<email>' . $user->getData('email') . '</email>');
			echo('<credit>' . $user->getData('credit') . '</credit>');
			echo('<company>' . $user->getData('company') . '</company>');
			echo('<post>' . $user->getData('post') . '</post>');
		}
		else
		{
			echo "<result>用户名或密码错误</result>";
		}
	}

	public function register()
	{
		;
	}

	public function leases()
	{
		$user_id = Request::instance()->param('user_id/d');

		$Lease = new Lease;
		$leases = $Lease->where('user_id=' . $user_id)->select();
		$str = "";
		foreach ($leases as $lease) {
			$str .= $lease->toString();
		}
		return $str;
	}

	public function meetings()
	{
		$user_id = Request::instance()->param('user_id/d');
		$Check = new Check;
		$checks = $Check->where('user_id=' . $user_id)->select();
		$str = "";
		foreach ($checks as $check) {
			$str .= $check->Lease->toString();
		}
		return $str;
	}

	public function updateUserInfo()
	{
		$param = Request::instance()->param();
		$user_id = $param['user_id'];
		if ($user_id == "") return "<result>无法获取用户ID</result>";
		$user = User::get($user_id);
		if (is_null($user))	return "<result>无法获取用户</result>";

		if (isset($param['nickname']))
			$user->nickname = $param['nickname'];

		if (isset($param['username']))
			$user->username = $param['username'];

		if (isset($param['password']))
			$user->password = $param['password'];

		if (isset($param['mobile']))
			$user->mobile = $param['mobile'];

		if (isset($param['email']))
			$user->email = $param['email'];

		if (isset($param['company']))
			$user->company = $param['company'];

		if (isset($param['post']))
			$user->post = $param['post'];

		if ($user->isUpdate(true)->save())
			return "<result>OK</result>";
		else
			return "</result>". $user->error() . "</result>";

	}

	public function joinLease()
	{
		$user_id = Request::instance()->param('user_id');
		$lease_id = Request::instance()->param('lease_id');
		if ($user_id == "" || $lease_id == "") {
			return "<result>无法获取ID</result>";
		}
		$map = ['lease_id'=>$lease_id, 'user_id'=>$user_id];
		$check = Check::get($map);
		if (is_null($check)) // 没有添加
		{
			$check = new Check;
			$check->lease_id = $lease_id;
			$check->user_id = $user_id;
			$check->save();
			return "<result>OK</result>";
		}
		else
		{
			return "<result>您已经参加了，无需重复加入</result>";
		}
	}

	public function addLease()
	{


	}

	public function updateLeaseInfo()
	{

	}

	public function deleteLease()
	{

	}
}
