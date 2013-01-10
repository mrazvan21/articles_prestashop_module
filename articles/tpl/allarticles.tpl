<div class="white">
    <h1>Noutati</h1>
	{foreach from=$articles item=article}
	<p>
		<h3><a href="{$base_dir}modules/articles/allarticles.php?id={$article.id_article|escape:'htmlall':'UTF-8'}">{$article.title|escape:'htmlall':'UTF-8'}</a></h3>
		<img src="/upload/articles/{$article.image[0]}200.{$article.image[1]}"><br>
		{$article.date|date_format:"%d %b %y"}<br>
		<span>{$article.content|escape:'htmlall':'UTF-8'}</span>
		<br>
		<a href="{$base_dir}modules/articles/allarticles.php?id={$article.id_article|escape:'htmlall':'UTF-8'}">Vezi detalii</a>
	</p>
	{/foreach}
	<p class="pagination"> {$pageLinks}
</div>