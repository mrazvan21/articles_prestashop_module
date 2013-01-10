<?php
/*
 *  @author Razvan Moldovan
 *  @email m.razvan92@gmail.com
*/

if (!defined('_PS_VERSION_'))
	exit;

class articles extends Module
{
	private $_html = '';
	private $_postErrors = array();
	private $allowedExts =  array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png');
	private $per_page = 3;
	
	public function __construct()
	{
		$this->name = 'articles';
		$this->tab = 'front_office_articles';
    	$this->version = 1.0;
    	$this->author = 'Razvan Moldovan';
    	$this->need_instance = 0;
		
		parent::__construct();
 
    	$this->displayName = $this->l('Articles');
   		$this->description = $this->l('Modul articole.');
	}
	
	public function install()
	{
		if (parent::install() == false OR !$this->registerHook('home') OR !$this->installDB())
     		 return false;
    	return true;
	}
	
	public function installDb()
  	{
  		 Db::getInstance()->execute('
		    CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'articles` (
		      `id_articol` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY ,
		      `title` VARCHAR(200) NOT NULL,
		      `content` text,
			  `date` datetime,
			  `image` VARCHAR(60)
		    )');
		return true;
  	}
	
	public function uninstall()
  	{
  		parent::uninstall();
  	}	
	
	private function _makeThumbnails($filename, $new_width)
	{
		$imageDir  = '/upload/articles/'.$filename;
		$fileInfos = pathinfo($filename);
		$imageName = str_replace('.'.$fileInfos['extension'],"", $filename);
		$thumbName = $imageName.$new_width.'.'.$fileInfos['extension'];
		
		$thumbDir = '/upload/articles/'.$thumbName;
		
		switch ($fileInfos['extension'])
		{
			case 'jpg' : case 'jpeg' :
		 		$src = imagecreatefromjpeg($imageDir); 
		 	break;
				 
			case 'png' :
				$src = imagecreatefrompng($imageDir);
			break;	
			
			case 'gif' :
				$src = imagecreatefromgif($imageDir);
			break;
		} 
		
		$width = imagesx($src);
  		$height = imagesy($src); 
		$height_t = floor($height * ($new_width / $width));
		
		$new = imagecreatetruecolor($new_width, $height_t);
		imagecopyresampled($new, $src, 0, 0, 0, 0, $new_width, $height_t, $width, $height);
		
		switch ($fileInfos['extension'])
		{
			case 'jpg' : case 'jpeg' :
		 		$src = imagejpeg( $new , $thumbDir);
		 	break;
				 
			case 'png' :
				$src = imagepng( $new , $thumbDir);
			break;	
			
			case 'gif' :
				$src = imagegif( $new , $thumbDir);
			break;
		} 
		
		imagedestroy( $new );
  		//imagedestroy( $src );
	}
	
	private function _postProcess()
	{
		$title 	 = Tools::getValue('title');
		$content = Tools::getValue('content');
		$date    = date('Y-m-d H:i:s') ;
		$image = $_FILES['file']['name'];
		
		if ($title == '')
			$errors[] = "Campul titlu nu este completat!";
		if ($content == '')
			$errors[] = "Campul Articol nu este completat!";
		if ($image)
		{
			if(in_array($_FILES['file']['type'], $this->allowedExts))
			{
				$uploadDir = "/upload/articles/".$image;
				$i=0;
				
				$fileInfos = pathinfo($uploadDir);
				$imageName = str_replace('.'.$fileInfos['extension'],"", $image);
		
				do
				{	
					$new_imageName = $imageName.(($i == 0)?'':$i).'.'.$fileInfos['extension'];
					$uploadDir = "/upload/articles/".$new_imageName;
					$i++;
				}
				while(file_exists($uploadDir));
				 
				if(!move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir))
				{
					$errors[] = "Eroare incarcare imagine";
				}
				else 
				{
					$this->_makeThumbnails($new_imageName, 60);
					$this->_makeThumbnails($new_imageName, 200);
				}
			}	
			else
			{
				$errors[] = "Imaginea trebuie sa aiba una din extensiile: jpg, jpeg, gif, png!";
			}
		}
		if (isset($errors) AND sizeof($errors))
			$this->_html .= $this->displayError(implode('<br />', $errors));
		else
		{
			$sql = "INSERT INTO "._DB_PREFIX_."articles (title, content, date, image) 
						VALUES('$title', '$content', '$date', '$new_imageName');";
			
			
			if(Db::getInstance()->execute($sql))
				$this->_html .=  $this->displayConfirmation("Articol incarcat cu succes!");
			else 
				$this->_html .= $this->displayError("Eroare baza de date!");
		}
	}
	
	private function _postRemove()
	{
		$id_article = Tools::getValue('id_article');
		
		$sql = "DELETE FROM "._DB_PREFIX_."articles
                            WHERE id_article = '$id_article'";
							
		if(Db::getInstance()->execute($sql))
				$this->_html .=  $this->displayConfirmation("Articol stears cu succes!");
			else 
				$this->_html .= $this->displayError("Eroare baza de date!");	
	}
	
	public function getContent()
	{
		$this->_html .= '<h2>'.$this->displayName.'</h2>';
	
		if (Tools::isSubmit('submitarticles'))
			$this->_html .= $this->_postProcess();
		
		if(Tools::isSubmit('submitRemove'))
	   		$this->_html .= $this->_postRemove();
		
		$this->_displayForm();
		
		return $this->_html;	
	}
	
	private function _displayForm()
	{
		$articles = Db::getInstance()->ExecuteS('SELECT * FROM '._DB_PREFIX_.'articles');
		
		$this->_html .= '<fieldset><legend>Marturii</legend><table style="width:100%;"><tbody>';
		
		if($articles)
		{
			foreach($articles as $article)
			{
				$this->_html .=  '<tr>';
					$this->_html .= '<td width="5%"><b>Titlu</b></td>';
					$this->_html .= '<td width="95%">'.$article['title'].'</td>';
				$this->_html .=  '</tr>';
				$this->_html .=  '<tr>';
					$this->_html .= '<td width="5%"><b>Articol</b></td>';
					$this->_html .= '<td width="95%">'.$article['content'].'</td>';
				$this->_html .=  '</tr>';
				$this->_html .=  '<tr>';
					$this->_html .= '<td width="5%">
									<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
									<input type="hidden" name="id_article" value="'.$article['id_article'].'" />
						          			<input type="submit" name="submitRemove" value="Sterge" class="button" />
						          	</form>
									</td>';
					$this->_html .= '<td width="95%"></td>';
				$this->_html .=  '</tr>';
				
				
			}
		}
		else
		{
			$this->_html .= '<tr valign="top"><td colspan=3>Nu exista nici o marturie adaugata.</td></tr>';	
		}
		
		$this->_html .= '</tbody></table></fieldset><br />'; 
		
		$this->_html .= '<form action="'.Tools::safeOutput($_SERVER['REQUEST_URI']).'" method="post" enctype="multipart/form-data">
						 <fieldset><legend>Adauga</legend>
						 <p>Adaugare articol</p><br />
						 <label>Titlu:</label>
						 <div class="margin-form">
							 <input type="text" name="title" value="'.Tools::safeOutput(Tools::getValue('title')).'" size="35">
						 </div>
						 <label>Articol</label>
						 <div class="margin-form">
						 	<textarea class="autoload_rte" name="content" rows="3" cols="32">'.Tools::safeOutput(Tools::getValue('content')).'</textarea>
						 </div>
						 <label>Imagine</label>
						 <div class="margin-form">
						 	<input type="file" name="file" id="file">
						 </div>
						 <center><input type="submit" value="Trimite" name="submitarticles" class="button" ></center>
						 </fieldset>
						 </form>';
						 
		return $this->_html;
	}
	
	public function hookHome()
	{
		global $smarty;
		$rendering = array();
		
		$articles = Db::getInstance()->ExecuteS('SELECT id_article, title, date, image FROM '._DB_PREFIX_.'articles ORDER BY id_article DESC LIMIT 3');
		
		//facem asa ca poate v-a vrea clientul sa modific data cu presc in ro si dimensiune nu
		//dimensiune ci si pe cuvinte si etc;
		//si din cate vad php in smarty versiunea noua e deprecated !!!!
		foreach($articles as $article)
		{
			$rendering[] = 	array('id'    =>$article['id_article'],
								  'title' => $article['title'], // si titlu pe cuvinte!
								  'date'  => $article['date'], // mai trebe sa prelucrezi data
								  'image' => explode(".", $article['image']));
		}
		
		$smarty->assign(array(
			'articles' => $rendering
		));
		
		return $this->display(__FILE__, 'tpl/hookhome.tpl');
			
	}
	
	public function display_articles()
	{
		global $smarty;
			
		if(!$page = (int)Tools::getValue('p'))
			$page = 1;
			
		$totalArticles = Db::getInstance()->getValue("SELECT COUNT(*) FROM "._DB_PREFIX_."articles");	
		$totalPages = ceil($totalArticles/$this->per_page);
		
		$first = ($page-1) * $this->per_page;
		
		$articles = Db::getInstance()->ExecuteS("SELECT * FROM "._DB_PREFIX_."articles LIMIT $first, $this->per_page");
		
		$range = array(($page-2 < 1 ? 1 : $page-2), ($page+2 > $totalPages ? $totalPages : $page+2));
		$first_page = $page > 3 ? '<a href="allarticles.php?p?p=1">1</a>'.($page < 5 ? ', ' : ' ... ') : null;
		$last_page = $page < $totalPages-2 ? ($page > $totalPages -4 ? ', ' : ' ... ').'<a href="allarticles.php?p?p='.$totalPages.'">'.$totalPages .'</a>' : null;
		
		$previous_page = $page > 1 ? '<a href="allarticles.php?p='.($page-1).'">Precedenta</a>  ' : null;
		$next_page = $page < $totalPages ? '  <a href="allarticles.php?p='.($page+1).'">Urmatoarea</a>' : null;
		
		for ($x=$range[0]; $x <= $range[1]; ++$x)
			$pages[] = '<a href="allarticles.php?p='.$x.'">'.($x == $page ? '<strong>'.$x.'</strong>' : $x).'</a>';
		
		$pageLinks = '<p class="pagination">'.$previous_page.$first_page.implode(', ', $pages).$last_page.$next_page.'</p>';
		
		$n = count($articles);
		for ($i=0; $i < $n; $i++)
			$articles[$i]['image'] = explode(".", $articles[$i]['image']);//pt dimensiunea img
		
		$smarty->assign(array(
				'pageLinks' => $pageLinks,
				'articles' => $articles
		));
	
		return $this->display(__FILE__, 'tpl/allarticles.tpl');
	}
	
	public function display_article()
	{
		global $smarty;
		
		if(!$id = (int)Tools::getValue('id'))
			 header( "Location: /" ) ;
			 
		$article = Db::getInstance()->getRow("SELECT * FROM "._DB_PREFIX_."articles WHERE id_article = $id");	
		
		$article['image'] = explode(".", $article['image']);
				
		$smarty->assign(array(
				'article' => $article
		));
		
		return $this->display(__FILE__, 'tpl/article.tpl');
	}
}
