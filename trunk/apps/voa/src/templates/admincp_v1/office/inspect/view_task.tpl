{include file='admincp/header.tpl'}
<script>
var getUsersUrl = "{$getUsersUrl}";
var getRegionUrl = "{$getRegionUrl}";
var getShopUrl = "{$getShopUrl}";

{literal} 

{/literal} 
</script>
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
        <div class="col-sm-5">
               {$data['it_assign_users']}
        </div>
    </div>

    <div class="form-group">
        <label for="ca_password" class="col-sm-2 control-label">商店列表</label>
        <div class="col-sm-5">
            <label>共{$data['it_csp_id_list_total']}家门店</label>
                    {if $data['it_csp_id_list']}
                    <table class="table table-striped table-hover font12">
                    {foreach $data['it_csp_id_list'] as $key=>$val}
                    <tr><td>{$val['csp_name']}</td><td>{$val['ins_status_text']}</td></tr>
                    {/foreach}
                    </table>
                    {/if}

        </div>
    </div>
    
    <div class="form-group">
        
        <label class="col-sm-2 control-label">开始日期/结束日期</label>
        <div class="col-sm-5">
                {$data['it_start_date']} 至 {$data['it_end_date']}
         </div>
    </div>
    <div class="form-group plan">
        <label for="ca_mobilephone" class="col-sm-2 control-label">重复执行计划</label>
        <div class="col-sm-10">
        
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
        </div>
        </div>
        <div class="form-group">
            <label class="col-sm-2 control-label">执行状态</label>
            <div class="col-sm-5">
                    {$data['it_execution_status_text']}
            </div>
        </div>
        <!--
        <div class="form-group">
            <label class="col-sm-2 control-label">任务说明</label>
            <div class="col-sm-5">
                      {$data['it_description']}
            </div>
        </div>-->
    </div>
    <div class="text-center">
        <p class="bg-info">
            <a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a></p>
    
    </div>
    <br />
</form>


{include file='admincp/footer.tpl'}