<?php
namespace app\seller\controller;
use think\Controller;
use think\Db;
class Index extends Controller {
    public function index() {
        $count = DB::name('seller')->where('district is not Null')->count();
        $list = DB::name('seller')->where('district is not Null')->field('district')->group('district')->select();
        foreach ($list as $key => $value) {
            $list[$key]['count'] = 0;
            $list[$key]['num'] = 0;
            $arr = DB::name('seller')->where('`district`= "'.$value['district'].'"')->field('city')->group('city')->select();
            foreach ($arr as $k => $v) {
                $a = array();
                $a['count'] = DB::name('seller')->where('`city`= "'.$v['city'].'"')->count();
                $a['city'] = $v['city'];
                $list[$key]['count'] += $a['count'];
                $list[$key]['num'] += 1;
                $list[$key]['list'][] = $a;
            }
        }
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
        $this->assign('list', $list);
        return $this->fetch();
    }
}