<?php
namespace app\index\controller;

use think\Controller;
use think\Db;

class Index extends Controller {
	public static $path = '/share/CACHEDEV1_DATA/Web/CompanyFiles/商家信息/';

	public function index() {

		$district = opendir(self::$path);
		$dirList = array();
		while ( $row = readdir($district)) {
			if(is_dir(self::$path.$row) and strpos($row,'.') === false){
				$city = opendir(self::$path.$row.'/');
				while ( $r = readdir($city)) {
					if(is_dir(self::$path.$row.'/'.$r) and strpos($r,'.') === false){
						if(mb_substr($row,0,-2).'市' == $r){
							$area = opendir(self::$path.$row.'/'.$r.'/');
							while ( $a = readdir($area)) {
								if(is_dir(self::$path.$row.'/'.$r.'/'.$a) and strpos($a,'.') === false){
									if($a != '原始照片') $dirList[$row][] = $a;
								}
							}
						}else{
							$dirList[$row][] = $r;
						}
					}
				}
			}
		}
		$this->assign('area', $dirList);
		return $this->fetch();
	}
	public function menu(){
		$get = input('get.');
		$post = input('post.');
		$ImgUrl = 'http://'.$_SERVER['SERVER_NAME'].'/Seller'.$_SERVER['PATH_INFO'].'?';
		if(!empty($get)){
			foreach ($get as $key => $value) {
				if($key != 'page'){
					$map[$key] = $value;
					if($key == 'district' and  strpos($value,'市') === false){
						$where[$key] = mb_substr($value,0,-2).'市';
					}elseif($key == 'name'){
						$where[$key] = ['like','%'.$get['name'].'%'];
					}elseif($key == 'imgpath'){
						$where[$key] = $value == 'no' ? array('EXP','IS NULL') : array('EXP','IS NOT NULL') ;
					}else{
						$where[$key] = $value;
					}
					if($key != 'imgpath'){
						$ImgUrl .= $key.'='.$value.'&';
					}
				}
			}
			$yesImgUrl = $ImgUrl.'imgpath=yes';
			$noImgUrl = $ImgUrl.'imgpath=no';
			$list = DB::name('seller')->where($where)->order('id ASC')->paginate(20)->appends($map);
			$page = $list->render();
			$this->assign('list', $list);
			$this->assign('page', $page);
			$this->assign('noImgUrl', $noImgUrl);
			$this->assign('yesImgUrl', $yesImgUrl);
			// print_r($_SERVER);
			return $this->fetch();
		}elseif(!empty($post)){
			foreach ($post as $key => $value) {
				if($key != 'imgpath'){
					$ImgUrl .= $key.'='.$value.'&';
				}
			}
			$yesImgUrl = $ImgUrl.'imgpath=yes';
			$noImgUrl = $ImgUrl.'imgpath=no';
			$list = DB::name('seller')->where('name','LIKE','%'.$post['name'].'%')->order('id ASC')->paginate(20)->appends($post);
			$page = $list->render();
			$this->assign('list', $list);
			$this->assign('page', $page);
			$this->assign('noImgUrl', $noImgUrl);
			$this->assign('yesImgUrl', $yesImgUrl);
			return $this->fetch();
		}else{
			$this->redirect('index');
		}
	}
	public function show(){
		if(!empty($_GET['id'])){
			$where['id'] = intval($_GET['id']);
			$info = DB::name('seller')->where($where)->find();
			if($info['industry'] != ''){
				$temp = explode(',', $info['industry']);
				$info['keyid'] = '';
				foreach ($temp as $value) {
					$key = explode('-', $value);
					if(count($key) > 1) $info['keyid'] .= $key[1].',';
				}
			}else{
				$info['keyid'] = '';
			}
			$gps = !empty($info['newcoordinate']) ? $info['newcoordinate'] : $info['coordinate'];
			$info['coordinate'] = !empty($gps) ? explode(',', $gps) : [0,0];
			$result = Db::name('seller')->where('industry is not NULL')->field('industry')->select();
			$industry = array();
			foreach ($result as $key => $value) {
				$arr = explode(',', $value['industry']);
				foreach ($arr as $v) {
					if(strlen($v) > 0) array_push($industry,$v);
				}
			}
			$industry = array_count_values($industry);
			arsort($industry);
			$str = '';
			$n = 0;
			foreach ($industry as $key => $value) {
				if($n > 40) break;
				$level = explode('.', $key);
				$name = explode('-', $level['1']);
				switch ($level['0']){
					case 1:
					  $class = 'btn-danger';
					  break;
					case 2:
					  $class = 'btn-warning';
					  break;
					case 3:
					  $class = 'btn-success';
					  break;
					case 3:
					  $class = 'btn-info';
					  break;
					default:
					  $class = 'btn-default';
				}
				$str .= '<label class="btn '.$class.'" name="industry">
							<input type="checkbox" autocomplete="off" value="'.$key.'"> '.$name[0].'
						 </label>';
				$n++;
			}
			$this->assign('string', $str);
			$this->assign('info', $info);
			// print_r($info);
			return $this->fetch();
		}else{
			$this->redirect('index');
		}

	}
	public function edit(){
		if(!empty($_POST['id'])){
			$where['id'] = intval($_POST['id']);
			$info = Db::name('seller')->where($where)->find();

			if($info['name'] != $_POST['name']) $data['name'] = $_POST['name'];
			if($info['profile'] != $_POST['profile']) $data['profile'] = $_POST['profile'];
			if($info['industry'] != $_POST['industry']) $data['industry'] = $_POST['industry'];
			if($info['linkman'] != $_POST['linkman']) $data['linkman'] = $_POST['linkman'];
			if($info['imgpath'] != $_POST['imgpath']){
				if(strlen($_POST['imgpath']) > 0){
					// echo $_POST['imgpath'];
					if(is_file(self::$path.$info['imgpath'])){
						$newpath = str_replace('/已匹配', '', self::$path.$_POST['imgpath']);
						if(is_file($newpath)){
							rename($newpath, self::$path.$_POST['imgpath']);
							$data['imgpath'] = $_POST['imgpath'];
							$gps = self::getGPS(self::$path.$_POST['imgpath']);
							if($gps != false) $data['newcoordinate'] = $gps;
							rename(self::$path.$info['imgpath'], str_replace('/已匹配', '', self::$path.$info['imgpath']));
						}else{
							rename(self::$path.$info['imgpath'],self::$path.$_POST['imgpath']);
							$data['imgpath'] = $_POST['imgpath'];
							$gps = self::getGPS(self::$path.$_POST['imgpath']);
							if($gps != false) $data['newcoordinate'] = $gps;
						}
					}else{
						$newpath = str_replace('/已匹配', '', self::$path.$_POST['imgpath']);
						if(is_file($newpath)){
							rename($newpath, self::$path.$_POST['imgpath']);
							$data['imgpath'] = $_POST['imgpath'];
							$gps = self::getGPS(self::$path.$_POST['imgpath']);
							if($gps != false) $data['newcoordinate'] = $gps;
						}
					}
				}else{
					if(is_file(self::$path.$info['imgpath'])) rename(self::$path.$info['imgpath'],str_replace('/已匹配', '', self::$path.$info['imgpath']));
					$data['imgpath'] = null;
					$data['newcoordinate'] = null;
				}
			}
			if($info['address'] != $_POST['address']) $data['address'] = $_POST['address'];
			if(isset($_POST['structure']) && $info['structure'] != $_POST['structure']) $data['structure'] = $_POST['structure'];
			if(isset($_POST['mode']) && $info['mode'] != $_POST['mode']) $data['mode'] = $_POST['mode'];
			if(isset($_POST['type']) && $info['type'] != $_POST['type']) $data['type'] = $_POST['type'];

			$aw = $_POST['add_keyword'] != '' ? str_replace('、',',',str_replace('，',',',$_POST['add_keyword'])) : '';
			$w = $info['keyword'] != $_POST['keyword'] ? str_replace('、',',',str_replace('，',',',$_POST['keyword'])) : $info['keyword'];

			if($aw == ''){
				if($w == ''){
					$data['keyword'] = '';
				}else{
					$data['keyword'] = $w;
				}
			}else{
				if($w == ''){
					$data['keyword'] = $aw;
				}else{
					$data['keyword'] = $w.','.$aw;
				}
			}

			$at = $_POST['add_telephone'] != '' ? str_replace('、',',',str_replace('，',',',$_POST['add_telephone'])) : '';
			$t = $info['telephone'] != $_POST['telephone'] ? str_replace('、',',',str_replace('，',',',$_POST['telephone'])) : $info['telephone'];


			if($at == ''){
				if($t == ''){
					$data['telephone'] = '';
				}else{
					$data['telephone'] = $t;
				}
			}else{
				if($t == ''){
					$data['telephone'] = $at;
				}else{
					$data['telephone'] = $t.','.$at;
				}
			}

			$am = $_POST['add_mobile'] != '' ? str_replace('、',',',str_replace('，',',',$_POST['add_mobile'])) : '';
			$m = $info['mobile'] != $_POST['mobile'] ? str_replace('、',',',str_replace('，',',',$_POST['mobile'])) : $info['mobile'];

			if($am == ''){
				if($m == ''){
					$data['mobile'] = '';
				}else{
					$data['mobile'] = $m;
				}
			}else{
				if($m == ''){
					$data['mobile'] = $am;
				}else{
					$data['mobile'] = $m.','.$am;
				}
			}

			if($data['keyword'] == ',') $data['keyword'] = '';
			if($info['telephone'] == ',') $data['telephone'] = '';
			if($info['mobile'] == ',') $data['mobile'] = '';

			// print_r($data);
			if(isset($data)) Db::name('seller')->where($where)->update($data);
			$this->redirect('/Seller/index/index/show?id='.$where['id']);

		}else{
			$this->redirect('index');
		}

	}
	public function category(){
		$db = DB::name('category');

		$where['pid'] = 0;
		$select = isset($_GET['keyid']) ? explode(',', $_GET['keyid']) : array();
		$level = DB::name('category')->order('level DESC')->find();
		$list = getSubset();
		$string = showSubset($list, $select);
		$this->assign('string', $string);
		return $this->fetch();
	}
	public function imgList(){
		if(!empty($_GET['province']) && !empty($_GET['district']) && !empty($_GET['city'])){
			$city = strpos($_GET['city'],'区') !== false ? mb_substr($_GET['district'],0,-2).'市/'.$_GET['city'].'/原始照片/' : $_GET['city'].'/原始照片/';
			$path = self::$path.$_GET['district'].'/'.$city;
			$files = getFilesPath($path);
			$imgs = array();
			foreach ($files as $value) {
				if(strpos($value,'已匹配') === false && strpos($value,'Thumbs') === false){
					$path = str_replace('/share/CACHEDEV1_DATA/Web', '', $value);
					$path = explode('/', $path);
					$name = array_pop($path);
					$path = join('/', $path);
					$imgs[] = array(
						'path' => $path,
						'name' => $name,
					);
				}
			}
			// $page = !empty($_GET['page']) ? intval($_GET['page'])*48 : 0;
			// $imgs = array_slice($imgs,$page,48);
			$this->assign('imgs', $imgs);
			return $this->fetch();
		}else{
			$this->redirect('index');
		}
	}
	public function imgShow(){
		if(!empty($_GET['path']) && !empty($_GET['name'])){
			$pathlist = explode('/', $_GET['path']);
			$info['province'] = '湖南省';
			$info['district'] = mb_substr($pathlist[3],0,-2).'市';
			$info['city'] = $pathlist[4] != $info['district'] ? $pathlist[4] : $pathlist[5];
			$name = $_GET['name'];
			$imgpath = $_GET['path'].'/'.$_GET['name'];
			$gps = self::getGPS(self::$path.str_replace('/CompanyFiles/商家信息/', '', $imgpath));

			$gps = $gps != false ? explode(',', $gps) : [0,0];
			// print_r($gps);
			// return $this->fetch('User-edit');
			$this->assign('name', $name);
			$this->assign('gps', $gps);
			$this->assign('info', $info);
			$this->assign('imgpath', $imgpath);
			return $this->fetch();
		}else{
			$this->redirect('index');
		}
	}
	public function upname(){
		// print_r($_POST);
		if(request()->isPost()){
			$path = '/share/CACHEDEV1_DATA/Web/'.$_POST['path'].'/'.$_POST['oldname'];
			$newpath = '/share/CACHEDEV1_DATA/Web/'.$_POST['path'].'/'.$_POST['name'];
			if($_POST['name'] != $_POST['oldname'] && is_file($path)){
				rename($path,$newpath);
				echo 'success';
			}
		}
		// echo '<script> history.back(-1); </script>';
	}
	public function imgPost(){
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
			rename(self::$path.$data['imgpath'],self::$path.$temPath);
			array_splice($arrPath, -1, 0, '已匹配');
			$newPath = join('/', $arrPath);
			rename(self::$path.$temPath,self::$path.$newPath);
			$data['imgpath'] = $newPath;
		}
		if(array_key_exists('address', $data)){
			if(strpos($data['address'],'路') !== false){
				$arr = explode('路', $data['address']);
				$data['road'] = $arr[0].'路';
			}
			if(strpos($data['address'],'道') !== false){
				$arr = explode('道', $data['address']);
				$data['road'] = $arr[0].'道';
			}
			if(strpos($data['address'],'街') !== false){
				$arr = explode('街', $data['address']);
				$data['road'] = $arr[0].'街';
			}
			if(strpos($data['address'],'巷') !== false){
				$arr = explode('巷', $data['address']);
				$data['road'] = $arr[0].'巷';
			}
		}
		Db::name('seller')->insert($data);
		// print_r($data);
		return $this->redirect('imgList',array('province' => $data['province'],
				'district' => mb_substr($data['district'],0,-1).'地区',
				'city' => $data['city']));
	}
	public function getGPS($path){

		$fileinfo = exif_read_data ($path);
		if(array_key_exists('GPSLatitude',$fileinfo) && array_key_exists('GPSLongitude',$fileinfo)){
			$hour = explode('/', $fileinfo['GPSLatitude'][0]);
			$minute = explode('/', $fileinfo['GPSLatitude'][1]);
			$second = explode('/', $fileinfo['GPSLatitude'][2]);
			$GPSLatitude = $hour[0]/$hour[1] + $minute[0]/$minute[1]/60 + $second[0]/$second[1]/3600;

			$hour = explode('/', $fileinfo['GPSLongitude'][0]);
			$minute = explode('/', $fileinfo['GPSLongitude'][1]);
			$second = explode('/', $fileinfo['GPSLongitude'][2]);
			$GPSLongitude = $hour[0]/$hour[1] + $minute[0]/$minute[1]/60 + $second[0]/$second[1]/3600;

			$data = $GPSLongitude.','.$GPSLatitude;
			return $data;
		}else{
			return false;
		}
	}
}