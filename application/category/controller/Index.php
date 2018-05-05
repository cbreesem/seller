<?php
namespace app\category\controller;

use think\Controller;
use think\Db;

class Index extends Controller {
	public function index() {
		$db = DB::name('category');

		$where['pid'] = 0;
		$list = getSubset();
		$string = showSubsetList($list);
		$this->assign('string', $string);
		return $this->fetch();
	}
	public function edit(){
		if(!empty($_POST)){
			$where['id'] = $_POST['pid'];
			$p = Db::name('category')->where($where)->find();
			$data['pid'] = $_POST['pid'];
			$data['name'] = $_POST['name'];
			$data['order'] = $_POST['order'];
			$data['level'] = $p['level']+1;
			$where['id'] = $_POST['id'];
			Db::name('category')->where($where)->update($data);
			$this->redirect('index');
		}else{
			if(!empty($_GET['id'])){
				$where['id'] = intval($_GET['id']);
				$data = Db::name('category')->where($where)->find();
				$this->assign('data', $data);
				return $this->fetch();
			}else{
				$this->redirect('index');
			}
		}
	}
	public function add(){
		if(!empty($_POST)){
			$where['id'] = $_POST['pid'];
			$p = Db::name('category')->where($where)->find();
			$data['pid'] = $_POST['pid'];
			$data['name'] = $_POST['name'];
			$data['order'] = $_POST['order'];
			$data['level'] = $_POST['pid'] > 0 ? $p['level']+1 : 1;
			Db::name('category')->insert($data);
			$this->redirect('index');
		}else{
			if(!empty($_GET['pid'])){
				$where['id'] = $_GET['pid'];
				$data = Db::name('category')->where($where)->find();
				$this->assign('data', $data);
				return $this->fetch();
			}else{
				$this->redirect('index');
			}
		}
	}
	public function getCategoryAjax(){
		$where['pid'] = isset($_POST['pid']) ? $_POST['pid'] : 0;
		$list = Db::name('category')->where($where)->select();
		return json($list);
	}
}