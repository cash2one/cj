{include file="$tpl_dir_base/header.tpl"}
<div class="panel panel-default font12">
	<div class="panel-body">
		<form class="form-horizontal font12" role="form"
			action="{$form_action_url}" method="post">
			<input type="hidden" name="formhash" value="{$formhash}" /> 
			<input type="hidden" name="n_id" value="{$view['n_id']}" />

			<div class="form-group">
				<label class="control-label col-sm-2 " for="id_name">接口名称</label>
				<div class="col-sm-9">
					<input type="text" class="form-control form-small" id="id_name"
						name="name" placeholder="最多输入25个字符" maxlength="25"
						value="{$view.name}" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2 " for="id_summary">接口描述</label>
				<div class="col-sm-9">
					<textarea class="form-control form-small" id="id_desc" name="desc"
						placeholder="最多输入100个字符" maxlength="100" rows=4>{$view.desc}</textarea>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2 " for="id_label_tc_id">应用名称</label>
				<span class="space"></span>
				<div class="col-sm-2">
					<select name="cp_pluginid" class="form-control form-small"
						data-width="auto">
						<option value="" selected="selected">请应用名称</option> {foreach
						$plugins as $_key => $_val}
						<option value="{$_val['cp_pluginid']}" {if $_val['cp_pluginid'] ==$view['cp_pluginid'] } selected{/if}>{$_val['cp_name']}</option>
						{/foreach}
					</select>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label  col-sm-2 ">请求方式</label>
				<div class="col-sm-2">
					<select name="method" class="form-control form-small"
						data-width="auto">
						<option value="GET"  {if $view.method== "GET"} selected="selected" {/if}>GET</option> 
						<option value="POST" {if $view.method== "POST"} selected="selected" {/if}>POST</option>
						<option value="PUT" {if $view.method== "PUT"} selected="selected" {/if}>PUT</option>
						<option value="DELETE" {if $view.method== "DELETE"} selected="selected" {/if}>DELETE</option>
					</select>
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2 " for="id_title">请求地址</label>
				<div class="col-sm-9">
					<input type="text" class="form-control form-small" id="id_url"
						name="url" placeholder="最多输入100个字符" maxlength="100"
						value="{$view.url}" />
				</div>
			</div>

			<div class="form-group">
				<label class="control-label col-sm-2 " for="id_label_tc_id"></label>
				<span class="space"></span>
				<div class="col-sm-2">
					<button id="add_request" type="button" class="btn">添加请求参数</button>
				</div>
			</div>

			<div id="add_request_paramter" index="{count($list)}">
			    {if !empty($list)}
					{foreach $list as $_k => $_v}
					<input type="hidden" name="formhash" value="{$formhash}" /> <input
					type="hidden" name="p_id[{$_k}]" value="{$_v['p_id']}" />
					<div class="form-group">
						<label class="control-label col-sm-2"></label>
						<div class="col-sm-2">
							<input type="text" class="form-control" name="key[{$_k}]"
								placeholder="请输入参数名" value="{$_v['name']}" />
						</div>
						<div class="col-sm-2">
							<input type="text" class="form-control" name="val[{$_k}]"
								placeholder="请输入参数值" value="{$_v['val']}" />
						</div>
						<div class="col-sm-2">
							<select name="p_type[{$_k}]"
								class="form-control form-small" data-width="auto">
								<option value="" selected="selected">参数类型</option>
								<option value="1" {if $_v['type'] == 1}selected{/if}>int</option>
								<option value="2" {if $_v['type'] == 2}selected{/if}>string</option>
							</select>
						</div>
	
						<div class="col-sm-2" style="padding-top: 8px">
							<a href="javascript:;" class="text-danger _delete delete_first"><i
								class="fa fa-times"></i>删除</a>
						</div>
					</div>
					{/foreach}
				{/if}
			</div>

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10" style="text-align: center;">
					<button type="submit" class="btn btn-primary">编辑</button>
					&nbsp;&nbsp; <a href="javascript:history.go(-1);" role="button"
						class="btn btn-default">返回</a>
				</div>
			</div>

		</form>
	</div>
</div>


<script id="add_request_tpl" type="text/template">
<div class="form-group" >
	<label class="control-label col-sm-2"></label>
	<div class="col-sm-2">
		<input type="text" class="form-control" name="key[<%= index %>]" placeholder="请输入参数名" value="" />
	</div>
	<div class="col-sm-2">
		<input type="text" class="form-control" name="val[<%= index %>]" placeholder="请输入参数值" value="" />
	</div>
	<div class="col-sm-2">
		<select name="p_type[<%= index %>]" class="form-control form-small"
			data-width="auto">
			<option value="" selected="selected">参数类型</option>
			<option value="1">int</option>
			<option value="2">string</option>
		</select>
	</div>

    <div class="col-sm-2" style="padding-top:8px">
        <a href="javascript:;" class="text-danger _delete delete_first"><i class="fa fa-times"></i>删除</a>
    </div>
</div>
</script>

<script type="text/javascript">
{literal} 
$(function(){
	// 添加自定义参数
	$('#add_request').bind('click', function () {
		var index = $('#add_request_paramter').attr('index');
		index = parseInt(index)+1;
		var str = txTpl("add_request_tpl",{index: index});
		$('#add_request_paramter').attr('index',index).append(str);
	});
	
	// 删除自定义参数
	$(document).on('click', '.delete_first', function () {
		var obj = $(this).parents('div').parents('div').first();
		obj.remove();
	});
});	
{/literal} 
</script>
{include file="$tpl_dir_base/footer.tpl"}
