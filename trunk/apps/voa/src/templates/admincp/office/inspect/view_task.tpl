{include file="$tpl_dir_base/header.tpl"}
<script>
var getUsersUrl = "{$getUsersUrl}";
var getRegionUrl = "{$getRegionUrl}";
var getShopUrl = "{$getShopUrl}";

{literal} 

{/literal} 
</script>
<div class="panel">
    <div class="panel-body">
<form id="form-adminer-edit" class="form-horizontal font12" role="form" method="POST" action="">
<!--
    <div class="form-group">
        <label  class="col-sm-2 control-label">任务标题</label>
        <div class="col-sm-5">
            {$data['it_title']|escape}
        </div>
    </div>-->
    <div class="form-group">
        <label  class="col-sm-2 control-label">执行人</label>
        <div class="col-sm-10">
            <p class="form-control-static">{$data['it_assign_users']}</p>            
        </div>
    </div>

    <div class="form-group">
        <label for="ca_password" class="col-sm-2 control-label">商店列表</label>
        <div class="col-sm-10">
            <p class="form-control-static">共{$data['it_csp_id_list_total']}家门店</p>
                    {if $data['it_csp_id_list']}
                    <table class="table table-striped table-hover font12">
                    {foreach $data['it_csp_id_list'] as $key => $val}
                    <tr><td>{if $shops[$val['csp_id']]}{$shops[$val['csp_id']]['csp_name']|escape}{/if}</td>
                    <td>{$val['ins_type_text']}</td></tr>
                    {/foreach}
                    </table>
                    {/if}

        </div>
    </div>
    
    <div class="form-group">
        
        <label class="col-sm-2 control-label">开始日期/结束日期</label>
        <div class="col-sm-10">
                <p class="form-control-static">{$data['it_start_date']} 至 {$data['it_end_date']}</p>
         </div>
    </div>
    <div class="form-group plan">
        <label for="ca_mobilephone" class="col-sm-2 control-label">重复执行计划</label>
        <div class="col-sm-10">
        <p class="form-control-static">
            {if $data['it_repeat_frequency']['no']} 
              不重复
            {/if}
            {if $data['it_repeat_frequency']['day']}
                每$data['it_repeat_frequency']['day']天重复
            {/if}
            {if $data['it_repeat_frequency']['week']}
                每周{$data['it_repeat_frequency']['week']}重复
            {/if}
            {if $data['it_repeat_frequency']['mon']}
                每月{$data['it_repeat_frequency']['mon']}号重复
            {/if}
        </p>
        </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">执行状态</label>
            <div class="col-sm-10">
                   <p class="form-control-static"> {$data['it_execution_status_text']}</p>
            </div>
        </div>
        <!--
        <div class="form-group">
            <label class="col-sm-2 control-label">任务说明</label>
            <div class="col-sm-5">
                      {$data['it_description']}
            </div>
        </div>--> 
   
</form>

</div>
<div class="panel-footer"> <a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a></div>
</div>
{include file="$tpl_dir_base/footer.tpl"}