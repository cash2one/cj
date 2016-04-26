{include file='mobile/header.tpl' css_file='app_sale.css'}
<div class="ui-tab">
    <ul class="ui-tab-nav ui-border-b">
        <li class="current">资料</li>
        <li id="return_visit" >回访</li>
    </ul>
    <ul id="content" class="ui-tab-content">

    </ul>
		<div class="ui-btn-wrap ui-padding-bottom-0 ui-padding-top-0">
			<button type="button" id="edit" class="ui-btn-lg ui-btn-primary">编辑</button>
		</div>
		<br />
		<br />
		<br />
	<script id="view" type="text/template">

        <li>
            <div class='ui-form'>
	            <div class="ui-form-item ui-form-item-show ui-conten-more">
		            <label for="#">简称</label>
		            <p id="companyshortname"><%=companyshortname%></p>
	            </div>
	            <div class="ui-form-item ui-border-t ui-form-item-show ui-conten-more">
		            <label for="#">全称</label>
		            <p id="company"><%=company%></p>
	            </div>
	            <div class="ui-form-item ui-border-t ui-form-item-show ui-conten-more">
		            <label for="#">地址</label>
		            <p id="address"><%=address%></p>
	            </div>
	            <div class="ui-form-item ui-border-t ui-form-item-show ui-conten-more">
		            <label for="#">客户来源</label>
		            <p id="source"><%=source%></p>
	            </div>
	            <div class="ui-form-item ui-border-t ui-form-item-show ui-conten-more">
		            <label for="#">联系人</label>
		            <p id="name"><%=name%></p>
	            </div>
	            <div class="ui-form-item ui-border-t ui-form-item-show ui-conten-more">
		            <label for="#">联系方式</label>
		            <p id="phone"><%=phone%></p>
	            </div>
                {*{cyoa_input_text attr_type='text' title='简称' attr_id='companyshortname' attr_name='companyshortname' attr_value='<%=companyshortname%>' div_class='ui-form-item ui-border-t ui-form-item-show ui-conten-more'}*}
				{*{cyoa_input_text attr_type='text' title='全称' attr_id='company' attr_name='company' attr_value='<%=company%>' div_class='ui-form-item ui-border-t ui-form-item-show ui-conten-more'}*}
				{*{cyoa_input_text attr_type='text' title='地址' attr_id='address' attr_name='address' attr_value='<%=address%>' div_class='ui-form-item ui-border-t ui-form-item-show ui-conten-more'}*}
				{*{cyoa_input_text attr_type='text' title='客户来源' attr_id='source' attr_name='source' attr_value='<%=source%>' div_class='ui-form-item ui-border-t ui-form-item-show ui-conten-more'}*}
				{*{cyoa_input_text attr_type='text' title='联系人' attr_id='name' attr_name='name' attr_value='<%=name%>' div_class='ui-form-item ui-border-t ui-form-item-show ui-conten-more'}*}
				{*{cyoa_input_text attr_type='text' title='联系方式' attr_id='phone' attr_name='phone' attr_value='<%=phone%>' div_class='ui-form-item ui-border-t ui-form-item-show ui-conten-more'}*}
				<% _.each(sfields, function(item) { %>
					<div class="ui-form-item ui-border-t ui-form-item-show ui-conten-more">
						<label for="#"><%=item.name%></label>
						<p><%=item.value%></p>
					</div>
					{*{cyoa_input_text attr_readonly='1' attr_type='text'  attr_placeholder ='' title="<%=item.name%>"  attr_value="<%=item.value%>" div_class='ui-form-item ui-border-t ui-form-item-show ui-conten-more'}*}
				<%});%>



        </li>

	</script>


{include file='mobile/navibar.tpl'}
</div>
<input type="hidden" id="scid" name="scid" value="{$scid}" />
{literal}
<script type="text/javascript">
require(["zepto", "underscore", "showview", "frozen"], function($, _, showview) {

	var scid = $("#scid").val();
	var data = '';
	var sv = new showview();
	data = sv.ajax_view('/api/sale/get/coustmer_view?scid='+scid, 'content', 'view');
	
	$("#edit").on("click", function(e) {
		window.location.href = "/frontend/sale/coustmer_add?scid="+scid;
	});
	$("#return_visit").on("click", function(e) {
		window.location.href = "/frontend/sale/coustmer_return?scid="+scid;
	});
});
</script>
{/literal}
{include file='mobile/footer.tpl'}