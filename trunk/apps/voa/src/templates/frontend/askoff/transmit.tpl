{include file='frontend/header.tpl'}

<body id="wbg_qj_launch">

<div id="viewstack">
    <section>
        <form name="askoff_post" id="askoff_post" method="post" action="/askoff/transmit/{$ao_id}?handlekey=post">
			<input type="hidden" name="formhash" value="{$formhash}" />
			<input type="hidden" name="referer" value="{$refer}" />
            <fieldset>
                <h1>批注(可不填, 默认同意):</h1>
                <textarea placeholder="请填写批注内容" required name="message">同意</textarea>
            </fieldset>

            <fieldset>
                <h1>审批人:</h1>
                {include file='frontend/mod_approver_select.tpl' iptname='approveuid' iptvalue=$approveuid}
            </fieldset>

            <fieldset>
                <h1>抄送人:</h1>
                {include file='frontend/mod_cc_select.tpl' iptname='carboncopyuids' ccusers=$ccusers}
            </fieldset>

            <footer><a id="apost" class="mod_button1">转审批</a></footer>
        </form>
    </section>
    <menu class="mod_members_panel"></menu>
</div>

<script>
{literal}
$onload(function() {
	/** 发布审批 */
	$one('#apost').addEventListener('click', function(e) {
		$one('form').onsubmit(e);
	});

	/** 表单校验 */
	$one('form').onsubmit = function(e) {
        var contentTa = $one('fieldset:nth-of-type(1) textarea'),
            memIpt = $one('fieldset:nth-of-type(2) input[type=hidden]');

        if (!contentTa.value || !$trim(contentTa.value).length) {
            MDialog.notice('请填写内容!');
            e.preventDefault();
            return false;
        }

        if (!memIpt.value || !$trim(memIpt.value).length) {
            MDialog.notice('请选择审批人!');
            e.preventDefault();
            return false;
        }

		if (true == ajax_form_lock) {
			return false;
		}

		ajax_form_lock = true;
		MLoading.show('稍等片刻...');
		MAjaxForm.submit('askoff_post', function(result) {
			MLoading.hide();
		});
		
		return true;
    };
});

function errorhandle_post(url, msg) {
	ajax_form_lock = false;
	MDialog.notice(msg);
}

function succeedhandle_post(url, msg) {
	MStorageForm.clear();
	MDialog.notice(msg);
	setTimeout(function() {
		window.location.href = url;
	}, 500);
}
{/literal}
</script>

{include file='frontend/footer_nav.tpl'}

{include file='frontend/footer.tpl'}