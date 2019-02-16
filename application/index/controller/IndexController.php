<?php
namespace app\index\controller;
use think\Controller;
use app\common\model\Admin;

class IndexController extends Controller
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

    }
}