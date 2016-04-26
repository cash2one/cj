{include file="$tpl_dir_base/header.tpl"}
<div class="stat-panel">
    <div class="stat-row">
        <!-- Small horizontal padding, bordered, without right border, top aligned text -->
        <div class="stat-cell col-sm-9 padding-sm-hr bordered no-border-r valign-top">
            <!-- Small padding, without top padding, extra small horizontal padding -->
            <h4 class="padding-sm no-padding-t padding-xs-hr text-center">{$nvote['subject']|escape}</h4>
            <!-- Without margin -->
            <hr>
            <div class="text-default"><span>发起人：{$usernames[$nvote['submit_id']]|escape}&nbsp;{$nvote['_is_show_name']}<span class="space"></span>投票时间：{$nvote['_start_time']} - {$nvote['_end_time']}<span class="space"></span>参与人数：{$nvote['voted_mem_count']}</span></div>
            <div class="padding-sm">
                {if $nvote['attachment']}
                <div class="img-box" id="fill_cover">
                    <a href="{$nvote['attachment']}" target="_blank"><img src="{$nvote['attachment']}/640" alt=""></a>
                </div>
                {/if}
            </div>


            <table class="table table-hover">
                <tbody>
                {$index = 1}
                {foreach $nvote['options'] as $key => $option}
                    <tr>
                        <td>{$index++}</td>
                        <td>{if $option['attachment']}<div class="img-box" id="fill_cover">
                                <a href="{$option['attachment']}" target="_blank"><img src="{$option['attachment']}/45" alt=""></a>
                            </div>
                        {/if}</td>
                        <td>{$option.option|escape}</td>
                        <td>{$option.nvotes}</td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
            <div>
            {if $nvote.end_time > startup_env::get('timestamp')}
                <a class="btn btn-danger" href="{$close_url}">结束此投票</a>
            {/if}
                <a class="btn btn-info" href="{$download_url}">导出</a>
            </div>

        </div> <!-- /.stat-cell -->
        <!-- Primary background, small padding, vertically centered text -->
        <div class="stat-cell col-sm-3 bordered padding-sm">
            <div id="hero-graph" class="graph text-info" ><h4>投票记录</h4></div>
            <div class="padding-sm">
                <ul>
                    {foreach $mem_options as $mem_option}
                        <li class="padding-xs-vr">
                            "{if $nvote.is_show_name == voa_d_oa_nvote::SHOW_NAME_YES}{$usernames[$mem_option['m_uid']]|escape}{else}{$mem_option.created|rgmdate}{/if}"投票给"{$nvote['options'][$mem_option['nvote_option_id']]['option']|escape}"
                        </li>
                    {/foreach}
                </ul>
            </div>
        </div>
    </div>
</div>




{include file="$tpl_dir_base/footer.tpl"}