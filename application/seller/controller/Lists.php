<?php
namespace app\seller\controller;
use think\Controller;
use think\Db;
class Lists extends Controller {
    public function index() {
        $ImgUrl = 'http://'.$_SERVER['SERVER_NAME'].'/Seller'.$_SERVER['PATH_INFO'].'?';
        $get = input('get.');
        $where = array();
        $map = array();
        if(!empty($get)){
            foreach ($get as $key => $value) {
                if ($key == 'page') continue;
                $map[$key] = $value;
                if($key == 'name'){
                    $where[$key] = ['like','%'.$get['name'].'%'];
                }elseif($key == 'imgpath'){
                    $where[$key] = $value == 'no' ? array('EXP','IS NULL') : array('EXP','IS NOT NULL') ;
                }else{
                    $where[$key] = $value;
                }
                if($key != 'imgpath') $ImgUrl .= $key.'='.$value.'&';
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

        return $this->fetch();

    }
}