{include file='mobile/header.tpl' navtitle='发起投票'}

<div class="ui-top-border"></div>
<form action="/frontend/nvote/new" method="POST" id="form_vote" >
    <div class="ui-form ">
        <div class="ui-form-item ui-form-item-info ui-form-item-info-title">
            <label for="txt_subject">
                主题
            </label>
            <input id="txt_subject" type="text" maxlength="30" name="nvote[subject]" placeholder="请输入主题描述" style="width:80%" >
            {cyoa_upload_image title='' attr_id='upload_image' name='nvote[at_id]' max='1' div_class='upload ui-list-action clearfix' }
        </div>
    </div>
    <div class="ui-form " id="div_options">
        <div class="ui-form-item ui-form-item-info ui-border-b div_option">
            <label for="txt_op_1" class="label_option">
                <i class="ui-icon ui-icon-minus-solid ui-icon-empty i_empty"></i>
                <span>选项1</span>
            </label>
            <input type="text" id="txt_op_1" maxlength="30" name="options[0][option]" placeholder="请输入描述" style="width:80%">
            {cyoa_upload_image title='' attr_id='upload_image' name='options[0][at_id]' max='1' div_class='upload ui-list-action clearfix' }
        </div>
        <div class="ui-form-item ui-form-item-info ui-border-b div_option">
            <label for="txt_op_2" class="label_option">
                <i class="ui-icon ui-icon-minus-solid ui-icon-empty i_empty"></i>
                <span>选项2</span>
            </label>
            <input type="text" id="txt_op_2" maxlength="30" name="options[1][option]" placeholder="请输入描述" style="width:80%">
            {cyoa_upload_image title='' attr_id='upload_image' name='options[1][at_id]' max='1' div_class='upload ui-list-action clearfix' }
        </div>
        <div class="ui-form-item ui-form-item-order div_option_a">
            <a href="javascript:;" id="a_option_add">
                <i class="ui-icon ui-icon-add-solid"></i>
                新增选项
            </a>
        </div>
    </div>
    <div class="ui-txt-muted">选项数不得少于2个</div>
    <div class="ui-form">
        {$end_time = $timestamp + 86400}
        {cyoa_input_datetime
        attr_value=$end_time
        title="截止日期"
        attr_name="nvote[end_time]"
        div_class="ui-form-item"
        date_min=$timestamp
        time_min=$timestamp
        all=true
        }
        {cyoa_input_switch
        title="准许多选"
        attr_id="nvote_is_single"
        open=0
        attr_value="2"
        attr_name="nvote[is_single]"
        label_class="ui-label-switch"
        }
        {cyoa_input_switch
        title="匿名投票"
        attr_id="nvote_show_name"
        open=0
        attr_value="2"
        attr_name="nvote[is_show_name]"
        label_class="ui-label-switch"
        }
        {cyoa_input_switch
        title="投票后即可查看结果"
        attr_id="nvote_show_result"
        open=0
        attr_value="2"
        attr_name="nvote[is_show_result]"
        label_class="ui-label-switch"
        }
        {cyoa_input_switch
        title="允许重复投票"
        attr_id="nvote_is_repeat"
        open=0
        attr_value="1"
        attr_name="nvote[is_repeat]"
        label_class="ui-label-switch"
        }
    </div>
    <div class="ui-form">
        {cyoa_user_selector title='参与人' id='div_contacts' dp_name='选择部门' dp_input='cd_ids' user_input='m_uids' description='必选' user_max=100 div_class='ui-form-item ui-form-contacts'}
    </div>
    <div class="ui-btn-group-tiled ui-btn-wrap">
        <button type="button" class="ui-btn-lg ui-btn" id="btn_go_back">取消</button>
        <button type="submit" class="ui-btn-lg ui-btn-primary" id="btn_create">创建</button>
    </div>
</form>
{literal}
<script type="text/javascript">

    //动态添加选项模板
    var b_div_option = '';
    //删除按钮
    var html_delete = '<i class="ui-icon ui-icon-minus-solid i_delete"></i>';
    //删除按钮占位符
    var i_empty = '<i class="ui-icon ui-icon-minus-solid ui-icon-empty i_empty"></i>';

    require(["zepto", "underscore", "submit", "jweixin", "frozen"], function($, _, submit, wx) {
        //复制一个原始模板
        b_div_option = $('.div_option').eq(0).clone();
        b_div_option.find('input[type=text]').attr('id', '');

        $('#btn_go_back').on('click',function(){
            window.location.href='/frontend/nvote/my';
        });
        $('#btn_create').on('click', function() {
            var subject = $.trim($('#txt_subject').val());
            if (subject == '') {
                $.tips({content:'主题不能为空'});
                return false;
            }else {
                //遍历判断选项中的值
                var $inputs = $('.div_option input[type=text]');
                var $input_size = $inputs.size();
                for (var $i = 0;$i < $input_size; $i++) {
                    if ($.trim($inputs.eq($i).val()) == '') {
                        $.tips({content:'请填写选项描述'});
                        return false;
                    }
                }
                var uids = $('#m_uids').val();
                var cd_ids = $('#cd_ids').val();
                if (uids == '' && cd_ids == '') {
                    $.tips({content:'请选择参与人'});
                    return false;
                }
            }
        });

        var sbt = new submit();
        sbt.init({"form": $("#form_vote")});

        //绑定添加选项事件
        $('#a_option_add').on('click', add_option);


    });
    //添加选项
    function add_option () {
        var div_option = b_div_option.clone();
        $(this).parent('.div_option_a').before(div_option);
        div_option.find('input[type=text]').val('').focus();
        //添加第三个时，前面两个也添加删除按钮绑定事件
        if ($('.div_option').size() == 3) {
            $('.div_option').each(function(index, self) {
                set_option($(self), index + 1);
            });
        } else {
            set_option(div_option, $('.div_option').size());
        }
    }
    //删除选项
    function delete_option() {
        $(this).parents('.div_option').remove();
        var size = $('.div_option').size();
        $('.div_option').each(function(index, self){
            if (size > 2) {
                $(self).find('.label_option span').html('选项' +(index + 1));
            } else {
                $(self).find('.label_option').html(i_empty + '<span>选项' +(index + 1)+ '</span>');
            }
        });
    }
    //设置新增option
    function set_option(option, index) {
        var label_option = option.find('.label_option');

        label_option.html(html_delete + '<span>选项' + index + '</span>');
        label_option.find('.i_delete').on('click', delete_option);
        label_option.attr('for', 'txt_op_' + index);

        option.find('input[type=text]').attr('id', 'txt_op_' + index);
        option.find('input[type=text]').attr('name', 'options[' + (index -1) + '][option]');
        option.find('input[type=hidden]').attr('name', 'options[' + (index -1) + '][at_id]');
        option.find('div.upload').attr('data-name', 'options[' + (index -1) + '][at_id]');
    }
</script>
{/literal}
{$_cyoa_jsapi_[] = 'closeWindow'}
{include file='mobile/footer.tpl'}