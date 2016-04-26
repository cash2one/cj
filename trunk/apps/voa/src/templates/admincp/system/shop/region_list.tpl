<div class="table-light">
	<div class="table-header">
		<div class="table-caption font12">
		<form class="form-inline vcy-from-search" role="form">
			<div class="form-row" style="margin:0;padding:0">
				<div class="row" style="margin:0;padding:0">
					<div class="col-sm-7">
						<div class="form-group">
							<span class="vcy-label-none">
								<a id="down" href="javascript:;">全部展开</a>
								<span class="space"></span> | <span class="space"></span>
								<a id="up" href="javascript:;">全部折叠</a>
								<span class="space"></span> | <span class="space"></span>
								<a id="add" href="javascript:;">添加一级区域</a>
							</span>
							<span class="space"></span>
							<input type="text" class="form-control form-small" id="region-keyword" value="" />
							<span class="space"></span>
							<button type="submit" class="btn btn-primary btn-sm font12">搜索</button>
						</div>
					</div>
					<div class="col-sm-5 text-right">
						<a href="{$region_batch_url}" class="btn btn-primary btn-sm pull-right">批量导入区域</a>
					</div>
				</div>
			</div>
		</form>
		</div>
	</div>
	<form class="form-horizontal" role="form" method="post" action="{$form_delete_url}">
	<input type="hidden" name="formhash" value="{$formhash}" />
		<table class="table table-striped table-bordered table-hover font12">
			<colgroup>
				<col />
				<col class="t-col-30" />
				<col class="t-col-8" />
			</colgroup>
			<thead>
				<tr>
					<th class="text-left">区域级别及名称</th>
					<th>区域负责人</th>
					<th>操作</th>
				</tr>
			</thead>
			<tbody id="tree">
				<tr>
					<td colspan="3" class="warning">暂无区域数据</td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="3" class="text-left">{if $delete_base_url}<button type="submit" class="btn btn-primary">提交</button>{/if}</td>
				</tr>
			</tfoot>
		</table>
	</form>
</div>

<script type="text/template" id="region-tpl">
<tr pid="<%=parentid%>" rel="<%=placeid%>" lv="<%=deepin%>">
	<td class="text-left"><%=space%><input type="text" value="<%=name%>" class="form-control form-small title" style="width:240px;display:inline-block" /></td>
	<td>
		<div class="contact" id="contact_<%=placeid%>"></div>
	</td>
	<td>
		<a href="javascript:;" class="_add text-success" title="添加子区域"><i class="fa fa-plus2"></i> 添加</a>
		<a href="javascript:;" class="remove text-danger" title="删除"><i class="fa fa-times"></i> 删除</a>
	</td>
</tr>
</script>

<script>
window._app = "contacts_pc";
window._root = '{$FM_JSFRAMEWORK}';
window.version = 0;
</script>
<script type="text/javascript" src="{$FM_JSFRAMEWORK}lib/requirejs/require.js"></script>
<script type="text/javascript" src="{$FM_JSFRAMEWORK}config.js"></script>
<script>
var list = {$list};
var member_list = {$member_list};
/**
 * 加载选人组件
 * @param defaults 默认选择的人员、部门
 * @param range_limit_data 限制选择的人员和部门范围
 */
function _load_user_selector(defaults) {
	requirejs(["jquery", "views/contacts"], function($, contacts) {
		$(function () {
			$('div.contact').each(function (){
				var id = $(this).closest('tr').attr('rel');
				var view = new contacts();
				if (typeof(defaults) == 'undefined') {
					defaults = [];
				}
				view.render({
					"container": '#' + this.id,
					"sct_callback" : sct_callback,
					"remove_callback" : remove_callback,
					"contacts_default_data": typeof(member_list[id]) == 'undefined' ? [] : member_list[id].selector,
					"input_name_contacts": 'contacts',
					"input_type": 'radio',
					"deps_enable": false,
					"contacts_enable": true
				});
			});
		});
	});
	//保存负责人
	function sct_callback(ctn){
		var contact_id = ctn.find('.mod_photo_uploader input').attr('value') * 1;	//负责人ID
		var region_id = ctn.closest('tr').attr('rel') * 1;
		if(contact_id < 1 || region_id < 1) return;
		var post = {
			"contact_id": contact_id, 
			"region_id": region_id
		};
		$.post('?subaction=bindmaster', post, function (json){
			
		}, 'json');
	}
	
	function remove_callback(ctn) {
		var uid = ctn.find('.mod_photo_uploader input').attr('value');
		var placeregionid = ctn.closest('tr').attr('rel');
		if (uid < 1 || placeregionid < 1) {
			return;
		}
		var post = {
			"contact_id": 0,
			"region_id": placeregionid
		};
		jQuery.post('?subaction=bindmaster', post, function (json) {
			
		}, 'json');
	}
	
}




$(function (){
	//区域树管理
	tree(list, $('#tree'));
	
});
function tree(list, ctn) {
	if (list.level) {
		ctn.html('');
	}
	var tr = null;
	var p = null;	//父tr
	var space = '';	//缩进字符
	var placeregionid, r, m;
	for (var k in list.level) {
		for (var i in list.level[k]) {
			placeregionid = list.level[k][i];
			r = list.data[placeregionid];
			m = typeof(member_list[placeregionid]) === 'undefined' ? [] : member_list;
			tr = fetch(r, m[placeregionid]);
			p = ctn.find('tr[rel='+r.parentid+']');
			if (p.length == 0) {
				//无父tr,即顶级目录
				ctn.append(tr);
			} else {
				p.after(tr);
			}
		}
	}
	fetch_after();
	//渲染tr模板(模板,数据)
	function fetch(r, m) {
		
		// 空隔符
		var space = '';
		for (var i = 1; i <= r.deepin; i++) {
			if (i == 1) {
				continue;
			}
			if (i >= 2) {
				space += '<span class="space"></span><span class="space"></span><span class="space"></span>';
			}
			if (i == 3) {
				space += '<span class="space"></span><span class="space"></span><span class="space"></span>';
			}
			if (i == r.deepin) {
				space += '<em></em><span class="space"></span><strong>|——</strong>';
			}
		}
		if (space == '') {
			space = '<span class="space"></span><em></em><span class="space"></span>';
		} else {
			space = '<span class="space"></span>' + space;
		}
		//console.log(m);
		var tr = txTpl('region-tpl',{
			"name": r.name,
			"placeid": r.placeregionid,
			"parentid": r.parentid,
			"deepin": r.deepin,
			"space": space,
			"username": typeof(m) == 'undefined' ? '' : m.user_list
		});
		return $(tr);
	}
	//图标,删除按钮的显示与隐藏
	function fetch_after() {
		//初始化删除标记
		ctn.find('tr').each(function (i, e){
			var tr = $(e);
			var id = tr.attr('rel');
			if (ctn.find('tr[pid='+id+']').length == 0 || id == 0) {
				//末级区域
				tr.find('._op_btn').remove();
				tr.find('.remove').show();
				if (tr.attr('lv') == 1 || tr.attr('lv') == 2) {
					tr.find('td:first em:first').before('<i class="fa fa-circle-o _op_btn"></i> ');
				}
			} else {
				//有子区域
				if (tr.find('._op_btn').length == 0 || tr.find('.fa-circle-o').length > 0) {
					tr.find('.fa-circle-o').remove();
					tr.find('td:first em:first').before('<i class="fa fa-minus _op_btn"></i> ');
				}
				tr.find('.remove').hide();
			}
			if (tr.attr('lv') == 3) {
				tr.find('._add').remove();
			}
		});
		//选人组件
		_load_user_selector([], []);
	}
	//折叠
	function up(tr) {
		var lv = tr.attr('lv') * 1;
		tr.nextAll('tr').each(function (i, e){
			if ($(e).attr('lv') * 1 > lv) {
				$(e).hide();	//将排在它之后,级别小于他的全部隐藏
			} else {
				return false;
			}
		});
		tr.find('.fa').removeClass('fa-minus').addClass('fa-plus');
	}
	//展开
	function down(tr) {
		var id = tr.attr('rel');
		var son = ctn.find('tr[pid='+id+']');
		//显示子级,并切换本级图标
		son.show();
		tr.find('.fa').removeClass('fa-plus').addClass('fa-minus');
		//如果子级有孙级且为下拉状态,则显示孙级
		son.each(function (i, e){
			if($(e).find('.fa-minus').length) {
				down($(e));
			}
		});
	}
	//折叠
	ctn.on('click', '.fa-minus', function (){
		up($(this).closest('tr'));
	});
	//展开
	ctn.on('click', '.fa-plus', function (){
		down($(this).closest('tr'));
	});
	//全部展开
	$('#down').click(function (){
		ctn.find('tr').show();
		ctn.find('.fa-plus').removeClass('fa-plus').addClass('fa-minus');	//箭头改为-号
	});
	//全部折叠
	$('#up').click(function (){
		ctn.find('tr[lv!=1]').hide();
		ctn.find('tr[lv=1]').show();
		ctn.find('.fa-minus').removeClass('fa-minus').addClass('fa-plus');//箭头改为+号
	});
	//添加子区域
	ctn.on('click', '._add', add);
	$('#add').click(add);
	function add() {
		var parent = $(this).closest('tr');	//父tr
		if (parent.length) {
			var id = parent.attr('rel');
			var lv = parent.attr('lv') * 1;
		} else {
			var parent = $('form>table tr:first');
			var id = 0;
			var lv = 0;
		}
		var r = {
			"parentid": id,
			"placetypeid": 1,
			"deepin": lv + 1
		};
		
		//对象转为post形式
		var post = {
			"deepin": r.deepin,
			"parentid": r.parentid,
			"placetypeid": r.placetypeid
		};
		
		$.post('?subaction=ajax_add', post, function(json){
			
			if (json.errcode == 0) {
				r.placeregionid = json.result.placeregionid;
				r.name = json.result.name;
				var tr = fetch(r);
				//添加到下一个同级或上级之前(假如有)
				
				if(id) {
					//添加2/3级区域
					parent.after(tr);
				}else{
					//添加1级区域
					ctn.prepend(tr);
				}
				
				fetch_after();
				tr.find('input:first').select();
			} else {
				alert(json.errmsg);
			}
		}, 'json');
	}
	
	//编辑区域
	ctn.on('change', 'input.title', function (){
		var input = $(this);
		var id = input.closest('tr').attr('rel');
		var value = this.value;
		if (value == '') {
			return false;
		}
		//对象转为post形式
		var post = {
			"placeregionid": id,
			"name": value
		};
		$.post('?subaction=ajax_edit', post, function (json){
			if (json.errcode == 0) {
				//保存成功
			} else {
				alert(json.errmsg);
			}
		}, 'json');
	});
	//删除区域
	ctn.on('click', '.remove', function (){
		var thistr = $(this).closest('tr');
		if (!confirm('是否确定删除分区 “'+thistr.find('input:first').val()+'”？')) {
			return false;
		}
		$.getJSON('?subaction=delete&placeregionid='+thistr.attr('rel'), function (json){
			if (json.errcode == 0) {
				thistr.remove();
				fetch_after();
			} else {
				alert(json.errmsg);
			}
		});
	});
}
</script>
<style>
#tree .fa, #tree a {
	cursor: pointer;
}
.fa-plus2:before {
	content:"\f067";
}
.contacts-search-box {
	background: none;
}
</style>
