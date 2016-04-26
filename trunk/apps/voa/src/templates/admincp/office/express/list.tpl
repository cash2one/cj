{include file="$tpl_dir_base/header.tpl"}

<div class="panel panel-default font12">
	<div class="panel-heading"><strong>搜索</strong></div>
	<div class="panel-body">
		<form class="form-inline vcy-from-search" role="form" action="{$searchActionUrl}">
			<input type="hidden" name="issearch" value="1" />
			<div class="form-row">
				<div class="form-group">
					<label class="vcy-label-none" for="id_cab_realname_author">快递状态：</label>
				    <select id="flag" name="flag" class="form-control form-small"  style="width: 194px;" data-width="auto">
                        <option value="0" {if $searchBy['flag'] == 0} selected="selected"{/if}>全部</option>
                        <option value="1" {if $searchBy['flag'] == 1} selected="selected"{/if}>待领取</option>
                        <option value="2" {if $searchBy['flag'] == 2} selected="selected"{/if}>已领取</option>
                    </select>
                    
                    <span class="space" style="margin-left: 50px;"></span>
                    <label class="vcy-label-none" for="id_m_username">收件人：</label>
                    <input type="text" class="form-control form-small"  id="id_username" name="username" value="{$searchBy['username']}" placeholder="快递领取人"/>
				
				    <span class="space" style="margin-left: 50px;"></span>
                    <button type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
				</div>
			</div>
			
		</form>
	</div>
</div>

<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
			列表
		</div>
	</div>
	<form class="form-horizontal" role="form" method="post" action="{$deleteUrlBase}">
	<input type="hidden" name="formhash" value="{$formhash}" />
	<table class="table table-striped table-bordered table-hover font12">
	<colgroup>
		<col class="t-col-5" />
		<col class="t-col-15" />
		<col class="t-col-15" />
		<col class="t-col-15" />
		<col class="t-col-15" />
	</colgroup>
	<thead>
		<tr>
			<th class="text-left"><label class="checkbox"><input type="checkbox" id="delete-all" class="px" onchange="javascript:checkAll(this,'delete');"{if !$deleteUrlBase || !$total} disabled="disabled"{/if} /><span class="lbl">全选</span></label></th>
			<th>快递状态</th>
			<th>领取时间</th>
			<th>收件人</th>
			<th>代领人</th>
			<th>操作</th>
		</tr>
	</thead>
{if $total > 0}
	<tfoot>
		<tr>
			<td colspan="2">{if $deleteUrlBase}<button type="submit" class="btn btn-danger">批量删除</button>{/if}</td>
			<td colspan="6" class="text-right vcy-page">{$multi}</td>
		</tr>
	</tfoot>
{/if}
	<tbody>
{foreach $list as $_id=>$_data}
		<tr>
			<td class="text-left"><label class="px-single"><input type="checkbox" name="delete[{$_id}]" class="px" value="{$_id}"{if !$deleteUrlBase} disabled="disabled"{/if} /><span class="lbl"> </span></label></td>
			<td>{if $_data['flag'] == 1}待领取{else}已领取{/if}</td>
			<td>{$_data['_get_time']}</td>
			<td>{$_data['username']}</td>
			<td>{$_data['c_username']}</td>
			<td>
				{$base->linkShow($deleteUrlBase, $_id, '删除', 'fa-times', 'class="text-danger _delete"')} | 
				{$base->linkShow($viewUrlBase, $_id, '详情', 'fa-eye', '')}
			</td>
		</tr>
{foreachelse}
		<tr>
			<td colspan="8" class="warning">{if $issearch}未搜索到指定条件的快递信息{else}暂无任何快递信息{/if}</td>
		</tr>
{/foreach}
	</tbody>
</table>
</form>
</div>

{include file="$tpl_dir_base/footer.tpl"}