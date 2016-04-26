{include file='mobile/header.tpl' navtitle='均分红包' css_file='app_redpack.css'}

<form id="frm_redpack" method="post" action="/api/redpack/post/add?ac={$ac}&type={$type}">
    <input type="hidden" name="formhash" value="{$formhash}" />
    <input type="hidden" id="sendall" name="sendall" value="1" />
    <div class="ui-form red-ui-form">
        <div class="ui-form-item">
            <label for="all_mem">发送对象</label>
            <input id="all_mem" type="text" disabled placeholder="全体员工" />
        </div>
    </div>

    <div class="ui-form">
        <div class="ui-form-item">
            <label for="count">红包个数</label>
            <input id="count" name="count" type="text" placeholder="填写个数" />
            <span href="#" class="ui-form-item-unit">个</span>
        </div>
    </div>

    <div class="ui-form red-ui-form">
        <div class="ui-form-item">
            <label for="total">单个金额</label>
            <input id="total" name="total" type="text" placeholder="填写金额" />
            <span href="javascript:;" class="ui-form-item-unit">元</span>
        </div>
    </div>
    <div class="red-status">每人可领1个，金额相同且不超过200元</div>

    <div class="ui-form">
        <div class="ui-form-item red-form-item">
            <input name="wishing" type="text" maxlength="30" placeholder="恭喜发财，大吉大利" />
        </div>
    </div>

    <h1 class="red-money" id="money_total">￥0.00</h1>

    <div class="ui-btn-wrap">
        <button id="sendrp" class="ui-btn-lg disabled ui-btn-danger-disabled">发放红包</button>
    </div>
</form>

<script type="text/javascript">
var mem_ct = '{$mem_ct}';
{literal}
require(["zepto", "underscore", "submit", "frozen"], function($, _, submit, fz) {
    var sbt = new submit();
    sbt.init({
        "form": $("#frm_redpack"),
        "src": $("#sendrp"),
        "src_event": "tap",
        "submit": function(e) {
            if ($("#sendrp").hasClass("disabled")) {
                return false;
            }

            var total = $("#total").val();
            total = parseInt(total);
            if (isNaN(total) || 1 > total) {
                var dia = $.dialog({
                    title: '',
                    content: '红包金额错误',
                    button: ["确认"]
                });
                return false;
            }

            return true;
        }
    }, {
        'success': function(data, status, xhr) {
            $.get('/qywxmsg/send');
            if (0 == data["errcode"]) {
                var dia = $.dialog({
                    title: '',
                    content: '红包发送成功',
                    button: ["确认"]
                });
                dia.on("dialog:hide", function(e) {
                    location.href = "/frontend/redpack/new";
                });
            }
        }
    });

    // 输入框
    function ipt_change(ipt) {

        var total = parseFloat(ipt.val());
        if (isNaN(total) || 1 > total) {
            $("#sendrp").removeClass('ui-btn-danger').addClass('ui-btn-danger-disabled').addClass('disabled');
            $("#money_total").html("￥0.00");
        } else {
            $("#sendrp").removeClass('disabled').removeClass('ui-btn-danger-disabled').addClass('ui-btn-danger');
            $("#money_total").html("￥" + (total * mem_ct));
        }
    }

    // 监听输入框
    var ele_t = $("#total");
    ele_t.on("input", function(e) {

        ipt_change($(this));
        return true || e;
    });

    ele_t.on("propertychange", function(e) {

        ipt_change($(this));
        return true || e;
    });
});
{/literal}
</script>

{include file='mobile/footer.tpl'}