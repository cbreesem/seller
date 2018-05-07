<?php
namespace app\photo\controller;

use think\Controller;
use think\Db;

class Index extends Controller {
    public function index() {
        $path = config('imgPath');
        $district = opendir($path);
        $list = array();
        while ($row = readdir($district)) {
            if(is_dir($path.$row) and substr($row,0,1) != '.'){
                $array = array();
                $array['district'] = $row;
                $city = opendir($path.$row.'/');
                $count = 0;
                $num = 0;
                while ($r = readdir($city)) {
                    if(is_dir($path.$row.'/'.$r) and substr($r,0,1) != '.'){
                        $arr = array();
                        $arr['city'] = $r;
                        $files = getFilesPath($path.$row.'/'.$r.'/',true,'xls,db,已录完,已入库');
                        $arr['count'] = count($files);
                        $array['list'][] = $arr;
                        $count += count($files);
                        $num += 1;
                    }
                }
                $array['num'] = $num;
                $array['count'] = $count;
                $list[] = $array;
            }
        }
        // print_r($list);
        $count = count($list);
        $temp = 0; 
        // 外层控制排序轮次
        for($i=0; $i<$count-1; $i++){
            // 内层控制每轮比较次数
            for($j=0; $j< $count-1-$i; $j++){
                if($list[$j]['num'] > $list[$j+1]['num']){
                    $temp = $list[$j];
                    $list[$j] = $list[$j+1];
                    $list[$j+1] = $temp;
                }
            }
        } 
        // print_r($list);
        // print_r(count($list));
        $this->assign('list', $list);
        return $this->fetch();
    }
}