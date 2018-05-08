<?php
namespace app\seller\controller;
use think\Controller;
use think\Db;
class Show extends Controller {
    public function index() {
        $get = input('get.');
        if(!empty($get)){
			$info = DB::name('seller')->where($get)->find();
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

    public function edit() {
        $path = config('imgPath');
        $post = input('post.');
        if(!empty($post)){
            $where['id'] = intval($post['id']);

            // 获取数据库原有资料
			$info = Db::name('seller')->where($where)->find();

            // 递交数据与原有数据比对，不同则增加
			if($info['name'] != $post['name']) $data['name'] = $post['name'];
			if($info['profile'] != $post['profile']) $data['profile'] = $post['profile'];
			if($info['industry'] != $post['industry']) $data['industry'] = $post['industry'];
            if($info['linkman'] != $post['linkman']) $data['linkman'] = $post['linkman'];
            if($info['address'] != $post['address']) $data['address'] = $post['address'];

			if(isset($post['structure']) && $info['structure'] != $post['structure']) $data['structure'] = $post['structure'];
			if(isset($post['mode']) && $info['mode'] != $post['mode']) $data['mode'] = $post['mode'];
			if(isset($post['type']) && $info['type'] != $post['type']) $data['type'] = $post['type'];
            
            // 图片文件的移动更名及比对
			if($info['imgpath'] != $post['imgpath']){
                // 如果递交数据不等于原始数据
				if(strlen($post['imgpath']) > 0){
					// 递交数据不为空
					if(is_file($path.$info['imgpath'])){
                        // 原始数据中图片路径文件存在
                        $newpath = str_replace('/已匹配', '', $path.$post['imgpath']);
						if(is_file($newpath)){
                            // 如果新路径图片存在
                            if (!is_file($path.$post['imgpath'])) {
                                // 没有此文件名的文件，直接把它移动到已匹配文件夹中并且获取它的经纬度信息
                                rename($newpath, $path.$post['imgpath']);
                                $data['imgpath'] = $post['imgpath'];
                                $gps = getCoordinate($path.$post['imgpath']);
                                if($gps != false) $data['newcoordinate'] = $gps;
                                rename($path.$info['imgpath'], str_replace('/已匹配', '', $path.$info['imgpath']));
                                // 将原始数据中图片路径中的文件移出已匹配文件夹
                            }                            
						}else{
                            // 如果递交数据中文件不存在，则把原始数据中的文件改名，此功能用于更改文件名
							rename($path.$info['imgpath'], $path.$post['imgpath']);
							$data['imgpath'] = $post['imgpath'];
							$gps = getCoordinate($path.$post['imgpath']);
							if($gps != false) $data['newcoordinate'] = $gps;
						}
					}else{
						$newpath = str_replace('/已匹配', '', $path.$post['imgpath']);
						if(is_file($newpath)){
                            // 如果新路径图片存在
                            if (!is_file($path.$post['imgpath'])) {
                                // 没有此文件名的文件，直接把它移动到已匹配文件夹中并且获取它的经纬度信息
                                rename($newpath, $path.$post['imgpath']);
                                $data['imgpath'] = $post['imgpath'];
                                $gps = getCoordinate($path.$post['imgpath']);
                                if($gps != false) $data['newcoordinate'] = $gps;
                                rename($path.$info['imgpath'], str_replace('/已匹配', '', $path.$info['imgpath']));
                                // 将原始数据中图片路径中的文件移出已匹配文件夹
                            }                            
						}
					}
				}else{
                    // 如果递交数据中图片路径为空，那么原有数据中图片路径中文件存在则把图片移出已匹配文件夹，清空数据库中的图片路径和经纬度信息
					if(is_file($path.$info['imgpath'])) rename($path.$info['imgpath'],str_replace('/已匹配', '', $path.$info['imgpath']));
					$data['imgpath'] = null;
					$data['newcoordinate'] = null;
				}
			}
            
            $arr = array('，', '、',' ');
            foreach ($arr as $value) {
                $post['add_keyword'] = str_replace($value, ',', $post['add_keyword']);
                $post['keyword'] = str_replace($value, ',', $post['keyword']);
                $post['add_telephone'] = str_replace($value, ',', $post['add_telephone']);
                $post['telephone'] = str_replace($value, ',', $post['telephone']);
                $post['add_mobile'] = str_replace($value, ',', $post['add_mobile']);
                $post['mobile'] = str_replace($value, ',', $post['mobile']);
            }
            if ($post['add_keyword'] != '' && $post['keyword'] != '') {
                $data['keyword'] = $post['add_keyword'].','.$post['keyword'];
            } else {
                $data['keyword'] = $post['add_keyword'] != '' ? $post['add_keyword'] : $post['keyword'] != '' ? $post['keyword'] : '';
            }

            if ($post['add_telephone'] != '' && $post['telephone'] != '') {
                $data['telephone'] = $post['add_telephone'].','.$post['telephone'];
            } else {
                $data['telephone'] = $post['add_telephone'] != '' ? $post['add_telephone'] : $post['telephone'] != '' ? $post['telephone'] : '';
            }

            if ($post['add_mobile'] != '' && $post['mobile'] != '') {
                $data['mobile'] = $post['add_mobile'].','.$post['mobile'];
            } else {
                $data['mobile'] = $post['add_mobile'] != '' ? $post['add_mobile'] : $post['mobile'] != '' ? $post['mobile'] : '';
            }

			if($data['keyword'] == ',') $data['keyword'] = '';
			if($info['telephone'] == ',') $data['telephone'] = '';
			if($info['mobile'] == ',') $data['mobile'] = '';

			if(isset($data)) Db::name('seller')->where($where)->update($data);
			$this->redirect('/Seller/seller/show?id='.$where['id']);

		}else{
			$this->redirect('index');
		}
    }
}