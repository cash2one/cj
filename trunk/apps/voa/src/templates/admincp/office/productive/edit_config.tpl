{include file="$tpl_dir_base/header.tpl"}
        
<div class="panel panel-default">
<div class="panel-heading">编辑 </div>

  <div class="panel-body">
  
<form id="form-adminer-edit" class="form-horizontal font12" role="form" method="POST" action="">
    {if !$pid}
    <div class="form-group">
        <label  class="col-sm-2 control-label">打分项名称</label>
        <div class="col-sm-6">
            <input type="text" class="form-control" id="pti_name" name="form[pti_name]" placeholder="打分项名称" value="{$form['pti_name']}" />
        </div>
    </div>
    {/if}
    <div class="form-group">
        <label  class="col-sm-2 control-label">打分项说明</label>
        <div class="col-sm-6">
            <input type="text" class="form-control" id="pti_describe" name="form[pti_describe]" placeholder="打分项说明" value="{$form['pti_describe']}" />
        </div>
    </div>
    {if $pid}
    <div class="form-group">
        <label  class="col-sm-2 control-label">打分详细规则</label>
        <div class="col-sm-6">
            <input type="text" class="form-control" name="form[pti_rules]" placeholder="打分详细规则" value="{$form['pti_rules']}" />
        </div>
    </div>
    {/if}
    <div class="form-group">
        <label  class="col-sm-2 control-label">该项分数</label>
        <div class="col-sm-6">
            <input type="text" class="form-control" name="form[pti_score]" placeholder="该项分数" value="{$form['pti_score']}" />
        </div>
    </div>
    <div class="form-group">
        <label  class="col-sm-2 control-label">排序值</label>
        <div class="col-sm-6">
            <input type="text" class="form-control" name="form[pti_ordernum]" placeholder="排序值, 越大越靠前" value="{$form['pti_ordernum']}" />
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <input type="submit" name="submit" class="btn btn-primary" value="保存">
            &nbsp;&nbsp;
            <a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
        </div>
    </div>
</form>
  </div>
</div>
<script>
$(function() {
    $('#form-adminer-edit').submit(function(){
        {if $pid}
        if ( $.trim($('#pti_describe').val()) == '' ) {
            alert('打分项说明不能为空');
            return false;
        }
        {else}
        if ( $.trim($('#pti_name').val()) == '' ) {
            alert('打分项名称不能为空');
            return false;
        }
        {/if}
        $('#form-adminer-edit>.btn-primary').attr("disabled", true);
        $.ajax({
            url: '',
            data: $(this).serialize()+'&submit=1',
            type: "post",
            dataType: "json",
            success: function(r){
                if (r.result.status == 100) {
                    alert('保存成功');
                    location.href="{$defaultUrl}"; 
                } else {
                    alert('保存错误请重新提交');
                    $('#form-adminer-edit>.btn-primary').attr("disabled", false);
                }
            }
        });
        return false;
    });
});
</script>
{include file="$tpl_dir_base/footer.tpl"}