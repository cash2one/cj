{*
	门店场所区域选择器
	模板变量：
	$selector_name  select控件的name，默认：placeregionid
	$placetypeid  区域类型，默认：1，受制于调用的应用
	$placeregionid 初始化显示的区域，默认：0
*}
{if empty($selector_name)}
{$selector_name = 'placeregionid'}
{/if}
{if empty($placetypeid)}
{$placetypeid = 1}
{/if}
{if empty($placeregionid)}
{$placeregionid = 0}
{/if}

<!--
{if !defined('__have_selector_placeregion__') || !__have_selector_placeregion__}
{define('__have_selector_placeregion__', 1)}
{$__selector_placeregion_first = 1}
{else}
{$__selector_placeregion_first = 0}
{/if}
-->
<span id="id-{$selector_name}" class="_selector_placeregion" data-name="{$selector_name}" data-defaults="{$placeregionid}">
	<img src="{$IMGDIR}loading.gif" alt="" style="width:16px;vertical-align:middle" />
</span>

{if $__selector_placeregion_first}
<script type="text/template" id="placeregion-selector-tpl">
<select<% if (name){ %> name="<%=name%>"<% } %> size="1" class="form-control" style="float:left;width:30%;">
<option value="0">请选择……</option>
{literal}
<% for (var key in list[parentid]) { %>
	<option value="<%=key%>"<% if (current == key) { %> selected="selected"<% } %>><%=data[key]['name']%></option>
<% } %>
{/literal}
</select>
</script>

<script type="text/javascript">

var placeregion_list = [];

function _list_parent_region(jq_box, init_placeregionid, name) {

	if (init_placeregionid < 0 || typeof(placeregion_list['data'][init_placeregionid]) == 'undefined' || !placeregion_list['data'][init_placeregionid]) {
		return false;
	}
	
	var cur = placeregion_list['data'][init_placeregionid];
	var parentid = cur['parentid'];
	
	// 列出当前，找到上级
	jq_box.prepend(txTpl('placeregion-selector-tpl', {
		"name": name,
		"parentid": parentid,
		"current": init_placeregionid, 
		"list": placeregion_list['level'],
		"data": placeregion_list['data']
	}));
	
	// 列出上级
	if (parentid >= 0 && init_placeregionid > 0) {
		_list_parent_region(jq_box, parentid, '');
	}
}

function _list_select() {

	jQuery(document).on('change', '._selector_placeregion select', function(){

		// 当前选择器
		var jq = jQuery(this);
		// 当前选择值
		var val = this.value;
		// 是否存在下级
		var have_children = true;
		if (typeof(placeregion_list['level'][val]) == 'undefined' || placeregion_list['level'][val].length == 0) {
			have_children = false;
		}

		// 移除下级select
		jq.nextAll('select').remove();
		// 无下级 或 选择为0，则直接返回
		if (val < 1 || !have_children) {
			return false;
		}
		//console.log(placeregion_list['level'][val]);
		// 新增下级
		// 清空上级的name，并赋值当前下级name
		jq.prevAll('select').removeAttr('name');
		jq.removeAttr('name');
		var s_name = jq.parent('._selector_placeregion').attr('data-name');
		jq.after(txTpl('placeregion-selector-tpl', {
			"name": s_name,
			"parentid": val,
			"current": 0,
			"list": placeregion_list['level'],
			"data": placeregion_list['data']
		}));
	});
}

/**
 * 初始化显示区域选择
 */
function _init_selector(plist) {
	jQuery.each(jQuery('._selector_placeregion'), function(i, o){
		var jq = jQuery(o);
		var id = jq.attr('id');
		var init_placeregionid = jq.attr('data-defaults');
		var name = jq.attr('data-name');
		jq.html('');

		if (init_placeregionid > 0 && typeof(plist['data'][init_placeregionid]) != 'undefined' && plist['data'][init_placeregionid]) {
			// 显示已经存在了的
			_list_parent_region(jq, init_placeregionid, name);
		} else {
			// 只显示顶级
			jq.append(txTpl('placeregion-selector-tpl', {
				"name": name,
				"current": 0,
				"parentid": 0,
				"list": plist['level'],
				"data": plist['data']
			}));
		}
	});
}
</script>
{/if}


<script type="text/javascript">
jQuery(function(){
	if (placeregion_list.length < 1) {
		jQuery.getJSON('/admincp/api/region/list/?placetypeid={$placetypeid}', function(r){
			if (typeof(r) == 'undefined') {
				alert('request error');
				return false;
			}
			if (typeof(r.errcode) == 'undefined') {
				alert('request error');
				return false;
			}
			if (r.errcode != 0) {
				alert(r.errmsg);
				return false;
			}
			console.log(r);
			placeregion_list = r.result;
			if (typeof(placeregion_list.data) == 'undefined') {
				jQuery('#id-{$selector_name}').html('<a href="/admincp/system/shop/region/">点击添加区域</a>');
			} else {
				_init_selector(placeregion_list);
			}
			
		});
	} else {
		_init_selector(placeregion_list);
	}
	
	_list_select({$placetypeid});
	
});
</script>
