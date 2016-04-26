{include file="$tpl_dir_base/header.tpl"}
<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" id="soform" role="form" method="get" action="{$turnover_url}">
		<input type="hidden" name="issearch" value="1" />
		<input type="hidden" name="orderby" id="orderby" value="{$orderby}" />
		<input type="hidden" name="so_date" id="so_date" value="{$so_date}" />
		<div class="form-row m-b-20">
			<label class="vcy-label-none" for="id_username">员工姓名：</label>
			<input type="text" class="form-control form-small" id="id_username" name="username"  value="{$username|escape}" maxlength="54" />
			<span class="space"></span>
			<label class="vcy-label-none" for="id_dpid" style="width: 49px;text-align: right;">部门：</label>
			<select name="cd_id" id="id_dpid" class="form-control form-small"  style="width: 120px;" data-width="auto">
			{foreach $departments as $_v}
			<option value="{$_v['cd_id']}"{if $cd_id == $_v['cd_id']} selected{/if}>{$_v['cd_name']}</option>
			{/foreach}
			</select>
			
			<span class="space"></span>
			<label class="vcy-label-none" for="id_start_date">支付时间：</label>
            <div class="input-daterange input-group" id="datepicker">
	            <input type="text" class="input-sm form-control" value="{$start_date}" placeholder="开始日期" id="id_start_date" name="start_date">
	            <span class="input-group-addon">至</span>
	            <input type="text" class="input-sm form-control" value="{$end_date}" name="end_date" placeholder="结束日期">
            </div>
            <button name="sbt" value="1" type="submit" class="btn btn-info input-sm"><i class="fa fa-search"></i> 搜索</button> <span class="space"></span>
            <a href="{$turnover_url}?act=putout" class="btn btn-default ">&nbsp;导出</a>
		</div>
		</form>
	</div>
</div>
<div class="clearfix padding-sm-vr no-padding-t">
	<div class="btn-group">
		{foreach $so_dates as $_k => $_v}	
		<a href="javascript:;" class="btn cla_date{if $_k == $so_date} active{/if}" data-select="{$_k}" data-start="{$_v[0]}">{$_v[1]}</a>
		{/foreach}						
	</div>
</div>
<div class="clearfix padding-sm-vr no-padding-t">
	{foreach $orderbys as $_k => $_v}
	<input type="radio" name="r_orderby" id="{$_k}" class="cla_orderby" value="{$_k}"{if $_k == $orderby} checked{/if} /><label for="{$_k}">{$_v}</label>
	<span class="space"></span>
	{/foreach}
</div>

<div class="table-light">
	<table class="table table-striped table-bordered table-hover font12">
    <colgroup> 
        <col class="t-col-8" />
        <col class="t-col-8" />
        <col class="t-col-3" />
        <col class="t-col-3" />
    </colgroup>
    <thead>
        <tr>
            <th>员工姓名</th>
            <th>部门</th>
            <th>业绩</th>
            <th>提成</th>
        </tr>
    </thead>
	{if !empty($multi)}
    <tfoot>
        <tr>
            <td colspan="4" class="text-right vcy-page">{$multi}</td>
        </tr>
    </tfoot>
	{/if}
    <tbody>
	{foreach $list as $_id => $_v}
    <tr>    
        <td><a href="{$turnover_url}?act=view&saleuid={$_v['saleuid']}">{$_v['salename']|escape}</a></td>
        <td>{$departments[$_v['cd_id']]['cd_name']}</td>
        <td>{$_v['price']/100}</td>
        <td>{$_v['profit']/100}</td>
    </tr>
	{foreachelse}
    <tr>
        <td colspan="7" class="warning">{if $issearch}未搜索到指定条件的销售信息{else}暂无对应数据{/if}</td>
    </tr>
	{/foreach}
    </tbody>
	</table>
</div>

<script type="text/javascript">
$(function() {
	// 日期
	$('.input-daterange').datepicker({
		todayHighlight: true
	});
	
	// 排序
	$('.cla_orderby').on('click', function(e) {
		$('#orderby').val($(this).val());
		$('#soform').submit();
	});
	
	// 时间范围
	$('.cla_date').on('click', function(e) {
		$('#id_start_date').val($(this).data('start'));
		$('#so_date').val($(this).data('select'));
		$('#soform').submit();
	});
});
</script>
{include file="$tpl_dir_base/footer.tpl"}
