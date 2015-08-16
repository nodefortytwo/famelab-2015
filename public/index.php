<?php
require __DIR__ . '/../vendor/autoload.php';

$_ENV['content_dir'] = __DIR__ . '/../content/';

//move to nginx config
if(substr($_SERVER['REQUEST_URI'], -1) !== '/'){
	header('Location: ' . $_SERVER['REQUEST_URI'] . '/');
}

$url = substr($_SERVER['REQUEST_URI'], 1, -1);

$file = find_active_file($url);

if(!$file){
	$file = $_ENV['content_dir'] . '_errors/404.html';
	$response_code = 404;
}else{
	$response_code = 200;
}

$content = file_get_contents($file);
$parsedown = Parsedown::instance();
$parsedown->setBreaksEnabled(true);

$content = ['body' => $parsedown->text($content), 'nav' => render_menu(get_menu($url))];


$template = file_get_contents(__DIR__ . '/templates/index.html');

foreach($content as $k=>$v){
	$template = str_replace('###' . strtoupper($k) . '###', $v, $template);
}

echo $template;


function find_active_file($url){
	$content_dir = $_ENV['content_dir'];
	$path = $content_dir . $url;
	if(file_exists($path) && file_exists($path . '/' . 'index.html')){
		return $path . '/' . 'index.html';
	}elseif(file_exists($path . '.html')){
		return $path . '.html';
	}

	return false;
}

function get_menu($url){
	$content_dir = $_ENV['content_dir'] . $url;
	if(!file_exists($content_dir)){
		$pos = strrpos($content_dir, '/') + 1;
		$content_dir = substr($content_dir, 0, $pos);
		$url .= '/../';
	}

	$iterator = new \DirectoryIterator($content_dir);
	
	$items = array();
	foreach ($iterator as $info) {
		$path = $info->getPathname();
		$path = str_replace($content_dir, '', $path);
		$path = str_replace(['index.html', '.html'], '', $path);
		if(substr($path, -1) == '.' || substr($path, 0,1) == '_' || substr($path, 0,1) == '.' || empty($path)){
			continue;
		}

		$items[$url . $path] = $path;
	}
	
	return $items;
}

function render_menu($items){
	$html = '<ul class="nav navbar-nav">';
	foreach($items as $url=>$item){

		$display_name = ucwords(str_replace(['/', '-'], ' ', $item));
		if(empty($display_name)){
			continue;
		}
		$html .= "<li><a href=\"/{$url}\">{$display_name}</a><li>";
	}

	$html .= '</ul>';
	return $html;
}

function dd(){
	call_user_func_array('var_dump', func_get_args());
	die();
}