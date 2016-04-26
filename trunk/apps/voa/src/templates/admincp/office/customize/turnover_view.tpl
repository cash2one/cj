{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default">
	<div class="panel-heading">个人信息 <button type="button" class="close"><span class="glyphicon glyphicon"></span></button></div>
	<div class="panel-body">
		<div class="form-group">
	        <label class="col-sm-2 control-label">员工姓名</label>
	        <div class="col-sm-4">{$saleinfo['m_username']}</div>
	    </div>
		<div class="form-group">
	        <label class="col-sm-2 control-label">手机</label>
	        <div class="col-sm-4">{$saleinfo['m_mobilephone']}</div>
	    </div>
		<div class="form-group">
	        <label class="col-sm-2 control-label">部门</label>
	        <div class="col-sm-4">{$departments[$saleinfo['cd_id']]['cd_name']}</div>
	    </div>
		<div class="form-group">
	        <label class="col-sm-2 control-label">昨日提成</label>
	        <div class="col-sm-4">{if $to_yesterday}{$to_yesterday['profit'] / 100}{else}0.00{/if} 元</div>
	    </div>
		<div class="form-group">
	        <label class="col-sm-2 control-label">近一月提成</label>
	        <div class="col-sm-4">{if $to_month}{$to_month['profit'] / 100}{else}0.00{/if} 元</div>
	    </div>
		<div class="form-group">
	        <label class="col-sm-2 control-label">总提成</label>
	        <div class="col-sm-4">{if $to_total}{$to_total['profit'] / 100}{else}0.00{/if} 元</div>
	    </div>
	</div>
</div>
<div class="clearfix padding-sm-vr no-padding-t">

<form class="form-inline vcy-from-search" id="soform" role="form" method="get" action="{$turnover_url}">
<input type="hidden" name="so_date" id="so_date" value="{$so_date}" />
<input type="hidden" name="act" value="view" />
<input type="hidden" name="saleuid" value="{$saleuid}" />
<label class="vcy-label-none" for="id_start_date">支付时间：</label>
<div class="input-daterange input-group" id="datepicker">
	<input type="text" class="input-sm form-control" value="{$start_date}" placeholder="开始日期" id="id_start_date" name="start_date">
	<span class="input-group-addon">至</span>
	<input type="text" class="input-sm form-control" value="{$end_date}" name="end_date" placeholder="结束日期">
</div>
<button name="sbt" value="1" type="submit" class="btn btn-info input-sm"><i class="fa fa-search"></i> 搜索</button> <span class="space"></span><span class="space"></span>
<label class="vcy-label-none">快速查看:</label><span class="space"></span>
<div class="btn-group">
	{foreach $so_dates as $_k => $_v}
		<a href="javascript:;" class="cla_date btn{if $_k == $so_date} active{/if}" data-select="{$_k}" data-start="{$_v[0]}">{$_v[1]}</a>
	{/foreach}
</div>
</form>
</div>

<script type="text/javascript">
$(function() {
	// 日期
	$('.input-daterange').datepicker({
		todayHighlight: true
	});

	// 时间范围
	$('.cla_date').on('click', function(e) {
		$('#id_start_date').val($(this).data('start'));
		$('#so_date').val($(this).data('select'));
		$('#soform').submit();
	});
});
</script>

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
            <th>产品名称</th>
            <th>数量</th>
            <th>单价</th>
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
        <td class="text-left">{$_v['goods_name']|escape}</td>
        <td>x{$_v['num']}</td>
        <td>{$_v['price'] / 100}</td>
        <td>{$_v['profit'] /100}</td>
    </tr>
	{foreachelse}
    <tr>
        <td colspan="7" class="warning">{if $issearch}未搜索到指定条件的销售信息{else}暂无对应数据{/if}</td>
    </tr>
	{/foreach}
    </tbody>
	</table>
</div>

{include file="$tpl_dir_base/footer.tpl"}
