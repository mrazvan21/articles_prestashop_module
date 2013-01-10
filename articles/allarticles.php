<?php
$css_files[_THEME_CSS_DIR_.'global.css'] = 'all';
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/articles.php');

$articles = new articles();
if(isset($_GET['id']))
	echo $articles ->display_article();
else
	echo $articles ->display_articles();

include(dirname(__FILE__).'/../../footer.php')
?>
