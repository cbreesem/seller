<?php
namespace app\photo\controller;

use think\Controller;
use think\Db;

class Show extends Controller {
    public function index() {
        $path = config('imgPath');
        $get = input('get.');
        if (!empty($get)) {
            $imgpath = $get['path'].'/'.$get['name'];
            $pathlist = explode('/', $get['path']);
			$info['province'] = '湖南省';
			$info['district'] = mb_substr($pathlist[3],0,-2).'市';
			$info['city'] = $pathlist[4] != $info['district'] ? $pathlist[4] : $pathlist[5];
			$info['name'] = $get['name'];
			$gps = getCoordinate($path.str_replace('/CompanyFiles/商家信息/', '', $imgpath));
            $gps = $gps != false ? explode(',', $gps) : [0,0];
			$this->assign('gps', $gps);
			$this->assign('info', $info);
			$this->assign('imgpath', $imgpath);
			return $this->fetch();
        }
        // $this->assign('list', $list);
        // return $this->fetch();
    }
}