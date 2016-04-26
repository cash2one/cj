{include file="$tpl_dir_base/header.tpl"}
<div class="table-header">
	<div class="table-caption font12 form-inline">
		<div class="row" style="margin:0;padding:0">
			<div class="col-sm-7">
                <span class="vcy-label-none">
                    <a id="down" href="javascript:;">全部展开</a>
                    <span class="space"></span> | <span class="space"></span>
                    <a id="up" href="javascript:;">全部折叠</a>
                </span>
			</div>
		</div>
	</div>
</div>
<div class="table-light">
	<form class="form-horizontal" role="form" method="post" action="">
		<input type="hidden" name="formhash" value="{$formhash}" />
		<table class="table table-striped table-bordered table-hover font12">
			<colgroup>
				<col />
				<col class="t-col-8" />
			</colgroup>
			<thead>
			<tr>
				<th class="text-left">职务级别及名称</th>
				<th>操作</th>
			</tr>
			</thead>
			<tbody id="tree">
			{if $positions}
				{foreach $positions as $position}
					<tr class="tr_position" data-parent-ids="{$position.parent_ids}" data-layer="{$position.layer}" data-parent-id="{$position.parent_id}" data-rel-id="{$position.id}">
						<td class="text-left">
							{$position.space}
							<i class="fa fa-minus _open"></i>
							<span class="space"></span>
							<strong>|——</strong>
							<input type="text" name="name[{$position.id}]" value="{$position.name}" class="form-control form-small title" style="width:240px;display:inline-block" />
						</td>
						<td>
							<a href="javascript:;" class="_add text-success" title="添加职务"><i class="fa fa-plus"></i> 添加</a>
							{if $position.parent_id != 0}
							<a href="javascript:;" class="remove text-danger" title="删除"><i class="fa fa-times"></i> 删除</a>
							{else}
							<span class="disabled"><i class="fa fa-times"></i> 删除</span>
							{/if}
						</td>
					</tr>
				{/foreach}
			{else}
				<tr>
					<td colspan="2" class="warning">暂无职务数据</td>
				</tr>
			{/if}
			</tbody>
			<tfoot>
			<tr>
				<td colspan="2" class="text-left"><button type="submit" class="btn btn-primary">提交</button></td>
			</tr>
			</tfoot>
		</table>
	</form>
</div>

<script type="text/template" id="tpl_position">
	<tr class="tr_position" data-parent-ids="<%=parent_ids%>" pid="<%=parent_id%>" data-layer="<%=layer%>" data-rel-id="<%=id%>">
		<td class="text-left">
			<%=space%>
			<i class="fa fa-minus _open"></i>
			<span class="space"></span>
			<strong>|——</strong>
			<input type="text" name="name[<%=id%>]" value="<%=name%>" class="form-control form-small title" style="width:240px;display:inline-block" />
		</td>
		<td>
			<a href="javascript:;" class="_add text-success" title="添加职务"><i class="fa fa-plus"></i> 添加</a>
			<a href="javascript:;" class="remove text-danger" title="删除"><i class="fa fa-times"></i> 删除</a>
		</td>
	</tr>
</script>

<script type="text/javascript">

	var url = '{$member_positions_url}';

	$(document).ready(function() {
		$('._add').on('click', add);
		$('.remove').on('click', remove);
		$('#down').on('click', function(){
			$('.tr_position').show();
		});
		$('#up').on('click', function(){
			$('.tr_position:not(:first)').hide();
		});
		$('._open').on('click', open);
	});

	function open() {
		var self = $(this);
		var parent_ids = self.closest('tr').attr('data-parent-ids');

		if (self.hasClass('fa-minus')) {
			self.removeClass('fa-minus').addClass('fa-plus');
			$('tr[data-parent-ids^=' + parent_ids + '_]').hide();
		} else {
			self.removeClass('fa-plus').addClass('fa-minus');
			$('tr[data-parent-ids^=' + parent_ids + '_]').show();
			$('tr[data-parent-ids^=' + parent_ids + '_]').each(function(){
				$(this).find('._open').removeClass('fa-plus').addClass('fa-minus');
			});
		}
	}

	function add() {

		var tr = $(this).closest('tr');
		var id = tr.attr('data-rel-id');
		var parent_id = tr.attr('data-parent-id');
		var parent_ids = tr.attr('data-parent-ids');
		var layer = tr.attr('data-layer');
		layer = parseInt(layer);

		$.ajax(url + '?act=add', {
			type : 'post',
			data : { parent_id : id},
			dataType : 'json',
			success : function(response) {
				if (response.errcode == 0) {
					var data = {
						id : '',
						parent_id : id,
						layer : layer + 1,
						space : '',
						parent_ids : parent_ids + '_' + id
					};
					data.space = get_space(layer);
					data.name = response.result.mp_name;
					data.id = response.result.mp_id;
					var tpl = txTpl('tpl_position', data);
					var obj_tpl = $(tpl);
					obj_tpl.find('.remove').on('click', remove);
					obj_tpl.find('._add').on('click', add);
					obj_tpl.find('._open').on('click', open);
					tr.after(obj_tpl);
				} else {
					alert(response.errmsg);
				}
			}
		});
	}

	function remove() {
		var tr = $(this).closest('tr');
		var id = tr.attr('data-rel-id');
		$.ajax(url + '?act=delete', {
			type : 'post',
			data : { id : id},
			dataType : 'json',
			success : function(response) {
				if (response.errcode == 0) {
					tr.remove();
				} else {
					alert(response.errmsg);
				}
			}
		});
	}

	function get_space(layer) {
		var $c = layer * 4;
		var space = '';
		for (var $i = 0; $i < $c; $i++) {
			space += '<span class="space"></span>';
		}
		return space;
	}
</script>
{include file="$tpl_dir_base/footer.tpl"}