<?php
namespace app\index\controller;
use think\Controller;
use think\Request;
use think\Model;
use app\common\model\Admin;

class AdminController extends Controller
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
		try {
			$name = Request::instance()->get('name');

			// 使用 Model
			$Admin = new Admin;

			// 获取所有数据
			// $admins = $Admin->select();

			if (!empty($name)) {
				$w = "%$name%";
				$Admin->where("nickname like '$w' or username like '$w' or password like '$w' or admin_id like '$w'");
			}

			// 获取数据并调用分页
			// 分页第三个额外参数，利用它来保持查找的内容
			$admins = $Admin->paginate(5, false, [
				'query' => [ 'name' => $name]
			]);

			// 传递数据到 V 层
			$this->assign('admins', $admins);

			// 显示 V 层
			return $this->fetch('all');
		} catch (\Exception $e) {
			// 什么时候会报错？比如V层的getData变成getDate……
			// throw $e;
			return $this->error('系统错误' . $e->getMessage(), url('all'));
		}
		
	}

	public function add()
	{
		try {
			return $this->fetch();
		} catch (\Exception $e) {
			return $this->error('系统错误' . $e->getMessage(), url('all'));
		}
	}

	public function insert()
	{
		$message = "";
		try {
			// 接收传入数据
			$post = Request::instance()->post();

			// 实例化 Admin 空对象
			$Admin = new Admin;
			// 为对象赋值
			$Admin->username = $post['username'];
			$Admin->password = $post['password'];
			$Admin->nickname = $post['nickname'];
			$Admin->permission = $post['permission'];

			// 验证并新增对象至数据表
			$state = $Admin->validate(true)->save();

			// 反馈结果
			if ($state == true)
				$message = '添加' . $Admin->username . '成功，ID为：' . $Admin->admin_id;
			else
				return $this->error('添加失败' . $Admin->getError(), url('all'));
		} catch (\Exception $e) {
			return $e->getMessage();
		}

		return $this->success($message, url('all'));
	}

	public function delete()
	{
		try {
			// 获取关键字。不能用 ->get()，因为使用了助手函数，URL不是get
			$admin_id = Request::instance()->param('admin_id/d'); // '/d' 转换成整数型

			if ($admin_id == 1) {
				throw new \Exception("不能删除主管理员", 1);
			}

			// 获取要删除的对象
			$Admin = Admin::get($admin_id);

			// 要删除的对象不存在
			if (is_null($Admin))
				throw new \Exception('不存在 id 为：' . $admin_id . '的管理员，删除失败', 1); // 输出异常

			// 删除对象
			if (!$Admin->delete())
				throw new \Exception('删除失败' . $Admin->getError(), 1);

			// 修改外键关联的记录
			$Admin->execute("update lease set admin_id = 1 where admin_id = '$admin_id'");
			$Admin->execute("update room set admin_id = 1 where admin_id = '$admin_id'");

			// 进行跳转
			return $this->success('删除' . $Admin->username . '(' . $Admin->nickname . ')' . '成功', Request::instance()->header('referer')); // 返回到上一个网址
		} catch (\think\Exception\HttpResponseException $e) {
			// 捕获到 TP 内置异常时，直接向上抛出，交给 TP 处理
			// 其实就是抵消 success 的异常……
			throw $e;
		} catch (\Exception $e) {
			return$e->getMessage();
		}
	}

	public function edit()
	{
		try {
			// 获取传入的 id
			$admin_id = Request::instance()->param('admin_id/d');

			if (is_null($admin_id) || $admin_id === 0)
			{
				throw new \Exception('未获取到 ID 信息', 1);
			}

			// 从数据表模型中获取记录
			if (is_null($Admin = Admin::get($admin_id)))
				$this->error('不存在 id 为：' . $admin_id . '的管理员，无法编辑');

			// 将数据传到V层
			$this->assign('Admin', $Admin);

			return $this->fetch();
		} catch (\think\Exception\HttpResponseException $e) {
			throw $e;
		} catch (\Exception $e) {
			return$e->getMessage();
		}
	}

	public function update()
	{
		// 直接把接收的数据存入数据表（方法一）
		// $admin = Request::instance()->post();
		// $Admin = new Admin;
		// $state = $Admin->validate(true)->isUpdate(true)->save($admin);

		try {
			// 接收数据关键字
			$admin_id = Request::instance()->post('admin_id/d');

			// 获取当前对象
			$Admin = Admin::get($admin_id);

			if (!is_null($Admin)) {
				// 写入要更新的数据
				$Admin->username = input('post.username');
				$Admin->password = input('post.password');
				$Admin->nickname = input('post.nickname');
				$Admin->permission = input('post.permission');

				// 更新
				if ($Admin->validate(true)->save() === false) {
					return $this->error('更新失败' . $Admin->getError());
				}
			} else {
				throw new \Exception('要更新的记录不存在', 1);
			}
		} catch (\think\Exception\HttpResponseException $e) {
			throw $e;
		} catch (\Exception $e) {
			return$e->getMessage();
		}
		
		return $this->success('更新成功', url('all'));
	}

}