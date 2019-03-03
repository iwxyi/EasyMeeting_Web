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

	public function insertLease()
	{
		$lease = new Lease;
		$params = Request::instance()->param();

		if (isset($params['start_time']))
			$lease->start_time = $params['start_time'];
		else
			$lease->start_time = 0;
		if (isset($params['end_time']))
			$lease->end_time = $params['end_time'];
		else
			$lease->end_time = 0;
		if (isset($params['finish_time']))
			$lease->finish_time = strtotime($params['finish_time']);
		else
			$lease->finish_time = $lease->end_time;

		$lease->room_id = $params['room_id'];
		if ($lease->room_id == '0') // 选中的是智能分配会议室，调用 Room 的静态变量来获取有空的会议室
			$lease->room_id = Room::getIntelliDistribute($lease->start_time, $lease->end_time);
		$lease->admin_id = $params['admin_id'];
		if ($lease->admin_id == '0') // 会议室默认管理员
			$lease->admin_id = Room::get($lease->room_id)->getData('admin_id');
		$lease->user_id = $params['user_id'];
		
		$lease->theme = $params['theme'];
		$lease->usage = $params['usage'];
		$lease->message = $params['message'];

		if (isset($params['sweep']) && $params['sweep'] != "false" && $params['sweep'] != "0")
			$lease->sweep = true;
		else
			$lease->sweep = false;
		if (isset($params['entertain']) && $params['entertain'] != "false" && $params['entertain'] != "0")
			$lease->entertain = true;
		else
			$lease->entertain = false;
		if (isset($params['remote']) && $params['remote'] != "false" && $params['remote'] != "0")
			$lease->remote = true;
		else
			$lease->remote = false;

		$state = $lease->validate(true)->save();
		if ($state) {
			return "<result>OK</result>";
		} else {
			return "<result>添加失败" . $lease->error() . "</result>";
		}
	}

	public function updateLease()
	{
		$params = Request::instance()->param();
		$lease_id = $params['lease_id'];
		$lease = Lease::get($lease_id);
		if (!is_null($lease)) {
			
			$lease->room_id = $params['room_id'];
			$lease->admin_id = $params['admin_id'];
			$lease->user_id = $params['user_id'];
			
			$lease->theme = $params['theme'];
			$lease->usage = $params['usage'];
			$lease->message = $params['message'];

			if (isset($params['sweep']))
				$lease->sweep = true;
			else
				$lease->sweep = false;
			if (isset($params['entertain']))
				$lease->entertain = true;
			else
				$lease->entertain = false;
			if (isset($params['remote']))
				$lease->remote = true;
			else
				$lease->remote = false;

			if (isset($params['start_time']))
				$lease->start_time = $params['start_time'];
			else
				$lease->start_time = 0;
			if (isset($params['finish_time']))
				$lease->finish_time = $params['finish_time'];
			else
				$lease->finish_time = $lease->start_time+7200000;

			$state = $lease->validate(true)->save();
			if ($state) {
				return "<result>OK</result>";
			} else {
				return "<result>更新失败" . $lease->getError() . "</result>";
			}
		}
	}

	public function deleteLease()
	{

	}
}
