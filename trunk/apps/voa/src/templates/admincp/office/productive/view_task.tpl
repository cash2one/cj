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
            {$data['ptt_title']|escape}
        </div>
    </div>-->
    <div class="form-group">
        <label  class="col-sm-2 control-label">执行人</label>
        <div class="col-sm-6">
               {$data['ptt_assign_users']}
        </div>
    </div>

    <div class="form-group">
        <label for="ca_password" class="col-sm-2 control-label">商店列表</label>
        <div class="col-sm-6">
            <label>共{$data['ptt_csp_id_list_total']}家门店</label>
                    {if $data['ptt_csp_id_list']}
                    <table class="table table-striped table-hover font12">
                    {foreach $data['ptt_csp_id_list'] as $key=>$val}
                    <tr><td>{$val['csp_name']}</td><td>{$val['pt_status_text']}</td></tr>
                    {/foreach}
                    </table>
                    {/if}

        </div>
    </div>
    
    <div class="form-group">
        
        <label class="col-sm-2 control-label">开始日期/结束日期</label>
        <div class="col-sm-6">
                {$data['ptt_start_date']} 至 {$data['ptt_end_date']}
         </div>
    </div>
    <div class="form-group">
        <label for="ca_mobilephone" class="col-sm-2 control-label">重复执行计划</label>
        <div class="col-sm-10">
        
            {if $data['ptt_repeat_frequency']['no']} 
              不重复
            {/if}
            {if $data['ptt_repeat_frequency']['day']}
                每$data['ptt_repeat_frequency']['day']天重复
            {/if}
            {if $data['ptt_repeat_frequency']['week']}
                每周{$data['ptt_repeat_frequency']['week']}重复
            {/if}
            {if $data['ptt_repeat_frequency']['mon']}
                每月{$data['ptt_repeat_frequency']['mon']}号重复
            {/if}
        </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">执行状态</label>
            <div class="col-sm-6">
                    {$data['ptt_execution_status_text']}
            </div>
        </div>
        <!--
        <div class="form-group">
            <label class="col-sm-2 control-label">任务说明</label>
            <div class="col-sm-5">
                      {$data['ptt_description']}
            </div>
        </div>-->
   
</form>

</div>
<div class="panel-footer">           
             <a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
                </div>
</div>
{include file="$tpl_dir_base/footer.tpl"}