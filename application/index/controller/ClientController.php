<?php
namespace app\index\controller;

use think\Controller;
use think\Request;
use app\common\model\User;
use app\common\model\Admin;
use app\common\model\Room;
use app\common\model\Lease;
use app\common\model\Check;

class ClientController extends Controller
{
	public function __construct()
	{
		parent::__construct();
		if (User::isLogin())
			return $this->error('请先登录', url('Login/index'));
	}

	public function index()
	{
		return $this->myLeases();
	}

	public function myLeases()
	{
		$leases = new Lease;

		$name = Request::instance()->param('name');
		if (!empty($name)) {
			$w = "%$name%";
			$leases->where(" room_id like '$w' or lease_id like '$w' or theme like '$w' or message like '$w'");
		}

		$user_id = session('user_id');
		if (empty($user_id))
			return $this->error('请先登录', url('Login/index'));

		$leases->where("user_id = '$user_id'")->order('start_time desc');
		$leases = $leases->select();
		$this->assign('leases', $leases);
		return $this->fetch('my_leases');
	}

	public function addLease()
	{
		$this->assign('leases',  Lease::all());
		$this->assign('admins', Admin::all());
		$this->assign('rooms', Room::getFreeRooms());
		$this->assign('user', User::get(session('user_id')));

		$this->assign('start_time', Lease::getSuitableTime());
		$this->assign('finish_time', Lease::getSuitableTime(7200));

		return $this->fetch();
	}

	public function insertLease()
	{
		$lease = new Lease;
		$post = Request::instance()->post();

		// 获取时间信息
		if (isset($post['start_time']))
			$lease->start_time = strtotime($post['start_time']);
		else
			$lease->start_time = 0;
		if (isset($post['finish_time']))
			$lease->finish_time = strtotime($post['finish_time']);
		else
			$lease->finish_time = $lease->start_time+7200000;

		// 获取ID信息
		$lease->room_id = $post['room_id'];
		if ($lease->room_id == '0') // 选中的是智能分配会议室，调用 Room 的静态变量来获取有空的会议室
			$lease->room_id = Room::getIntelliDistribute($lease->start_time, $lease->finish_time);
		$lease->admin_id = Room::get($lease->room_id)->getData('admin_id');
		$lease->user_id = session('user_id');
		
		// 获取会议数据
		$lease->theme = $post['theme'];
		$lease->usage = $post['usage'];
		$lease->message = $post['message'];

		// 获取会议服务情况
		if (isset($post['sweep']))
			$lease->sweep = true;
		else
			$lease->sweep = false;
		if (isset($post['entertain']))
			$lease->entertain = true;
		else
			$lease->entertain = false;
		if (isset($post['remote']))
			$lease->remote = true;
		else
			$lease->remote = false;

		// 添加到数据库
		$state = $lease->validate(true)->save();
		if ($state) {
			return $this->success('添加成功', url('index'));
		} else {
			return $this->error('添加失败' . $lease->getError(), Request::instance()->header('referer'));
		}
	}

	public function editLease()
	{
		$lease_id = Request::instance()->param('lease_id');
		$lease = Lease::get($lease_id);
		if (is_null($lease))
			return $this->error('未获取到 ID ', url('index'));

		$this->assign("lease", $lease);
		$this->assign('rooms', Room::all());

		$this->assign('start_time', $lease->start_time);
		$this->assign('finish_time', $lease->finish_time);

		return $this->fetch();
	}

	public function updateLease()
	{
		$post = Request::instance()->post();
		$lease_id = $post['lease_id'];
		$lease = Lease::get($lease_id);
		if (!is_null($lease)) {
			
			$lease->room_id = $post['room_id'];
			
			$lease->theme = $post['theme'];
			$lease->usage = $post['usage'];
			$lease->message = $post['message'];

			if (isset($post['sweep']))
				$lease->sweep = true;
			else
				$lease->sweep = false;
			if (isset($post['entertain']))
				$lease->entertain = true;
			else
				$lease->entertain = false;
			if (isset($post['remote']))
				$lease->remote = true;
			else
				$lease->remote = false;

			if (isset($post['start_time']))
				$lease->start_time = strtotime($post['start_time']);
			else
				$lease->start_time = 0;
			if (isset($post['finish_time']))
				$lease->finish_time = strtotime($post['finish_time']);
			else
				$lease->finish_time = 0;

			$state = $lease->validate(true)->save();
			if ($state) {
				return $this->success('更新成功', url('index'));
			} else {
				return $this->error('更新失败' . $lease->getError(), Request::instance()->header('referer'));
			}
		}
	}

	public function deleteLease()
	{
		$lease_id = Request::instance()->param('lease_id/d');
		$lease = Lease::get($lease_id);
		if (is_null($lease))
			return $this->error('未获取到租约ID', Request::instance()->header('referer'));
		
		if (!$lease->delete())
			return $this->error('删除失败' . $lease->getError(), Request::instance()->header('referer'));

		return $this->success('删除订单成功', url('index'));
	}

	/**
	 * 现在立即结束租约（提前结束）
	 * 用来避免用完后没有
	 * @return [type] [description]
	 */
	public function finishLease()
	{
		$lease_id = Request::instance()->param('lease_id');
		$lease = Lease::get($lease_id);
		if (is_null($lease))
			return $this->error('未获取到租约ID', Request::instance()->header('referer'));

		$time = time();
		$lease->finish_time = time();
		$lease->save();

		return $this->success('已经设置本次租约已结束。<br>若需要修改，请在列表中编辑。', url('myLeases'));
	}

	public function join()
	{
		$lease_id = Request::instance()->param('lease_id');
		$user_id = session('user_id');
		$map = ['lease_id'=>$lease_id, 'user_id'=>$user_id];
		$check = Check::get($map);
		if (is_null($check)) // 没有添加
		{
			$check = new Check;
			$check->lease_id = $lease_id;
			$check->user_id = $user_id;
			$check->save();
			return $this->success('参与成功', url('index'));
		}
		else
		{
			return $this->success('您已经参加了，无需重复参与', url('index'));
		}
	}
	
}
