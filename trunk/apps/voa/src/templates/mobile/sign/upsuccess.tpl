{include file='mobile/header.tpl' navtitle='外出考勤' css_file='app_sign.css'}



<div class="s_title s_font s_margin">
	上报地理位置
</div>
<div class="s_box">
	<div class="s_suc">
		@ 上报成功!
	</div>
	<div class="s_add">
		{$data['sl_address']}
	</div>
	<div class="s_st">
		{$data['sl_signtime']}
	</div>

	<div  class="s_im">
		{cyoa_view_image
		attachs=$data['attachs']
		bigsize = 0
		}

		<form method ="post" action="/frontend/sign/upsuccess" id="form_im">
			<input type="hidden" name="sl_id" value="{$data['sl_id']}">
			<!--  <div style="" class="s_s_im"> -->
			{if count($data['attachs']) <= 5}
				{cyoa_upload_image title='上传图片' attr_id='upload_image' div_class='ui-form-item ui-form-item-show upload _uploader_box'  name='atids' attachs=$data['image'] progress=1 max= $last}
			{/if}
			<!--      </div> -->

	</div>
</div>
{if $last > 0}
	<div  class="s_s_btn" style="display:none">
		<button type="submit"  class="s_btn" id="btn_create">确定</button>
	</div>
{/if}
</form>
<script>

</script>

<script type="text/javascript">
	require(["zepto", "frozen"], function ($, fz) {

		function inputchange(){
			if($("input[name='atids']").val() != ''){
				$('.s_s_btn').css('display','block');
			}else{
				$('.s_s_btn').css('display','none');
			}
		}
		setInterval(inputchange, 50);

	});
</script>

{include file='mobile/footer.tpl'}