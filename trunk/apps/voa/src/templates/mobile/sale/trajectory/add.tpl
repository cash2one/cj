{include file='mobile/header.tpl' css_file='app_sale.css'}
<div class="ui-tab">
    <div class="ui-top-border"></div>
<form name="frmpost" id="frmpost" method="post" action="/api/sale/post/trajectory_insert">
    <div class="ui-form">
		{cyoa_select
			title='客户名称'
			attr_name='scid'
			attr_options=$coustmer
			div_class='ui-form-item ui-border-b ui-form-item-link'
		}
		{cyoa_select
			title='更改进度'
			attr_name='stid'
			attr_options=$types
			div_class='ui-form-item ui-border-b ui-form-item-link'
		}
		{cyoa_getlocation
				onlyread=0
				div_class='ui-form-item ui-border-b'
				input_location_name='location'
				input_address_name ='address'
		}

    </div>
    <div class="ui-form">
		{cyoa_textarea attr_placeholder='请简述您的工作报告(选填)' attr_id='textarea' attr_name='content' styleid=1 div_style='height:96px'  attr_style='height:72px'}
		{cyoa_upload_image title='上传图片' attr_id='at_ids' name='at_ids' attachs=$a progress=1}
    </div>


<div class="ui-btn-wrap ui-padding-bottom-0 ui-padding-top-0">
    <button class="ui-btn-lg ui-btn-primary">保存</button>
</div>
</form>
    <br />
    <br />
    <br />

{include file='mobile/navibar.tpl'}
</div>
</body>
{literal}
<script type="text/javascript">
require(["zepto", "underscore", "submit", "localtion", "frozen"], function($, _, submit) {
	var sbt = new submit();
	sbt.init({"form": $("#frmpost")});
});
</script>
{/literal}
{include file='mobile/footer.tpl'}