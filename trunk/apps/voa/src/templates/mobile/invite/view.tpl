{include file='mobile/header.tpl' css_file=''}
<div class="ui-tab">
    <ul id="content" class="ui-tab-content" style="width:100%;margin:auto;">
        <li>
            <div class='ui-form sale-ui-nowrap'>
                {cyoa_input_text attr_type='text' attr_readonly='1' attr_placeholder ='' title='姓名' attr_id='name' attr_name='name' attr_value={$view.name} div_class='ui-form-item  ui-border-b'}
                {cyoa_input_text attr_type='text' attr_readonly='1' attr_placeholder ='' title='性别' attr_id='gender' attr_name='gender' attr_value={$view.gender} div_class='ui-form-item  ui-border-b'}

				
				{if null != $view.gz_state}

					{if $view.gz_state == "已关注"}
						<div class="ui-form-item  ui-border-b">
							<div>
								<label for="gz_state">关注状态</label>
								<label for="gz_state" class="ui-badge" style="margin-left:84px;margin-top:17px;width:48px;background:#187B19">{$view.gz_state}</label>
							</div>
							
							<!-- <input name="gz_state" id="gz_state" value="{$view.gz_state}" type="text" placeholder="" readonly="1"> -->
						</div>
					{else}
						<div class="ui-form-item  ui-border-b">
							<div>
								<label for="gz_state">关注状态</label>
								<label for="gz_state" class="ui-badge-muted" style="margin-left:84px;margin-top:17px;width:48px;">{$view.gz_state}</label>
							</div>
						</div>
					{/if}
					<!-- <span>{$view.gz_state}</span> -->
				{/if}
				{cyoa_input_text attr_type='text' attr_readonly='1' attr_placeholder ='' title='邮箱' attr_id='email' attr_name='email' attr_value={$view.email} div_class='ui-form-item  ui-border-b'}
				{cyoa_input_text attr_type='text' attr_readonly='1' attr_placeholder ='' title='微信号' attr_id='weixin_id' attr_name='weixin_id' attr_value={$view.weixin_id} div_class='ui-form-item  ui-border-b'}
				{cyoa_input_text attr_type='text' attr_readonly='1' attr_placeholder ='' title='手机号' attr_id='phone' attr_name='phone' attr_value={$view.phone} div_class='ui-form-item  ui-border-b'}
				{cyoa_input_text attr_type='text' attr_readonly='1' attr_placeholder ='' title='职位' attr_id='position' attr_name='position' attr_value={$view.position} div_class='ui-form-item  ui-border-b'}
				<!--自定义字段-->
				{if null != $view.custom}
					{foreach $view.custom as $_key => $_val}
						{cyoa_input_text attr_type='text' attr_readonly='1' attr_placeholder ='' title={$_val[1]} attr_name={$_key} attr_value={$_val[0]} div_class='ui-form-item  ui-border-b'}
					{/foreach}
				{/if}
				
				{cyoa_input_text attr_type='text' attr_readonly='1' attr_placeholder ='' title='申请时间' attr_id='updated' attr_name='updated' attr_value={$view.updated} div_class='ui-form-item  ui-border-b'}
				{cyoa_input_text attr_type='text' attr_readonly='1' attr_placeholder ='' title='审批状态' attr_id='approval_state' attr_name='approval_state' attr_value={$view.approval_state} div_class='ui-form-item  ui-border-b'}

			</div>
			<input type="hidden" id="cdid" name="cdid" value="" />
        </li>
    </ul>
    	{if '审批中' == $view.approval_state}
		<div class="ui-btn-wrap" id="jxsp">
			<button type="button" id="check" class="ui-btn-lg ui-btn-primary" style="width:80%;margin:auto;">进行审批</button>
		</div>
		{/if}
		<!--对话框啊-->
		<div class="ui-dialog">
			<div class="ui-dialog-cnt">
				<div class="ui-dialog-bd">
					<div>
						<div>
							<p>是否同意此人加入企业号？</p>
						</div>
					</div>
				</div>
				<div class="ui-dialog-ft ui-btn-group">
					<button type="button" data-role="button"  id="cancel">驳回</button>
					<button type="button" data-role="button"  id="sure">同意</button>
				</div>
			</div>
		</div>
</div>
	<p id="show_dp">
            <a  class="ui-icon-add"></a>
    </p>
<script type="text/javascript">
	var per_id = '{$view.per_id}';

{literal}
require(["zepto", "underscore", "addrbook", "frozen"], function($, _, addrbook) {
	$("#check").tap(function(){
		var dia = $(".ui-dialog").dialog("show");
		dia.on('dialog:action', function (e) {
			if (e.index == 0) {
				var data = {'per_id': per_id, 'approval_state':2, 'ext_select': 0};
				$.post('/api/invite/post/check', data, function (json){
					if(json.errcode == 0 && json.result.update) {
						$('#approval_state').val("未通过");
						$("#jxsp").remove();
					}else{
						$.tips({content: '审核失败:' + json.errmsg});
					}
				}, 'json');
				// 改变元素
				
			}else if(e.index == 1){
				$("#show_dp a").trigger('tap');
			}
		});
	});

	// 选择部门 和 更新状态
	var ab = new addrbook();
	var data = {'per_id': per_id, 'approval_state': 1, 'ext_select': 1};
	ab.show({
		"dist": $("#addrbook"),
		"src": $("#show_dp"), // 触发对象
		"tabs": {
			"dp": {
				"name": "选择部门",
				"input": $("#cdid"),
				// cdid当前操作的部门id, checked: 选中(true)还是剔除(false)
				"cb": function() {
					// return;			
				}
			}
		},
		"cb": function() {
			// 获取到所有的数据
			data.cdid = $("#cdid").val();
			$.post('/api/invite/post/check', data, function(json){
				if(json.errcode == 0 && json.result.update) {
					$("#jxsp").remove();
					$("#approval_state").val("已通过");
					//$.dialog({"title": "提示", "content": tips, "button": btns});
					$.tips({content: '操作成功!'});
					// 此处发送消息
				}else{
					$.tips({content: '审核失败:' + json.errmsg});
				}
			}, 'json');

			// 改变元素
			
			//window.location.reload();
		}
	});

});
</script>
{/literal}
{include file='mobile/footer.tpl'}