<div class="noutati">
    <div class="noutati-titlu">Noutati si evenimente</div>
    <div class="noutati-articole">
        <ul class="articole">
            {foreach from=$articles item=article}
                   <li>	
                    <img src="/upload/articles/{$article.image[0]}60.{$article.image[1]}">
                    <div class="articol-sumar">
                        <span class="data-articolului">{$article.date|date_format:"%d %b %y"}</span>
                        <a href="{$base_dir}modules/articles/allarticles.php?id={$article.id|escape:'htmlall':'UTF-8'}">
                                {$article.title|truncate:70:"..."}
                        </a>
                    </div>
                        <div classs="clearfix"></div>
                    </li>
            {/foreach}
        </ul>    
    </div>
<a href="{$base_dir}modules/articles/allarticles.php">Vezi toate noutatiile</a>
</div>