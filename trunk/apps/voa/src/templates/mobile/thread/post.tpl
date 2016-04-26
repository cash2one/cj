{include file='mobile/header.tpl'}

<div class="ui-top-content-info"></div>
<form name="thread_post" id="thread_post" method="post"
	action="/frontend/thread/newthread?handlekey=post">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<div class="frozen-module-dom">
		<div class="ui-form ">
			<div class="ui-form-item ui-border-b">
				<label for="#">话题标题</label> <input type="text" placeholder="不超过15个字"
					id="subject" name="subject" maxlength="15">
			</div>
			<!--报告内容 -->
			{cyoa_textarea 
			attr_placeholder='请输入话题内容...' 
			attr_id='message'
			attr_name='message' 
			attr_maxlength=500
			attr_value=$message 
			onlymodule= 0 
			styleid=1 
			attr_style="height:61px" 
			div_style="height:76px" }
			<!-- 上传图片 -->
			{cyoa_upload_image 
			max=5 
			allow_upload=1 
			name = at_ids }
		</div>
	</div>
	<div class="ui-btn-wrap">
<!--     <button class="ui-btn-lg" id="cancle">取消</button> -->
		<button id="apost" class="ui-btn-lg ui-btn-primary">发布</button>
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

   	var sbt = new submit();//报告提交
	sbt.init({"form": $("#thread_post")}); 
	
});

require(["zepto", "underscore", "frozen"], function($, _, fz) {
	$(document).ready(function() {
		
		//发布报告
		$('#apost').on('click', function(e) {
		
			var title =$.trim($('#subject').val());
			if(title.length == 0){
				show_dialog('请填写话题标题！');
				return false;
			}
			
			var contentTa =$.trim($('#message').val());
			if(contentTa.length == 0){
				show_dialog('请填写话题内容！');
				return false;
			}
		});
		
		//取消
		$('#cancle').on('click', function(e) {
	 		window.history.go(-1);
			return false;
		});
		
	});
});

{/literal}
</script>


{include file='mobile/footer.tpl'}
