<?php
namespace app\index\Controller;
use app\common\model\Admin;
use app\common\model\User;
use app\common\model\Lease;
use app\common\model\Room;
use think\Controller;
use think\Request;

class LeaseController extends Controller
{
	public function __construct()
	{
		parent::__construct();

		if (!Admin::isLogin()) {
			return $this->error("请先登录", url('Login/index'));
		}
	}

	public function index()
	{
		return $this->all();
	}

	public function all()
	{
		$Lease = new Lease;

		$name = Request::instance()->param('name');
		if (!empty($name)) {
			$w = "%$name%";
			$Lease->where("user_id like '$w' or admin_id like '$w' or room_id like '$w' or lease_id like '$w' or theme like '$w' or message like '$w'");
		}

		$leases = $Lease->paginate(5, false, [
				'query' => [ 'name' => $name]
			]);

		$this->assign('leases', $leases);
		return $this->fetch('all');
	}

	public function add()
	{
		$this->assign('leases',  Lease::all());
		$this->assign('admins', Admin::all());
		$this->assign('users', User::all());
		$this->assign('rooms', Room::all());

		$this->assign('start_time', Lease::getSuitableTime());
		$this->assign('finish_time', Lease::getSuitableTime(7200));

		return $this->fetch();
	}

	public function insert()
	{
		$lease = new Lease;
		$post = Request::instance()->post();

		if (isset($post['start_time']))
			$lease->start_time = strtotime($post['start_time']);
		else
			$lease->start_time = 0;
		if (isset($post['end_time']))
			$lease->end_time = strtotime($post['end_time']);
		else
			$lease->end_time = 0;
		if (isset($post['finish_time']))
			$lease->finish_time = strtotime($post['finish_time']);
		else
			$lease->finish_time = $lease->end_time;

		$lease->room_id = $post['room_id'];
		if ($lease->room_id == '0') // 选中的是智能分配会议室，调用 Room 的静态变量来获取有空的会议室
			$lease->room_id = Room::getIntelliDistribute($lease->start_time, $lease->end_time);
		$lease->admin_id = $post['admin_id'];
		if ($lease->admin_id == '0') // 会议室默认管理员
			$lease->admin_id = Room::get($lease->room_id)->getData('admin_id');
		$lease->user_id = $post['user_id'];
		
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

		$state = $lease->validate(true)->save();
		if ($state) {
			return $this->success('添加成功', url('index'));
		} else {
			return $this->error('添加失败' . $lease->getError(), Request::instance()->header('referer'));
		}
	}

	public function edit()
	{
		$lease_id = Request::instance()->param('lease_id');
		$lease = Lease::get($lease_id);
		if (is_null($lease))
			return $this->error('未获取到 ID ', url('index'));

		$this->assign("lease", $lease);
		$this->assign('admins', Admin::all());
		$this->assign('users', User::all());
		$this->assign('rooms', Room::all());

		$this->assign('start_time', $lease->start_time);
		$this->assign('finish_time', $lease->finish_time);

		return $this->fetch();
	}

	public function update()
	{
		$post = Request::instance()->post();
		$lease_id = $post['lease_id'];
		$lease = Lease::get($lease_id);
		if (!is_null($lease)) {
			
			$lease->room_id = $post['room_id'];
			$lease->admin_id = $post['admin_id'];
			$lease->user_id = $post['user_id'];
			
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
				$lease->finish_time = $lease->start_time+7200000;

			$state = $lease->validate(true)->save();
			if ($state) {
				return $this->success('更新成功', url('index'));
			} else {
				return $this->error('更新失败' . $lease->getError(), Request::instance()->header('referer'));
			}
		}
	}

	public function delete()
	{
		$lease_id = Request::instance()->param('lease_id/d');
		$lease = Lease::get($lease_id);

		if (is_null($lease))
			return $this->error('未获取到订单ID', Request::instance()->header('referer'));
		
		if (!$lease->delete())
			return $this->error('删除失败' . $lease->getError(), Request::instance()->header('referer'));

		return $this->success('删除订单成功', url('index'));
	}

	public function MyLeases()
	{
		$leases = new Lease;

		$name = Request::instance()->param('name');
		if (!empty($name)) {
			$w = "%$name%";
			$leases->where(" room_id like '$w' or lease_id like '$w' or theme like '$w' or message like '$w'");
		}

		$admin_id = session('admin_id');
		if (empty($admin_id))
			return $this->error('请先登录', url('Login/index'));

		$leases->where("admin_id = '$admin_id'");
		$leases = $leases->select();
		$this->assign('leases', $leases);
		return $this->fetch('my_leases');
	}

	public function giveScore()
	{
		$lease_id = Request::instance()->param('lease_id');
		$lease = Lease::get($lease_id);
		if (is_null($lease))
			return $this->error('未获取到 ID ', url('myLeases'));
		$this->assign('lease', $lease);
		$score = $lease->getData('admin_score');
		if ($score == '')
			$score = "+100";
		$this->assign('default_score', $score);
		return $this->fetch();
	}

	public function setScore()
	{
		$post = Request::instance()->post();
		$lease_id = $post['lease_id'];
		$lease = Lease::get($lease_id);
		if (is_null($lease))
			return $this->error('未获取到 ID ', url('myLeases'));
		
		$circumstance = $post['circumstance'];
		$admin_score = $post['admin_score'];

		$lease->circumstance = $circumstance;
		$lease->admin_score = $admin_score;

		$state = $lease->validate(true)->save();

		if (!$state)
			return $this->error('评分失败' . $lease->getError(), Request::instance()->header('referer'));
		return $this->success('评分成功', url('myLeases'));
	}
}