{include file="$tpl_dir_base/header.tpl"}

<script type="text/javascript">
var getUsersUrl = "{$getUsersUrl}";
var getRegionUrl = "{$getRegionUrl}";
var getShopUrl = "{$getShopUrl}";

{literal} 
$(function() {
	$('.selectpicker-city').on('change', function() {
		var val = $(this).val();
		if (val.length > 0) {
			$.ajax({
				url: getRegionUrl,
				type: "POST",
				data: 'parent=' + val.join(','),
				dataType: 'json',
				success: function (data) {
					$('.selectpicker-district').find('option').each(function() {
					    $(this).remove();
					});
					$.each(data, function (k, item) {
					    $('.selectpicker-district').append('<option value="' + k + '">' + item.cr_name + '</option>');
					});
					$('.selectpicker-district').selectpicker('refresh');
				}
			});
		}
	});
	$('#sandbox-container .input-daterange').datepicker({
		todayHighlight: true
	});
	$(".token-m-username").tokenInput(getUsersUrl, {
		theme: "facebook",
		queryParam: 'kw',
		hintText: '请输入你要搜索的用户名',
		prePopulatefun:function (el) {
			var val = $(el).parents('div.panel').find('.users').val();
			var ids = $(el).parents('div.panel').find('.it-uid').val();
			if (val) {
				ids = ids.split(',');
				var data = [];
				$.map(val.split(','), function(value, key) {
					if (value) {
						data[key] = {m_username: value, m_uid: ids[key]};
					}
				});
				if (data) {
					return data;
				} 
			}
			return false;
		},
		onAdd: function (item) {
			input = $(this).parents('div.panel').find('.it-uid');
			if (input.val()) {
				input.val(input.val() + ',' + item.m_uid);
			} else {
				input.val(item.m_uid);
			}
		},
		onDelete: function (item) {
			input = $(this).parents('div.panel').find('.it-uid');
			users = $(this).parents('div.panel').find('.users');
			var val = input.val();
			input.val('');
			var newval = '';
			$.map(val.split(','), function (value) {
				if (value && value != item.m_uid) {
					newval = newval + ','+value;
					input.val(newval);
				}
			});
			
		},
		propertyToSearch: 'm_username',
		minChars: 1,
		tokenLimit: 10,
	});
	$(".token-shop").tokenInput(getShopUrl, {
			theme: "facebook",
			queryParam: 'kw',
			hintText: '请输入你要搜索的门店名称',
			prePopulatefun:function (el) {
				var val = $(el).parents('div.panel').find('.csp-names').val();
				var ids = $(el).parents('div.panel').find('.csp-ids').val();
				if (val) {
					ids = ids.split(',');
					var data = [];
					$.map(val.split(','), function(value, key) {
						if (value) {
							data[key] = {csp_name: value, csp_id: ids[key]};
						}
					});
					if (data) {
						return data;
					} 
				}
				return false;
			},
			onAdd: function (item) {
				input = $(this).parents('div.panel').find('.csp-ids');
				if (input.val()) {
					input.val(input.val()+','+item.csp_id);
				} else {
					input.val(item.csp_id);
				}
			},
			onDelete: function (item) {
				input = $(this).parents('div.panel').find('.csp-ids');
				var val = input.val();
				input.val('');
				var newval = '';
				$.map(val.split(','), function (value) {
					if (value && value != item.csp_id) {
						newval = newval + ','+value;
						input.val(newval);
					}
				});
			},
			propertyToSearch: 'csp_name',
			minChars: 1,
			tokenLimit: 10,
		}); 
	});
{/literal} 
</script>
<div class="panel panel-default">
	<div class="panel-heading">搜索巡店 <button type="button" class="close"><span class="glyphicon glyphicon"></span></button></div>
	<div class="panel-body">

	<form class="form-horizontal" action="{$acurl}" method="post" style="margin-bottom:0">
	<div class="form-group ">
		<label class="control-label col-sm-1" for="title">日期</label>
		<div class="col-md-4" id="sandbox-container">
            <div class="input-daterange input-group" id="datepicker">
            <input type="text" class="input-sm form-control" value="{$search['start_date']}" placeholder="开始日期" name="search[start_date]">
            <span class="input-group-addon">至</span>
            <input type="text" class="input-sm form-control" value="{$search['end_date']}" name="search[end_date]" placeholder="结束日期">
            </div>
        </div>
        <label class="control-label col-sm-1">{$cache_config['title_city']}</label>
        <div class="col-sm-5">
            <select name="search[city][]" title="选择{$cache_config['title_city']}" data-header="可选择多个{$cache_config['title_city']}" class="selectpicker  selectpicker-city col-lg-5" data-live-search="true" multiple >
                {foreach $region as $_id=>$_data}
                <option value="{$_data['cr_id']}" {if !empty($search['city']) && in_array($_data['cr_id'], $search['city'])}selected{/if}>{$_data['cr_name']}</option>
                {/foreach}
            </select>
            <select name="search[district][]" title="选择{$cache_config['title_region']}" data-header="可选择多个{$cache_config['title_region']}" class="selectpicker selectpicker-district  col-lg-5" data-live-search="true" multiple>
                <option disabled>无</option>
                {foreach $search['district_org'] as $_id=>$_data}
                <option value="{$_data['cr_id']}" {if !empty($search['district']) && in_array($_data['cr_id'], $search['district'])}selected{/if}>{$_data['cr_name']}</option>
                {/foreach}
            </select>
            <span class="help-block"></span>
        </div>
    </div>
    <div class="form-group" style="margin-bottom:0">
        
         <label class="control-label col-sm-1">巡店人</label>
        <div class="col-sm-2">
            <input type="hidden" class="users" value="{$search['assign_users']}" >
            <input type="hidden" name="search[assign_uid]" class="it-uid" value="{$search['assign_uid']}" >
            <input type="text"   placeholder="" class="input-sm form-control token-m-username">
            <span class="help-block"></span>
        </div>
        <label class="control-label col-sm-1">门店名称</label>
        <div class="col-sm-2">
            <input type="hidden" class="csp-names" value="{$search['csp_names']}" >
            <input type="hidden" name="search[csp_ids]" class="csp-ids" value="{$search['csp_ids']}" >
            <input type="text" placeholder="" class="input-sm form-control token-shop">
            <span class="help-block"></span>
        </div>
        <div class="col-sm-2">
            <button name="submit" value="1" type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
        </div>
        
    </div>
    </form>
</div>
</div>
<form class="form-horizontal" role="form" method="post" action="{$deleteUrlBase}">
<div class="table-light">
    <div class="table-header"><div class="table-caption font12">记录列表</div></div>
	<table class="table table-striped table-bordered table-hover font12">
    <colgroup>
        <!--<col class="t-col-3" />-->    
        <col class="t-col-8" />
        <col class="t-col-8" />
        <col class="t-col-3" />
        <col class="t-col-3" />
        <col class="t-col-3" />
        <col class="t-col-3" />
        <col class="t-col-3" />
    </colgroup>
    <thead>
        <tr>
           <!-- <th>状态</th>-->
            <th>记录人</th>
            <th>门店名称</th>
            <th>城市</th>
            <th>区域</th>
            <th>总分</th>
            <th>提交日期</th>
            <th>操作</th>
        </tr>
    </thead>
{if $total > 0}
    <tfoot>
        <tr>
            <td colspan="7" class="text-right vcy-page">{$multi}</td>
        </tr>
    </tfoot>
{/if}
    <tbody>
{foreach $list as $_id=>$_data}
        <tr>
           <!-- <td>{$_data['ins_type_text']}</td> -->       
            <td>{$_data['m_username']|escape}</td>
            <td class="text-left">{if $shops[$_data['csp_id']]}{$shops[$_data['csp_id']]['csp_name']|escape}{/if}</td>
            <td>{if $regions['data'][$_data['_cr_id']]}{$regions['data'][$_data['_cr_id']]['cr_name']}{/if}</td>
            <td>{if $regions['data'][$_data['_cr_parent_id']]}{$regions['data'][$_data['_cr_parent_id']]['cr_name']}{/if}</td>
            <td>{$_data['ins_score']}</td>
            <td>{$_data['_updated_u']}</td>
            <td>   
                    {$base->linkShow($viewUrl, $_id, '详情', 'fa-eye', '')}
            </td>
        </tr>
{foreachelse}
        <tr>
            <td colspan="7" class="warning">{if $issearch}未搜索到指定条件的职务信息{else}暂无对应数据{/if}</td>
        </tr>
{/foreach}
    </tbody>
</table>
</div>
</form>

{include file="$tpl_dir_base/footer.tpl"}