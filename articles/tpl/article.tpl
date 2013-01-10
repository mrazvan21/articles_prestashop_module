<div class="white">
	<h3>{$article.title|escape:'htmlall':'UTF-8'}</h3>
	<img src="/upload/articles/{$article.image[0]}200.{$article.image[1]}"><br>
	{$article.content|escape:'htmlall':'UTF-8'}
</div>