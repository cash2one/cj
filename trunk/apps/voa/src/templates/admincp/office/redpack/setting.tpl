{include file="$tpl_dir_base/header.tpl"}

<style type="text/css">
#tip {
    float:left;
    width:330px;
    font-size: 14px;
}
#tip li {
    margin: 12px 0;
}
.search-contacts {
    margin-top: 10px;
    margin-left: 10px;
}
.mod_photo_uploader {
    height: 38px;
}
</style>

<form class="form-horizontal font12" role="form" method="post" action="{$form_act_url}">
    <input type="hidden" name="formhash" value="{$formhash}" />

    <div class="form-group">
        <label for="users_container" class="col-sm-2 control-label text-danger">发红包用户</label>
        <div id="users_container" class="col-sm-10">
            {include file="$tpl_dir_base/common_selector_member.tpl"
            input_type='checkbox'
            input_name='m_uids[]'
            selector_box_id='users_container'
            allow_member=true
            allow_department=false
            default_data = $default_users
            }
        </div>
    </div>

    <div class="form-group">
        <label for="default_sender_name" class="col-sm-2 control-label text-danger">红包发送者名称</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="default_sender_name" name="default_sender_name" placeholder="发送者名称" value="{$p_sets['default_sender_name']}" maxlength="10" required="required" />
        </div>
    </div>
    <div class="form-group">
        <label for="default_sender_avatar" class="col-sm-2 control-label text-danger">红包发送者头像</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="default_sender_avatar" name="default_sender_avatar" placeholder="发送者头像URL" value="{$p_sets['default_sender_avatar']}" required="required" />
        </div>
    </div>

    <div class="form-group">
        <label for="redpack_min" class="col-sm-2 control-label text-danger">最小签到红包(单位:元)</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="redpack_min" name="redpack_min" placeholder="1" value="{$p_sets['_redpack_min']}" maxlength="30" required="required" />
            用户在签到时, 可能领取到得红包最小值
        </div>
    </div>
    <div class="form-group">
        <label for="redpack_max" class="col-sm-2 control-label text-danger">最大签到红包(单位:元)</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" id="redpack_max" name="redpack_max" placeholder="200" value="{$p_sets['_redpack_max']}" maxlength="30" required="required" />
            用户在签到时, 可能领取到得红包最大值
        </div>
    </div>

    <div class="form-group">
        <label for="new_rp" class="col-sm-2 control-label text-danger">是否启用新红包进行签到</label>
        <div class="col-sm-10">
            <input type="checkbox" class="form-control" id="new_rp" name="new_rp" value="1" /> 是
        </div>
    </div>
    <div class="form-group">
        <label class="col-sm-2 control-label">签到二维码</label>
        <div class="col-sm-10">
            <a href="/admincp/office/redpack/view/pluginid/{$p_sets['pluginid']}/?act=qrcode&id={$p_sets['sign_redpack_id']}">
                <img style="float:left;width:310px;height:310px;" src="/admincp/office/redpack/view/pluginid/{$p_sets['pluginid']}/?act=qrcode&id={$p_sets['sign_redpack_id']}"/>
            </a>
            <ul id="tip">
                <li>二维码：您可以在公司或其他场所张贴该图片，这样您的员工进入时通过扫描二维码进行“签到”操作后, 就可以领取红包了。</li>
                <li>可以使用户更加活跃。</li>
                <li>提示：该二维码为签到红包永久二维码。</li>
            </ul>
        </div>
    </div>
    <div class="form-group">
        <div class="col-sm-offset-2 col-sm-10">
            <button type="submit" class="btn btn-primary">编辑</button>
            &nbsp;&nbsp;
            <a href="javascript:history.go(-1);" role="button" class="btn btn-default">返回</a>
        </div>
    </div>
</form>

{include file="$tpl_dir_base/footer.tpl"}