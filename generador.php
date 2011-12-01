<?php
/**
 * generator of static web pages, based on files in FS
 * 
 * a 'full'description
 * multilene is cool
 * and big number is more cool
 * @author Jesus Christian Cruz Acono <devel@compermisosnetwork.info>
 * @version 0.0.1
 * @package sample
*/

/**#@+
 * Constants
 */
/**
 * basedir text
 * define the base dir to search
 */
define('basedir', "./");
/**
 * max_recursive numeric
 * define the mas levels of recursivity
 */
define('max_recursive', 10);
/**
 * fileType text
 * file Type to search
 */
define('fileType', 'pdf');

/**
 * fileToGenerate text
 * file name to generate
 */
define('fileToGenerate', 'generado.html');


/**
 * templatesDir text
 * rute to templates
 */
define('templatesDir', 'templates/basic/');


function parser($dirPath = './', $fileType = 'pdf', $recursive = 10 , $basedir = './'){
	if($recursive == 0){
		return;
	}	
	$retval = array();
	$fileTypePattern = '/\.'. $fileType . '$/';
	if(substr($dirPath, -1) != "/"){
		$dirPath .= "/";
	} 
	if ($handle = opendir($dirPath)) {
		while (false !== ($file = readdir($handle))) {
			$fileEntry = $dirPath. $file;
			if ($file != "." && $file != "..") {
				if (is_dir($fileEntry)) {
					$retval[] = array(
						"name" => $file,
						"type" => filetype($fileEntry),
						"size" => 0,
						"lastmod" => filemtime($fileEntry),
						"content" => parser($fileEntry, $fileType, $recursive - 1, $basedir)
					);
				}else {
					if(preg_match($fileTypePattern, $file)){
						$retval[] = array(
							"name" => $file,
							"namenotype" => str_replace('.' . $fileType, '', $file),
							"pathname" => str_replace($basedir, '', $fileEntry),
							"type" => mime_content_type($fileEntry),
							"size" => filesize($fileEntry),
							"lastmod" => filemtime($fileEntry)
						);
					} 
				}
			}
		}
		closedir($handle);
	}
	
	return $retval;
}
function cleaner($tree = array()){
	$cant = count($tree);
	$dummy_array = array();
	$rtree = array();
	for($i = 0; $i< $cant; $i++){
		if(isset($tree[$i]['content'])){
			if($tree[$i]['content'] === $dummy_array){
				unset($tree[$i]); /*no it is nesseray */
			}else{
				$tree[$i]['content'] = cleaner($tree[$i]['content']);
				$rtree[] = $tree[$i];
			}
		}else{
			$rtree[] = $tree[$i];
		}
	}
	return $rtree;
}
function painter($tree = array(),$templateDir = 'templates/basic/', $level = 0){
	$cant = count($tree);
	#echo 't' . $cant;
	$return = '';
	for($i = 0; $i< $cant; $i++){
		if(isset($tree[$i]['content'])){
			$dir = new coSimpleTemplate($templateDir . 'dir.tpl');
			$dir->set("name", htmlentities($tree[$i]['name'], ENT_QUOTES, "UTF-8"));
			$dir->set("content", painter($tree[$i]['content'], $templateDir, $level + 1));
			$dir->set('level', $level);
			$return .= $dir->output();;
		}else{
			if(!$tree[$i]['pathname'] == ''){
				$file = new coSimpleTemplate($templateDir . 'file.tpl');
				$file->set('name', htmlentities($tree[$i]['name'], ENT_QUOTES, "UTF-8"));
				$file->set('namenotype', htmlentities($tree[$i]['namenotype'], ENT_QUOTES, "UTF-8"));
				$file->set('nameclean', htmlentities(str_replace('_', ' ', $tree[$i]['namenotype']), ENT_QUOTES, "UTF-8"));
				$file->set('pathname', htmlentities($tree[$i]['pathname'], ENT_QUOTES, "UTF-8") );
				$file->set('filetype', $tree[$i]['type']);
				$file->set('filesize', $tree[$i]['size']);
				$file->set('filesizekb', round($tree[$i]['size']/1024,2));
				$file->set('filesizemb', round($tree[$i]['size']/1024/1024,2));
				$file->set('lastmod', $tree[$i]['lastmod']);
				$file->set('lastmodyear', date('Y',$tree[$i]['lastmod']) );
				$file->set('lastmodmonth', date('m',$tree[$i]['lastmod']) ); 
				$file->set('lastmodmontstring', date('M',$tree[$i]['lastmod']) ); 
				$file->set('lastmodday', date('j',$tree[$i]['lastmod']) ); 
				$file->set('lastmoddaystring', date('l',$tree[$i]['lastmod']) );
				$file->set('lastmodhour', date('G',$tree[$i]['lastmod']) );
				$file->set('lastmodminute', date('i',$tree[$i]['lastmod']) );  
				$file->set('lastmodsecond', date('s',$tree[$i]['lastmod']) );      
				$file->set('level', $level);
				$return .= $file->output();;
			}
		}
	}
	return $return;
}
function copier($templateDir = 'templates/basic/', $basePath = './'){
	$ini_array = parse_ini_file($templateDir . 'manifest.ini', true);
	foreach ($ini_array as $file) {
			$destination = $file['destinationRute'];
			$dirs = explode('/' , $destination);
			$dirs_cant = count($dirs);
			$filepath = $basePath;
			if($dirs_cant > 1){
				for($i = 0; $i< $dirs_cant -1; $i++){
					$filepath .= $dirs[$i] . '/'; 
				}
				if(!is_dir($filepath ) ){
					mkdir($filepath, 0755, TRUE);
				}
			}
			if(file_exists($file['templateRute'])){
				copy($file['templateRute'], $basePath . $file['destinationRute']);
			}else{
				copy($templateDir . $file['templateRute'], $basePath . $file['destinationRute']);
			}
		}
}
function generate($fileName = 'generado.html', $templateDir = 'templates/basic/', $basePath = './', $fileType = 'pdf', $recursive = 10){
	$header = new coSimpleTemplate($templateDir . 'header.tpl');
	#$header->set('value', 'data');
	$footer = new coSimpleTemplate($templateDir . 'footer.tpl');
	$footer->set('copy', 'This page are generated by CoGenerate &copy; 2011');
	$file = fopen($basePath . 'generado.html', 'w');
	$html = $header->output();
	$tree = parser($basePath, $fileType, $recursive, $basePath);
	$cleanTree = cleaner($tree);
	for($i = 0; $i< 5; $i++){
		$cleanTree = cleaner($cleanTree); /*for truely clean the tree*//*WTF? really not know this code*/
	}
	$html .= painter($cleanTree, $templateDir);
	$html .= $footer->output();
	fwrite($file, html_nicer($html));
	fclose($file);
	copier($templateDir, $basePath);
}
function html_nicer($html){
	$clean = $html;
	if (class_exists('tidy')) {
		$config = array(
			'indent'         => true,
			'output-xhtml'   => true,
			'wrap'           => 200);
		$tidy = new tidy;
		$clean = $tidy->repairString($html, $config, 'utf8');
	}
	include_once(@"XML/Beautifier.php");/*TODO off the alert in case of fail*/
	if (class_exists('XML_Beautifier')) {
		$fmt = new XML_Beautifier();
		$options = array(
			"caseFolding"       => true,
			"caseFoldingTo"     => "uppercase",
			"normalizeComments" => true
		);
		$fmt->setOptions($options);
		$fmt->setOption("indent", "\t");
		$tempclean = $fmt->formatString($clean, 'plain');
		if (PEAR::isError($tempclean)) {
			echo $tempclean->getMessage();
			#exit();
		}else {
			$clean = $tempclean;
		}
	}	
	return $clean;
}


class coSimpleTemplate {
	protected $file;
	protected $values = array();
	public function __construct($file) {
		$this->file = $file;
	}
	
	public function set($key, $value) {
		$this->values[$key] = $value;
	}
	
	public function output() {
		if (!file_exists($this->file)) {
			return 'Error loading template file (' .$this->file. ').' . "\r\n";
		}
		$output = file_get_contents($this->file);
		foreach ($this->values as $key => $value) {
			$tag = '[@' . $key . ']';
			$output = str_replace($tag, $value, $output);
		}
		return $output;
	}
}
/*generate('generado.html', 'templates/basic/', basedir, fileType, max_recursive);
var_dump($argv);
var_dump($argc);*/
echo('Usage generador.php fileToGenerate routeToTemplate baseDir fileTypeToCatalog maxRecursive');
$var = array();
if(isset($argv[1])){
	$var[1] = $argv[1];
}else{
	$var[1] = 'generado.html';
}
if(isset($argv[2])){
	$var[2] = $argv[2];
}else{
	$var[2] = 'templates/basic/';
}
if(isset($argv[3])){
	$var[3] = $argv[3];
}else{
	$var[3] = basedir;
}
if(isset($argv[4])){
	$var[4] = $argv[4];
}else{
	$var[4] = fileType;
}
if(isset($argv[5])){
	$var[5] = $argv[5];
}else{
	$var[5] = max_recursive;
}
generate($var[1],$var[2],$var[3],$var[4],$var[5]);
?>