{include file='mobile/header.tpl' navtitle='红包' css_file='app_redpack.css'}

<div id="rp_get" class="ui-dialog show"{if $has_got} style="display: none;"{/if}>
    <div class="red-bg-red-bonus">
        <div class="red-bonus-top">
            <section class="red-avatar">
                <div class="ui-avatar-one red-bonus-avatar">
                    <span style="background-image:url({if $p_sets['default_sender_avatar']}{$p_sets['default_sender_avatar']}{else}{$cinstance->avatar($redpack['m_uid'])}{/if})"></span>
                </div>
                <p>{if $p_sets['default_sender_name']}{$p_sets['default_sender_name']}{else}{$redpack['m_username']}{/if}</p>
                <p><span>给你发了一个红包</span></p>
                <h2>{$redpack['wishing']}</h2>
            </section>
        </div>
        <div class="red-open">
            <div class="red-open-img" id="get_rp">拆红包</div>
        </div>
    </div>
</div>

<div id="view_rp" class="red-bg-white"{if !$has_got} style="display: none;"{/if}>
    <div class="red-top"></div>
    <section class="red-avatar red-receive-one">
        <div class="ui-avatar-one red-avatar-one">
            <span style="background-image:url({if $p_sets['default_sender_avatar']}{$p_sets['default_sender_avatar']}{else}{$cinstance->avatar($wbs_uid)}{/if})"></span>
        </div>
        <p>{if $p_sets['default_sender_name']}{$p_sets['default_sender_name']}{else}{$redpack['m_username']}的红包{/if}</p>
        {if $can_get}
        <p><span>{$redpack['wishing']}</span></p>
        <h1 id="rp_money">{if empty($rplog)}0.00{else}{$rplog['_money']}{/if}<span>元</span></h1>
        {else}
        <p><span>您没有权限领取该红包</span></p>
        {/if}
    </section>
</div>

<div id="list_title" class="red-status"{if !$has_got} style="display: none;"{/if}>{$redpack['redpacks']} 个红包, 已被领取 <b id="times">{$redpack['times']}</b> 个</div>
<ul class="ui-list ui-list-text red-ui-list" id="rp_list"{if !$has_got} style="display: none;"{/if}></ul>

{literal}
<script id="rp_list_tpl" type="text/template">
<%if (_.isEmpty(list)){%>
<section class="ui-notice ui-notice-norecord">
    <i></i>
    <p>暂无数据</p>
</section>
<%$('#templates').removeClass('ui-list');%>
<%}else{%>
<%_.each(list, function(item, index) {%>
<li class="ui-border-t" data-href="atom.html">
    <div class="ui-avatar-s">
        <span style="background-image:url(<%=item.avatar%>);"></span>
    </div>
    <div class="ui-list-info">
        <h4 class="ui-nowrap"><%=item.m_username%></h4>
        <p><%=item._created%></p>
    </div>
    <div class="ui-list-right red-list-right">
        <h4><%=item._money%>元</h4>
    </div>
</li>
<%});%>
<%}%>
</script>
{/literal}

<script type="text/javascript">
var redpack_id = {$redpack_id};
{literal}
require(["zepto", "underscore", "frozen", "showlist"], function($, _, fz, showlist) {
    var time_interval = null;
    // 发送红包消息
    function send_redpack() {

        $.ajax({
            'type': 'POST',
            'url': '/api/redpack/post/send/redpack_id/' + redpack_id,
            'success': function(data, status, xhr) {
                if (_.has(data, "errcode") && 0 == data["errcode"]) {
                    // 如果有红包数据
                    if (_.has(data, "result")) {
                        $("#rp_money").html(data["result"]["_money"] + "<span>元</span>");
                    }
                } else {
                    var dia = $.dialog({
                        title: '',
                        content: _.isEmpty(data["errmsg"]) ? '红包领取错误, 请重新尝试' : data["errmsg"],
                        button: ["确认"]
                    });
                    $("#rp_money").html("?<span>元</span>");
                }


                show_rp_list();
                get_list();
            }
        });
    }

    // 显示红包列表, 隐藏领取页面
    function show_rp_list() {

        $("#view_rp").show();
        $("#rp_list").show();
        $("#list_title").show();
        $("#rp_get").hide();
        clearInterval(time_interval);
    }

    // 获取列表数据
    function get_list() {

        st.show_ajax({'url': '/api/redpack/get/redpacklog', "data": {'redpack_id': redpack_id}}, {
            'dist': $('#rp_list')
        });
    }

    var st = new showlist();
    {/literal}
    {if $has_got}
    get_list();
    {else}
    $("#get_rp").on('click', function(e) {
        var self = $(this);
        var posx = 0;
        self.removeClass('red-open-img').addClass('red-open-gif').html('');
        time_interval = setInterval(function(e) {
            if (posx + 92 < 644) {
                posx += 92;
            } else {
                posx = 0;
            }

            self.css('background-position-x', '-' + posx + 'px');
        }, 100);
        // 请求地址详情
        $.ajax({
            'type': 'POST',
            'url': '/api/redpack/post/presend/redpack_id/' + redpack_id,
            'success': function(data, status, xhr) {
                if (_.has(data, "errcode") && 0 < data["errcode"]) {
                    self.removeClass('red-open-gif').addClass('red-open-img').html('拆红包');
                    clearInterval(time_interval);
                    var dia = $.dialog({
                        title: '',
                        content: _.isEmpty(data["errmsg"]) ? '红包领取错误, 请重新尝试' : data["errmsg"],
                        button: ["确认"]
                    });
                    show_rp_list();
                    get_list();
                    return true;
                }

                // 更新代码
                var t_dom = $("#times");
                t_dom.html(parseInt(t_dom.html()) + 1);
                send_redpack();
            }
        });
    });
    {/if}
    {literal}
});
{/literal}
</script>

{include file='mobile/footer.tpl'}