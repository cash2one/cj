{include file="$tpl_dir_base/header.tpl" css_file="exam/exam.css"}
<div class="table-light">
<form class="form-horizontal" role="form" method="post" action="">
<input type="hidden" name="formhash" value="{$formhash}" />

<div class="form-group">
	<label class="control-label col-sm-2" for="id_title"></label>
	<div class="col-sm-8">
		<ul class="op-step clearfix">
			<li class="col-sm-2">
				<em>1</em>
				<h3>模式设置</h3>
			</li>
			<li class="i-border col-sm-3"><em></em></li>
			<li class="col-sm-2 i-active">
				<em>2</em>
				<h3>选择题目</h3>
			</li>
			<li class="i-border col-sm-3"><em></em></li>
			<li class="col-sm-2">
				<em>3</em>
				<h3>基本设置</h3>
			</li>
		</ul>
	</div>
	<div class="col-sm-2"></div>
</div>

<table class="table table-striped table-hover table-bordered font12">
	<colgroup>
		<col class="t-col-20" />
		<col class="t-col-20 "/>
		<col class="t-col-20" />
		<col class="t-col-20" />
		<col class="t-col-20" />
	</colgroup>
	<thead>
		<tr>
			<th>题库名称</th>
			<th>题目名称</th>
			<th>题型</th>
			<th><label class="checkbox"><input type="checkbox" class="px" onchange="javascript:checkAll(this,'ids');" /><span class="lbl">选择</span></label></th>
			<th>分数</th>
			<th>排序</th>
		</tr>
	</thead>
	<tbody>
	{foreach $tis as $_id => $_data}
		<tr>
			<td>{$_data['tiku_name']}</td>
			<td>{$_data['title']|escape}</td>
			<td>{$types[$_data['type']]}</td>			
			<td><input type="checkbox" name="ids[]" value="{$_data['id']}" {if $paper['type'] == 1} checked{/if}/></td>
			<td>
				{$_data['score']}
				<input type="hidden" name="scores[{$_data['id']}]" value="{$_data['score']}"/>
			</td>
			<td><input type="text" name="orders[{$_data['id']}]"  value="{$_data['orderby']}"/></td>
		</tr>
	{/foreach}
	</tbody>
</table>
	<div class="form-group" id="btn-box">
		<div class="col-sm-offset-2 col-sm-9">
			<div class="row">
				<div class="col-md-3">
					<button type="button" onclick="javascript:location.href='{$addpaper_url}';" class="btn btn-default col-md-9">上一步</button>
				</div>
				{if $paper['type'] == 1}
				<div class="col-md-3">
					<button type="button" onclick="javascript:location.reload();" class="btn btn-primary  col-md-9">重新抽题</button>
				</div>
				{/if}
				<div class="col-md-3">
					<button type="submit" class="btn btn-primary  col-md-9" id="draft_btn">下一步</button>
				</div>
			</div>
		</div>
	</div>
	<input type="hidden" name="id" value="{$paper['id']}" />
</form>
</div>
{include file="$tpl_dir_base/footer.tpl"}
<script type="text/javascript">

$('#draft_btn').bind('click', function () {
	var ids = [];
	$('input[name="ids[]"]:checked').each(function(){
		//ids+=$(this).val();
		ids.push($(this).val());
	});
	if(ids.length==0){
		alert('至少选择一个题目');
		return false;
	}
	// 检测排序是否重复
	var o_arr=[];
	var ret=true;

	for (var i in ids) {
		var val=$('input[name="orders['+ids[i]+']"]').val();
		if( in_array( o_arr, val ) || val=='' ){
			ret=false;
		}
		o_arr.push( val );
	};

	if(ret==false){
		alert('序号不能为空或不能重复');
	}

	return ret;
});

</script>