{include file='admincp/header.tpl'}

<form id="form-adminer-edit" class="form-horizontal font12" role="form" method="POST" action="">
    <div class="form-group">
        <label  class="col-sm-2 control-label">门店名称</label>
        <div class="col-sm-5">
            <input type="text" class="form-control" name="csp_name" placeholder="门店名称" value="{$data['csp_name']|escape}" />
        </div>
    </div>

    <div class="form-group">
        <label for="ca_password" class="col-sm-2 control-label">{$cache_config['title_city']}</label>
        <div class="col-sm-5">
            <input type="text" class="form-control"  name="cr_name_parent" placeholder="{$cache_config['title_city']}" value="{$data['cr_name_parent']|escape}" />
        </div>
    </div>
    
    <div class="form-group">
        <label for="ca_mobilephone" class="col-sm-2 control-label">{$cache_config['title_region']}</label>
        <div class="col-sm-5">
            <input type="text" class="form-control"  name="cr_name" placeholder="{$cache_config['title_region']}" value="{$data['cr_name']|escape}"  />
        </div>
    </div>
    <div class="form-group">
        <label for="ca_mobilephone" class="col-sm-2 control-label">具体地址</label>
        <div class="col-sm-5">
            <input type="text" class="form-control" name="csp_address" placeholder="具体地址" value="{$data['csp_address']|escape}"  />
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
<script type="text/javascript" src="{$staticUrl}/js/md5.js"></script>
<script type="text/javascript">
$(function(){
    $('#form-adminer-edit').submit(function(){
        if ( $.trim($('[name=csp_name]').val()) == '' ) {
            alert('请输入门店名称');
            return false;
        }
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

{include file='admincp/footer.tpl'}