{include file='mobile/header.tpl'}

{if empty($setting)}
    <div class="ui-notice ui-notice-norecord kdzs-ui-noset"><i></i>
        <h2>{$e_title}</h2>
        <p>{$error}</p>
    </div>
{else}
<form name="express_post" id="express_post" method="post"
	action="/frontend/express/new?handlekey=post">
   <input type="hidden" name="formhash" value="{$formhash}" />
	<div class="ui-top-border"></div>
	  <h2 class="kdzs-ui-title">请填写快递信息</h2>
	    <div class="ui-form kdzs-ui-margin-bottom">
	      {cyoa_upload_image 
	       title='拍照'
	       styleid=2
	       max=5 
	       allow_upload=1 
	       name = at_ids }
	
		  {cyoa_user_selector 
		   title='收件人' 
		   users=$cc_users 
		   user_input='uid' 
		   user_max=1}
	     </div>
	
	<div class="ui-btn-wrap ui-padding-bottom-0 ui-padding-top-0">
	   <button  id="apost" class="ui-btn-lg ui-btn-primary">提交</button>
	</div>
</form>
{/if}

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

	//快递登记
	$('#apost').on('click', function(e) {
		var uid =$('#uid').val();
		if(uid == 0){
			show_dialog('请选择收件人！');
			return false;
		}

	});

   	var sbt = new submit();//报告提交
	sbt.init({"form": $("#express_post")}); 
	
});

{/literal}
</script>

{include file='mobile/footer.tpl'}
