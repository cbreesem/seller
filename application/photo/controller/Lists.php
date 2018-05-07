<?php
namespace app\photo\controller;

use think\Controller;
use think\Db;

class Lists extends Controller {
    public function index() {
        $path = config('imgPath');
        $get = input('get.');
        if (!empty($get)) {
            $path = $path.$get['district'].'/'.$get['city'].'/';
            $files = getFilesPath($path,true,'xlsx,xls,db,已录完,已入库');
            foreach ($files as $value) {
                $path = str_replace('/share/CACHEDEV1_DATA/Web', '', $value);
                $path = explode('/', $path);
                $name = array_pop($path);
                $path = join('/', $path);
                $list[] = array(
                    'path' => $path,
                    'name' => $name,
                );
			}
        }
        $page = !empty($_GET['page']) ? intval($_GET['page'])*48 : 0;
		$list = array_slice($list,$page,48);
        $this->assign('list', $list);
        return $this->fetch();
    }
}