{include file='mobile/header.tpl' }

<form name="express_post" id="express_post" method="post"
	action="/frontend/express/newexpress?handlekey=post">
    <input type="hidden" name="formhash" value="{$formhash}" />
    <input type="hidden" name="eid" value="{$eid}" />
	<div class="ui-top-border"></div>
	<h2 class="kdzs-ui-title">设置代领人</h2>
	<div class="ui-form kdzs-ui-margin-bottom">
			  {cyoa_user_selector 
			   title='代领人' 
			   users=$cc_users 
			   user_input='uid'
			   user_class='ui-form-item ui-form-contacts clearfix _addrbook_list'
			   user_max=1
			   div_class='ui-form-item ui-form-contacts'
			   dp_class='ui-form-item ui-form-contacts clearfix _dpname_list'}
	</div>
	<div class="ui-btn-group-tiled ui-btn-wrap ui-padding-bottom-0 ui-padding-top-0">
	    <button id="cancle" class="ui-btn-lg">取消</button>
	    <button id="ok" class="ui-btn-lg ui-btn-primary" data-myuid="{$myuid}">确定</button>
	</div>
</form>

<script>
{literal}

//弹出对话框
function show_dialog(content){
    var dia=$.dialog({
        title:'温馨提示',
        content:content,
        button:["确认","取消"]
    });

    dia.on("dialog:action",function(e){
        console.log(e.index)
    });
    dia.on("dialog:hide",function(e){
        console.log("dialog hide")
    });
}

require(["zepto", "underscore", "submit", "frozen"], function($, _, submit, fz) {

		//取消
		$('#cancle').on('click', function(e) {
	 		window.history.go(-1);
			return false;
		});
		
		//确定
	    $('#ok').on('click', function(e) {
	 		var myuid = $(this).data('myuid');
	 		var uid = $('#uid').val();
	 		if(uid == 0){
				show_dialog('请选择代领人！');
				return false;
		    }
		    
		    if(myuid == uid){
		    	show_dialog('代领人不能是自己！');
				return false;
		    }
		});

   	    var sbt = new submit();//报告提交
	    sbt.init({"form": $("#express_post")}); 
	
});

{/literal}
</script>

{include file='mobile/footer.tpl'}