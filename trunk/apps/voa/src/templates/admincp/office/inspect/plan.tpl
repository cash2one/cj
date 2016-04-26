{include file="$tpl_dir_base/header.tpl"}

<script>
var add_url = "{$addUrlBase}";
var getUsersUrl = "{$getUsersUrl}";

{literal} 
$(function () {
   $('#btn-add').click(function () {
        location.href = add_url;
   });   
    $('#sandbox-container .input-daterange').datepicker({
        todayHighlight: true
    });
 $(".token-m-username").tokenInput(getUsersUrl, {
        theme: "facebook",
        queryParam: 'kw',
        hintText: '请输入你要搜索的用户名',
        prePopulatefun:function (el) {
            var val = $(el).parents('div.col-sm-2').find('.users').val();
            var ids = $(el).parents('div.col-sm-2').find('.it-uid').val();
            if (val) {
                ids = ids.split(',');                
                var data = [];
                $.map( val.split(','), function(value, key) {
                    if (value) {
                        data[key] = { m_username: value, m_uid: ids[key]};
                    }
                });
                if (data) {
                    return data;
                } 
            }
            return false;
        },
        onAdd: function (item) {
            input = $(this).parents('div.col-sm-2').find('.it-uid');
            if (input.val()) {
                input.val(input.val()+','+item.m_uid);
            } else {
                input.val(item.m_uid);
            }
        },
        onDelete: function (item) {
            input = $(this).parents('div.col-sm-2').find('.it-uid');
            users = $(this).parents('div.col-sm-2').find('.users');
            var val = input.val();
            input.val('');
            var newval = '';
            $.map(val.split(','), function (value) {
                if (value) {
                    if (value != item.m_uid) {
                        newval = newval + ','+value;
                        input.val(newval);
                    }
                }
            });
            
        },
        propertyToSearch: 'm_username',
        minChars: 1,
        tokenLimit: 1,
    });   
});

{/literal} 
</script>

<div class="panel panel-default">
<div class="panel-heading">搜索 <button type="button" class="close"><span class="glyphicon glyphicon"></span></button></div>
  <div class="panel-body">

  <form   class="form-horizontal" action="" method="post" style="margin-bottom:0">
  <div class="form-group ">
          <label class="control-label col-sm-1" for="title">时间</label>
       <div class="col-md-4" id="sandbox-container">
            <div class="input-daterange input-group" id="datepicker">
            <input type="text" class="input-sm form-control" value="{$search['it_start_date']}" name="search[it_start_date]">
            <span class="input-group-addon">至</span>
            <input type="text" class="input-sm form-control" value="{$search['it_end_date']}" name="search[it_end_date]">
            </div>
        </div>
        
    </div>
    <div class="form-group " style="margin-bottom:0">
      
        <label class="control-label col-sm-1">巡店员</label>
        <div class="col-sm-2">
            <input type="hidden" class="users" value="{$search['it_assign_users']}" >
            <input type="hidden" name="search[it_assign_uid]" class="it-uid" value="{$search['it_assign_uid']}" >
            <input type="text"   placeholder="" class="input-sm form-control token-m-username">
            <span class="help-block"></span>
        </div>
        <label class="control-label col-sm-1">计划人员</label>
        <div class="col-sm-2">
            <input type="hidden" class="users" value="{$search['it_submit_users']}" >
            <input type="hidden" name="search[it_submit_uid]" class="it-uid" value="{$search['it_submit_uid']}" >
            
            <input type="text" value="{$search['it_submit_uid']}" placeholder="" class="input-sm form-control token-m-username">

            <span class="help-block"></span>
        </div>
        <button name="submit" value="1" type="submit" class="btn btn-info form-small form-small-btn margin-left-12"><i class="fa fa-search"></i> 搜索</button>
     
        <span id="btn-add" class="btn btn-primary input-sm">
            <i class="fa fa-plus"></i>
            <botton>新增</botton>
         </span>
             
        
    </div>
   
    </form>
</div>
</div>
<div class="table-light">
    <div class="table-header">
        <div class="table-caption font12">
            记录列表
        </div>
    </div>
<form class="form-horizontal" role="form" method="post" action="{$deleteUrlBase}">
<table class="table table-striped table-bordered table-hover font12">
    <colgroup>
        <col class="t-col-5" />
        <!--<col class="t-col-8" />-->
        <col class="t-col-15" />
        <col />
        <col class="t-col-15" />
        <col class="t-col-12" />
        <col class="t-col-12" />
        <col class="t-col-12" />
        <col class="t-col-12" />
        
    </colgroup>
    <thead>
        <tr>
            <th><label class="checkbox"><input type="checkbox" id="delete-all" onchange="javascript:checkAll(this,'delete');"{if !$deleteUrlBase} disabled="disabled"{/if} class="px" /><span class="lbl">全选</span></label></th>
            <!--<th>标题</th>-->
            <th>开始时间</th>
            <th>计划人员</th>
            <th>巡店人员</th>
            <th>计划巡店数量</th>
            <th>已巡店数量</th>
            <th>状态</th>
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
            <td class="text-left"><label class="px-single"><input type="checkbox" name="delete[{$_id}]" class="px" value="{$_id}"{if !$deleteUrlBase} disabled="disabled"{/if} /><span class="lbl"> </span></label>

                </td>
            <!--<td>{$_data['it_title']|escape}</td>-->
            <td>{$_data['it_start_date']|escape}</td>
            <td>{$_data['it_submit_username']}</td>
            <td>{$_data['it_assign_users']}</td>
            <td>{$_data['it_csp_id_list_total']}</td>
            <td>{$_data['it_finished_total']}</td>
            <td>{$_data['it_execution_status_text']}</td>

            <td>
                {if $_data['it_execution_status'] == 1 || $_data['it_execution_status'] == 3} 
                    {$base->linkShow($deleteUrlBase, $_id, '删除', 'fa-times', 'class="text-danger _delete"')} | 
                    {$base->linkShow($editUrl, $_id, '编辑', 'fa-edit', '')}
                    {if $_data['it_csp_id_list_total'] && $_data['it_assign_users']}
                        {$base->linkShow($executionUrl, $_id, '执行', 'fa-edit', '')}
                    {/if}
                {else}
                    {$base->linkShow($viewUrl, $_id, '查看', 'fa-eye', '')}
                    {$base->linkShow($rollbackUrl, $_id, '撤消', 'fa-mail-reply', '')}
                {/if}
            </td>
        </tr>
{foreachelse}
        <tr>
            <td colspan="8" class="warning">{if $issearch}未搜索到指定条件的职务信息{else}暂无职务信息{/if}</td>
        </tr>
{/foreach}
    </tbody>
</table>
</form>
</div>
{include file="$tpl_dir_base/footer.tpl"}