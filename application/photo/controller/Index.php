<?php
namespace app\photo\controller;

use think\Controller;
use think\Db;

class Index extends Controller {
    public function index() {
        $path = config('imgPath');
        $district = opendir($path);
        while ( $row = readdir($district)) {
            if(is_dir($path.$row) and substr($row,0,1) != '.'){
                echo '<br>';
                print_r($row);
            }
        }
    }
}