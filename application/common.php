<?php
use think\Db;
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

function key2Word($keyid){
	$where['id'] = $id;
	$data = Db::name('category')->where($where)->find();
	return $data['name'];
}

function getSon($id){//高手改的，可以取得所有子孙的
	$arr_news_sort = '';
	$where['pid'] = $id;
	$list = Db::name('category')->where($where)->select();
	foreach($list as $k =>$v){
		$arr_news_sort .= ','.$list[$k]['id'];
		$tmp = getSon($list[$k]['id']);
		$arr_news_sort .= !empty($tmp) ? $tmp : '' ;
	}
	return $arr_news_sort;
}

function getSubset($id = 'null'){
	$arr = array();
	$where['pid'] = $id != 'null' ? $id : 0;
	$list = Db::name('category')->where($where)->select();
	foreach ($list as $key => $value) {
		$temp = getSubset($value['id']);
		if(!empty($temp)){
			$list[$key]['sub'] = $temp;
		}
	}
	return $list;
}
function showSubset($arr,$select=array()){
	$str = '';
	foreach ($arr as $key => $value) {
		switch ($value['level']){
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
		$active = in_array($value['id'], $select) ? $active = 'active' : '';

		$str .= '<label class="btn '.$class.' '.$active.'" name="industry">
					<input type="checkbox" autocomplete="off" value="'.$value['level'].'.'.$value['name'].'-'.$value['id'].'"> '.$value['name'].'
				 </label>';
		if(array_key_exists('sub', $value)){
			$str .= showSubset($value['sub'],$select);
		}
	}
	return $str;
}
function showSubsetList($arr){
	$str = '';
	foreach ($arr as $key => $value) {
		switch ($value['level']){
			case 1:
			  $btn = 'btn-danger';
			  $label = 'label-danger';
			  break;  
			case 2:
			  $btn = 'btn-warning';
			  $label = 'label-warning';
			  break;
			case 3:
			  $btn = 'btn-success';
			  $label = 'label-success';
			  break;
			case 3:
			  $btn = 'btn-info';
			  $label = 'label-info';
			  break;
			default:
			  $btn = 'btn-default';
			  $label = 'label-default';
		}
		$str .= '<div class="btn '.$btn.'" style="margin:5px;">'.$value['name'].'
					<a class="badge" href="/Seller/category/index/edit?id='.$value['id'].'">改</a>
					<a class="badge" href="/Seller/category/index/add?pid='.$value['id'].'">加</a>
				 </div>';
		if(array_key_exists('sub', $value)){
			$str .= showSubsetList($value['sub']);
		}
	}
	return $str;
}
function getFilesPath($path, $matching=false, $filter=NULL){
	$dir = array();
	$district = opendir($path);
	while ( $row = readdir($district)) {
		if(is_file($path.$row)){
			if ($filter == NULL) {
				$dir[] = $path.$row;
			}else{
				if(strpos($filter,',') === false){
					if(strpos($row,$filter) === false) $dir[] = $path.$row;
				}else{
					$arr = explode(',',$filter);
					$ext = explode('.', $row);
					if(!in_array($ext[count($ext)-1], $arr)) $dir[] = $path.$row;
				}
			}
		}else{
			if($row == '已匹配' && $matching == true) continue;
			if(is_dir($path.$row) and substr($row,0,1) != '.') $dir = array_merge($dir, getFilesPath($path.$row.'/',$matching,$filter));
		}
	}
	return $dir;
}

function getCoordinate($path){
	if(!is_file($path)) return false;
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