{include file="$tpl_dir_base/header.tpl"}
<h3>{$category_name}</h3>
<ol>
{foreach $doc_list as $f}
<li><a href="{$f['url']}">{$f['title']}</a></li>
{/foreach}
</ol>

{include file="$tpl_dir_base/footer.tpl"}