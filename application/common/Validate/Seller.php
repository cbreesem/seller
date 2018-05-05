<?php
namespace app/index/validate;
use think/validate;

/**
* 商家信息递交检验类
*/
class Seller extends Validate
{
	protected $rule = [
		['name','require','商家名称不能为空']，
		['mobile','require','商家名称不能为空']，
		['add_mobile','require','商家名称不能为空']，
		['telephone','require','商家名称不能为空']，
		['add_telephone','require','商家名称不能为空']，
	]

}
