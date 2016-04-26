{include file='mobile/header.tpl' css_file='app_sale.css'}
<div class="ui-tab">
<form name="frmpost" id="frmpost" method="post" action="/api/sale/post/business_edit">
    <div class="ui-top-border"></div>
    <div class="ui-form">
	    {cyoa_input_text
			attr_type='text'
			title='操作时间'
			attr_readonly=1
			attr_value="{$data['updated']|escape}"
			div_class='ui-form-item  ui-border-b'
	    }
		{cyoa_input_text attr_type='text' attr_placeholder ='必填' title='机会名称' attr_id='title' attr_value="{$data['title']|escape}" attr_name='title' div_class='ui-form-item  ui-border-b'}
		{cyoa_select
			title='客户名称'
			attr_name='scid'
			attr_options=$coustmer
			attr_value=$data['scid']
			div_class='ui-form-item ui-border-b ui-form-item-link'
		}
		{cyoa_select
			title='客户进展'
			attr_name='types'
			attr_id='types'
			attr_options=$type
			attr_value=$data['typeid']
			div_class='ui-form-item ui-border-b ui-form-item-link'
		}
		{cyoa_input_text attr_type='text' attr_placeholder ='必填' title='预计金额' attr_id='amount' attr_value="{$data['amount']}" attr_name='amount' div_class='ui-form-item '}

    {cyoa_textarea attr_placeholder='选填' attr_id='content' title='备注' attr_value="{$data['content']|escape}" attr_name='content' styleid=2 div_style='height:96px'  attr_style='height:72px'}
	</div>
<input type="hidden" id="bid" name="bid" value="{$data['bid']}"/>
</form>
<div class="ui-btn-wrap ui-padding-bottom-0 ui-padding-top-0">
    <button  id="save" class="ui-btn-lg ui-btn-primary">保存</button>
</div>
    <br />
    <br />
    <br />

{include file='mobile/navibar.tpl'}
</div>
{literal}
<script type="text/javascript">
require(["zepto", "underscore", "submit", "frozen"], function($, _, submit) {

	var sbt = new submit();
	sbt.init({
  	       "form": $("#frmpost"),
  	       "src": $("#save"), // 可选
  	       "src_event": "click", // 可选
           "submit": function() {
 	           if($('#title').val() == ''){
					alert("机会名不能为空");
					return false;
				}
				if($('#amount').val() == ''){
					alert("预计金额不能为空");
					return false;
				}
				return true;
  	       }
   });
});
</script>
{/literal}
{include file='mobile/footer.tpl'}