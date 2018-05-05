var category = function(){
	$select = $("#category > select");
	//alert($select.length);
	for(i=0;i<$select.length;i++) {
		this.ajax[i] = $select.eq(i);
	}
}
category.prototype = {
	/*
		定义ajax成员方法
		参数说明:
			json	Json格式的数据
			index	select下拉框的索引号0表示第一个select
			selectObj	表示第一个select对象
	*/
	ajax : function(json,index,selectObj){
		$.post("/Seller/category/index/getCategoryAjax",json,function(result){
			selectObj.empty();
			selectObj.append("<option value=''>选择区域</option>");
			for(var i in result){
				selectObj.append("<option value=\""+result[i]['id']+"\">"+result[i]['name']+"</option>");
			}
			j=result;

		});	
		if(index=='1'){
			this.cssDisplay(selectObj,"block");
			this.cssDisplay(this.ajax[2],"none");
			this.cssDisplay(this.ajax[3],"none");
		}else if(index=='2'){
			this.cssDisplay(selectObj,"block");
			this.cssDisplay(this.ajax[1],"block");
			this.cssDisplay(this.ajax[3],"none");	
		}else if(index =='3'){
			this.cssDisplay(selectObj,"block");
			this.cssDisplay(this.ajax[1],"block");
			this.cssDisplay(this.ajax[2],"block");
		}
	},

	cssDisplay : function(OperatorObj,status){
		OperatorObj.css("display",status);
	}
}
function getPid(selectObj){
	
	selectObj = $(selectObj);//将表单传过来的javascript对象转变成Jquery对象
	selectIndex = parseInt(selectObj.index()+1);//取得selectObj的索引号
	selectText = selectObj.val();//取得selectObj对值
	// alert(selectIndex);
	$category = new category();
	document.getElementById("pid").value=selectText; 
	if(selectText=="") {
		//alert(selectIndex);
		switch(selectIndex){
			case 1:
				$category.cssDisplay($category.ajax[1],"none");
				$category.cssDisplay($category.ajax[2],"none");
				$category.cssDisplay($category.ajax[3],"none");
			break;
			case 2:
				$category.cssDisplay($category.ajax[2],"none");
				$category.cssDisplay($category.ajax[3],"none");
			break;
			case 3:
				$category.cssDisplay($category.ajax[3],"none");
			break;
			default:
			break;	
		}
		return;
	}else {
		//selectText不为空则显示下一级
		$category.ajax({pid:selectText},selectIndex,$category.ajax[selectIndex]);
	}
}

$(function(){
	$category = new category();
	
	for(i=1;i<4;i++){
		$category.cssDisplay($category.ajax[i],"none");
	}

	/*
		初始化显示所有一级目录，从父ID为0的开始获取
			{fid:0}	传给Jquery框架中的Ajax的$.post()方法的Url地址（Json格式的数据）
			0 select下拉框的索引号0表示第一个select
			$category.ajax[0] 表示第一个select对象
			ajax是对象的成员属性为数组类型，$category.ajax[0]则表示第一个
	*/
	$category.ajax({pid:0},0,$category.ajax[0]);	
})