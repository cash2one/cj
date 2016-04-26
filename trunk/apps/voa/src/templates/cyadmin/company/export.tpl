{include file='cyadmin/header.tpl'}

<div class="row">
	<div class="col-md-6 col-md-offset-3">
	<div class="panel panel-success">
		<div class="panel-heading">提示信息</div>
		<div class="panel-body">
            {if $num == $page+1}
                文件下载成功！
                {else}
                共{$total}条数据，{$num}个文件，正在下载第{$page}个文件，请耐心等待。
            {/if}
		    
		</div>
        {if $num == $page+1}<div class="panel-footer text-right"><a href="{$list_url}" class="btn btn-success btn-sm" role="button"><i class="fa fa-backward"></i> 继续操作</a></div>{/if}
	</div>
	</div>
</div>
<script>
    var href = window.location.href;
    var total = {$total};
    var page = {$page};
    var num = {$num};
    var param = window.location.search;
    if(page){
           if(param.indexOf('page=')!=-1){
           var offest = (parseInt(page)+1);
          
             var str = 'page='+offest;
             newstr =  href.replace(/page=(\d)+/,str);
			window.location = newstr;
		}else{
			window.location = href+'&page='+page;
		}
    }

</script>

{include file='cyadmin/footer.tpl'}
