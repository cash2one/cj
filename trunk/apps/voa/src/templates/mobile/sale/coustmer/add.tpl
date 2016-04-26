{include file='mobile/header.tpl' css_file='app_sale.css'}
<div class="ui-tab">
    <div class="ui-top-border"></div>
    <div class="ui-form">
	<form name="frmpost" id="frmpost" method="post" action="/api/sale/post/coustmer_edit">
		{cyoa_input_text attr_type='text' attr_placeholder ='必填' title='简称' attr_id='companyshortname' attr_value="{$data['companyshortname']|escape}" attr_name='companyshortname' div_class='ui-form-item  ui-border-b'}
		{cyoa_input_text attr_type='text' attr_placeholder ='必填' title='全称' attr_id='company' attr_value="{$data['company']|escape}" attr_name='company' div_class='ui-form-item  ui-border-b'}
		{cyoa_input_text attr_type='text' attr_placeholder ='必填' title='地址' attr_id='address' attr_value="{$data['address']|escape}" attr_name='address' div_class='ui-form-item  ui-border-b'}
		{cyoa_select
			title='客户来源'
			attr_name='source'
			attr_options=$source
		}
		{cyoa_input_text attr_type='text' attr_placeholder ='必填' title='联系人' attr_value="{$data['name']|escape}" attr_id='name' attr_name='name' }
		{cyoa_input_text attr_type='text' attr_placeholder ='必填' title='联系方式' attr_value="{$data['phone']|escape}" attr_id='phone' attr_name='phone' }
		{if is_array($fields) && count($fields) > 0}
		{foreach $fields as $k => $v}
			{if $v['required'] == 1}
			{cyoa_input_text attr_type='text' attr_placeholder='必填' attr_class='required' title="{$v['name']}" attr_id="value$k" attr_name="fields[$k][value]" attr_value="{$data['sfields'][$k]['value']|escape}" }
			<input type="hidden" name="fields[{$k}][key]" value="{$v['stid']}" />
			<input type="hidden" name="fields[{$k}][required]" value="1" />
			{else}
			{cyoa_input_text attr_type='text' attr_placeholder='非必填' title="{$v['name']}" attr_id="value$k" attr_name="fields[$k][value]" attr_value="{$data['sfields'][$k]['value']|escape}" }
			<input type="hidden" name="fields[{$k}][key]" value="{$v['stid']}" />
			{/if}
		{/foreach}
		{/if}
		<input type="hidden" id="scid" name="scid" value="{$data['scid']}"/>
	</form>
    </div>

<div class="ui-btn-wrap ui-padding-bottom-0 ui-padding-top-0">
    <button id="save" class="ui-btn-lg ui-btn-primary">保存</button>
</div>
    <br />
    <br />
    <br />

{include file='mobile/navibar.tpl'}
</div>
{literal}
<script type="text/javascript">

	// 点击后滚动条跳到点击的地方
	function on_click () {
		var click_position = $(this).offset();
		$(this).scrollTop(click_position);
	}

require(["zepto", "underscore", "submit", "frozen"], function($, _, submit) {

	// 绑定联系人手机
	$(function () {
		$('#phone').attr('onclick', 'javascript:on_click()');
	});

	var sbt = new submit();
	sbt.init({
		"form": $("#frmpost"),
		"src": $("#save"), // 可选
		"src_event": "click", // 可选
		"submit": function() {
			// 循环判断自定义字段是否填写
			var required_size = $('.required').size();
			for (var i=0; i < required_size; i ++) {
				var required_i = $('.required').eq(i);
				var trim_required_i = $.trim(required_i.val());
				if (trim_required_i == '') {
					var requisite = required_i.prev().text();
					$.tips({content:'必填项' + requisite + '未填'});
					required_i.val('');
					return false;
				}
			}
			if($('#companyshortname').val() == '') {
				$.tips({content:'简称不能为空'});
				return false;
			}
			var shortname_len = $('#companyshortname').val().length;
			if(shortname_len < 1 || shortname_len >10) {
				$.tips({content:'简体不能小于1个字，大于10个字'});
				return false;
			}
			if($('#company').val() == '') {
				$.tips({content:'全称不能为空'});
				return false;
			}
			if($('#address').val() == '') {
				$.tips({content:'地址不能为空'});
				return false;
			}
			if($('#name').val() == '') {
				$.tips({content:'联系人不能为空'});
				return false;
			}
			var phone = $('#phone').val();
			if(phone == '') {
				$.tips({content:'联系电话不能为空'});
				return false;
			}
			return true;
	   }
   });

});
</script>
{/literal}
{include file='mobile/footer.tpl'}