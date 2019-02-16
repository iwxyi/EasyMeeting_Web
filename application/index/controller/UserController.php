<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use app\common\model\User;
use app\common\model\Admin;

class UserController extends Controller
{
	public function __construct()
	{
		// 调用父类的构造函数
		parent::__construct();

		// 验证用户是否登录
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
		$User = new User;

		$name = Request::instance()->get('name');
		if (!empty($name)) {
			$w = "%$name%";
			$User->where("nickname like '$w' or username like '$w' or password like '$w' or user_id like '$w'");
		}

		// 获取数据并调用分页
		$users = $User->paginate(5, false, [
			'query' => [ 'name' => $name]
		]);

		$this->assign('users', $users);
		return $this->fetch('all');
		
	}

	public function add()
	{
		return $this->fetch();
	}

	public function insert()
	{
		// 接收传入数据
		$post = Request::instance()->post();

		$User = new User;
		$User->username = $post['username'];
		$User->password = $post['password'];
		$User->nickname = $post['nickname'];

		if ($User->validate(true)->save() == true)
			return $this->success('添加成功' . $User->username . '成功，ID为：' . $User->user_id, url('all'));
		else
			return $this->error('添加失败' . $User->getError(), url('all'));
	}

	public function delete()
	{
		// 获取关键字。不能用 ->get()，因为使用了助手函数，URL不是get
		$user_id = Request::instance()->param('user_id/d'); // '/d' 转换成整数型

		// 获取要删除的对象
		$User = User::get($user_id);

		// 要删除的对象不存在
		if (is_null($User))
			return $this->error('不存在 id 为：' . $user_id . '的用户，删除失败', Request::instance()->header('referer'));

		// 删除对象
		if (!$User->delete())
			return $this->error('删除失败' . $User->getError(), Request::instance()->header('referer'));

		// 进行跳转
		return $this->success('删除' . $User->username . '(' . $User->nickname . ')' . '成功', Request::instance()->header('referer')); // 返回到上一个网址
	}

	public function edit()
	{
		$user_id = Request::instance()->param('user_id/d');

		if (is_null($user_id) || $user_id === 0)
			return $this->error('未获取到 ID 信息', Request::instance()->header('referer'));

		// 从数据表模型中获取记录
		if (is_null($User = User::get($user_id)))
			$this->error('不存在 id 为：' . $user_id . '的管理员，无法编辑');

		// 将数据传到V层
		$this->assign('User', $User);

		return $this->fetch();
	}

	public function update()
	{
		// 接收数据关键字
		$user_id = Request::instance()->post('user_id/d');

		// 获取当前对象
		$User = User::get($user_id);

		if (!is_null($User)) {
			// 写入要更新的数据
			$User->username = input('post.username');
			$User->password = input('post.password');
			$User->nickname = input('post.nickname');

			// 更新
			if ($User->validate(true)->save() === false) {
				return $this->error('更新失败' . $User->getError(), Request::instance()->header('referer'));
			}
		} else {
			return $this->error('要更新的记录不存在', Request::instance()->header('referer'));
		}
		return $this->success('更新成功', url('all'));
	}

}