<?php
namespace app\index\controller;

use think\Controller;
use think\Db;

class Home extends Controller {
    public function index() {
        return $this->fetch();
    }
    
}