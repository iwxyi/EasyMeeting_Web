<?php
namespace app\index\Controller;
use app\common\model\Admin;
use app\common\model\User;
use app\common\model\Lease;
use app\common\model\Room;
use app\common\model\Check;
use think\Controller;
use think\Request;

class CheckController extends Controller
{
	public function __construct()
	{
		parent::__construct();

		/*if (!User::isLogin()) {
			return $this->error("请先登录", url('Login/index'));
		}*/
	}

	public function index()
	{
		return $this->participants();
	}

	public function participants()
	{
		$lease_id = Request::instance()->param('lease_id');
		$checks = new Check;
		$checks->where('lease_id=' . $lease_id)->order("check_id desc");
		$checks = $checks->select();

		$this->assign('checks', $checks);
		$this->assign('lease', Lease::get($lease_id));
		return $this->fetch('participants');
	}

	public function invite()
	{
		$lease_id = Request::instance()->param('lease_id');
		$this->assign('lease', Lease::get($lease_id));
		return $this->fetch('');
	}

	public function add()
	{
		$lease_id = Request::instance()->param('lease_id');
		$lease = Lease::get($lease_id);
		$this->assign('ids', $this->allIds());
		$this->assign('lease', $lease);
		return $this->fetch();
	}

	public function inserts()
	{
		$lease_id = Request::instance()->param('lease_id');
		$user_ids = Request::instance()->param('user_ids');
		$user_ids = explode(' ', $user_ids);
		$add = 0;
		$exi = 0;

		foreach ($user_ids as $user_id) {
			$Check = new Check;
			$map = ['lease_id'=>$lease_id, 'user_id'=>$user_id];
			$Check->where("`lease_id`='" . $lease_id . "' and `user_id`='" . $user_id . "'");
			$checks = $Check->select();
			if (count($checks) == 0) {
				$check = new Check;
				$check->lease_id = $lease_id;
				$check->user_id = $user_id;
				$check->save();
				$add++;
			} else  {
				$exi++;
			}
		}
		return $this->success('批量添加成功，加入' . $add . '人，已存在' . $exi . '人', url('index?lease_id=' . $lease_id));
	}

	public function toCheck()
	{
		$check_id = Request::instance()->param('check_id');
		$check = Check::get($check_id);
		if (is_null($check))
			return $this->error('找不到对应的ID', Request::instance()->header('referer'));
		if ($check->checked == false)
		{
			$check->checked = true;
			$state = $check->save();
			return $this->success('签到成功', Request::instance()->header('referer'));
		}
		else
		{
			$check->checked = false;
			$state = $check->save();
			return $this->success('取消签到成功', Request::instance()->header('referer'));
		}
	}

	public function toLeave()
	{
		$check_id = Request::instance()->param('check_id');
		$check = Check::get($check_id);
		if (is_null($check))
			return $this->error('找不到对应的ID', Request::instance()->header('referer'));
		if ($check->leave == false)
		{
			$check->leave = true;
			$state = $check->save();
			return $this->success('签退成功', Request::instance()->header('referer'));
		}
		else
		{
			$check->leave = false;
			$state = $check->save();
			return $this->success('取消签退成功', Request::instance()->header('referer'));
		}
	}

	public function toDelete()
	{
		$check_id = Request::instance()->param('check_id');
		$check = Check::get($check_id);
		if (is_null($check))
			return $this->error('找不到对应的ID', Request::instance()->header('referer'));
		$check->checked = true;
		$state = $check->delete();
		return $this->success('删除人员成功', Request::instance()->header('referer'));
	}

	public function allIds()
	{
		$lease_id = Request::instance()->param('lease_id');
		$Check = new Check;
		$map = ['lease_id' => $lease_id];
		$checks = $Check->where($map)->select();
		$ans = '';
		foreach ($checks as $check) {
			$ans .= $check->user_id . ' ';
		}
		return trim($ans);
	}
}