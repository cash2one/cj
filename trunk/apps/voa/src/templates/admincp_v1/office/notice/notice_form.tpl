{include file='admincp/header.tpl'}

<form id="form-adminer-edit" class="form-horizontal font12" role="form" method="POST" action="{$form_action_url}">
	<input type="hidden" name="formhash" value="{$formhash}" />
{if $nt_id}
	<div class="form-group">
		<label class="col-sm-2 control-label">发布时间</label>
		<div class="col-sm-9">
			<p class="form-control-static">{$notice['_created']}</p>
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">更新时间</label>
		<div class="col-sm-9">
			<p class="form-control-static">{$notice['_updated']}</p>
		</div>
	</div>
{/if}
	<div class="form-group">
		<label for="nt_subject" class="col-sm-2 control-label">公告标题</label>
		<div class="col-sm-9">
			<input type="text" class="form-control" id="nt_subject" name="nt_subject" placeholder="公告标题，必须填写" value="{$notice['nt_subject']|escape}" maxlength="80" required="required" />
		</div>
	</div>
	<div class="form-group">
		<label for="nt_author" class="col-sm-2 control-label">发布人</label>
		<div class="col-sm-9">
			<input type="text" class="form-control" id="nt_author" name="nt_author" placeholder="公告发布人名字" value="{$notice['nt_author']|escape}" maxlength="50" />
		</div>
	</div>
	<div class="form-group">
		<label class="col-sm-2 control-label">通知给指定部门</label>
		<div class="col-sm-9">
			<label class="checkbox-inline"><input type="checkbox" name="nt_receiver[0]" value="0" class="_department"{if empty($department_selected) || isset($department_selected[0])} checked="checked"{/if} /> 所有部门</label>
{foreach $department_list as $_cd_id => $_cd}
			<label class="checkbox-inline"><input type="checkbox" name="nt_receiver[{$_cd_id}]" value="{$_cd_id}" class="_department"{if isset($department_selected[$_cd_id])} checked="checked"{/if} /> {$_cd['cd_name']|escape}</label>
{/foreach}
		</div>
	</div>
<!-- 
	<div class="form-group">
		<label for="nt_repeattimestamp" class="col-sm-2 control-label">重复提醒</label>
		<div class="col-sm-9">
			<label class="radio-inline"><input type="radio" id="nt_repeattimestamp" name="nt_repeattimestamp" value="{if $notice['nt_repeattimestamp'] > 0}{$notice['nt_repeattimestamp']}{else}{$repeattimestamp}{/if}"{if $notice['nt_repeattimestamp'] > 0} checked="checked"{/if} /> 是</label>
			<span class="space"></span><span class="space"></span>
			<label class="radio-inline"><input type="radio" id="nt_repeattimestamp_0" name="nt_repeattimestamp" value="0"{if !$notice['nt_repeattimestamp']} checked="checked"{/if} /> 否</label>
			<span class="help-block">
				选择“是”，如果指定接收人未阅读，将会间隔 {$repeattimestamp}秒 重复发送消息通知，会持续一天。
			</span>
		</div>
	</div>
 -->
	<div class="form-group">
		<label for="nt_message" class="col-sm-2 control-label">公告内容</label>
		<div class="col-sm-9">
			{$ueditor_output}
		</div>
	</div>
	<div class="form-group">
		<div class="col-sm-offset-2 col-sm-9">
			<button type="submit" class="btn btn-primary">{if $nt_id}编辑{else}添加{/if}</button>
			&nbsp;&nbsp;
			<a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
		</div>
	</div>
</form>

<script type="text/javascript">
var jq_all_department = jQuery('._department[value="0"]');
function _all_department(y) {
	if (y) {
		jQuery('._department').prop('disabled', true);
		jq_all_department.prop('checked', true).prop('disabled', false);
	} else {
		jQuery('._department').attr('disabled', false);
		jq_all_department.prop('checked', false);
	}
	
}
jQuery(function(){
	
	if (jq_all_department.prop('checked')) {
		_all_department(true);
	}
	jQuery('input[name^="nt_receiver"]').change(function(){
		if (this.value > 0) {
			// 选择了某个部门
			jq_all_department.prop('checked', false);
		} else {
			// 选择了所有部门
			if (jQuery(this).prop('checked')) {
				_all_department(true);
			} else {
				_all_department(false);
			}
		}
	});
});
</script>

{include file='admincp/footer.tpl'}