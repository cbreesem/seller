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
    }
    
    public function add() {
        $path = config('imgPath');
        $data = input('post.');

		if(empty($data['name'])) unset($data['name']);
		if(empty($data['mobile'])) unset($data['mobile']);
		if(empty($data['telephone'])) unset($data['telephone']);
		if(empty($data['profile'])) unset($data['profile']);
		if(empty($data['keyword'])) unset($data['keyword']);

		$sellerNumbers = array();
		foreach ($data as $key => $value) {
			if($key == 'mobile' || $key == 'telephone' || $key == 'keyword'){
				$value = str_replace('.',',',$value);
				$value = str_replace('。',',',$value);
				$value = str_replace('，',',',$value);
				$value = str_replace('、',',',$value);
				$data[$key] = $value;
			}
			if($key == 'mobile' || $key == 'telephone'){
				if(strpos($value,',') === false){
					array_push($sellerNumbers, $value);
				}else{
					$newArr = explode(',', $value);
					$sellerNumbers = array_merge($sellerNumbers, $newArr);
				}
			}
			if(empty($value)) unset($data[$key]);
		}
		if(array_key_exists('imgpath', $data)){
			$gps = self::getGPS(self::$path.$data['imgpath']);
			if($gps != false) $data['newcoordinate'] = $gps;

			$arrPath = explode('/', $data['imgpath']);
			$where = array(
				'province' => $data['province'],
				'district' => $data['district'],
				'city' => $data['city']
			);
			$number = Db::name('seller')->where($where)->field(['mobile','telephone'])->select();
			$numList = array();
			foreach ($number as $k => $v) {
				foreach ($v as $i) {
					if(empty($i)) continue;
					if(strpos($i,',') === false){
						array_push($numList, $i);
					}else{
						$newArr = explode(',', $i);
						$numList = array_merge($numList, $newArr);
					}
				}
			}
			$fileName = null;
			foreach ($sellerNumbers as $value) {
				if(!in_array($value, $numList)){
					$fileName = $value.'.jpg';
				}
			}
			if($fileName == null) $fileName = $data['name'].'.jpg';
			$arrPath[count($arrPath)-1] = $fileName;
			$temPath = join('/', $arrPath);
			rename($path.$data['imgpath'], $path.$temPath);
			array_splice($arrPath, -1, 0, '已匹配');
			$newPath = join('/', $arrPath);
			rename($path.$temPath, $path.$newPath);
			$data['imgpath'] = $newPath;
		}
		if(array_key_exists('address', $data)){
            $arr = array('路', '道', '街', '巷');
            foreach ($arr as $v) {
                if(strpos($data['address'], $v) !== false){
                    $a = explode($v, $data['address']);
                    $data['road'] = $a[0].$v;
                    break;
                }
            }
		}
		Db::name('seller')->insert($data);
		return $this->redirect('lists',array('district' => mb_substr($data['district'],0,-1).'地区','city' => $data['city']));
    }
}