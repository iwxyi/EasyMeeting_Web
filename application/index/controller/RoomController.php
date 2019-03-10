<?php
namespace app\index\controller;
use app\common\model\Room;
use app\common\model\Admin;
use think\Controller;
use think\Request;

class RoomController extends Controller
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
		$Room = new Room;

		$name = Request::instance()->param('name');
		if (!empty($name)) {
			$w = "%$name%";
			$Room->where("name like '$w' or num like '$w' or room_id like '$w'");
		}

		$rooms = $Room->paginate(5, false, [
				'query' => [ 'name' => $name]
			]);

		$this->assign('rooms', $rooms);
		return $this->fetch('all');
	}

	public function add()
	{
		$this->assign('Admins',  Admin::all());
		return $this->fetch();
	}

	public function insert()
	{
		$room = new Room;

		$post = Request::instance()->post();
		$room->admin_id = $post['admin_id'];
		$room->name = $post['name'];
		if (isset($post['max']))
			$room->max = $post['max'];
		/*$room->building = $post['building'];
		$room->floor = $post['floor '];
		$room->num = $post['num'];
		$room->using = $post['using'];
		$room->maintaining = $post['maintaining'];*/

		$state = $room->validate(true)->save();
		if ($state) {
			return $this->success('添加成功', url('index'));
		} else {
			return $this->error('添加失败' . $room->getError(), Request::instance()->header('referer'));
		}
	}

	public function edit()
	{
		$room_id = Request::instance()->param('room_id');
		$Room = Room::get($room_id);
		if (is_null($Room))
			return $this->error('未获取到 ID ', url('index'));
		$this->assign("Room", $Room);
		return $this->fetch();
	}

	public function update()
	{
		$post = Request::instance()->post();
		$room_id = $post['room_id'];
		$room = Room::get($room_id);
		if (!is_null($room)) {
			$room->admin_id = $post['admin_id'];
			$room->name = $post['name'];
			if (isset($post['max']))
				$room->max = $post['max'];
			/*$room->building = $post['building'];
			$room->floor = $post['floor '];
			$room->num = $post['num'];
			$room->using = $post['using'];
			$room->maintaining = $post['maintaining'];*/

			$state = $room->validate(true)->save();
			if ($state) {
				return $this->success('更新成功', url('index'));
			} else {
				return $this->error('更新失败' . $room->getError(), Request::instance()->header('referer'));
			}
		}
	}

	public function delete()
	{
		$room_id = Request::instance()->param('room_id/d');
		$room = Room::get($room_id);

		if ($room_id == 1)
		{
			return $this->error('1号房间不允许删除', Request::instance()->header('referer'));
		}

		if (is_null($room))
			return $this->error('未获取到会议室ID', Request::instance()->header('referer'));
		
		if (!$room->delete())
			return $this->error('删除失败' . $room->getError(), Request::instance()->header('referer'));

		$room->execute("update lease set room_id = 1 where room_id = '$room_id'");

		return $this->success('删除会议室：' . $room->getData('name') . ' 成功', url('index'));
	}
}