<?
session_start();
$img_arr = array("http://casetamatic.ru/editor_dev/imgs/design/iphone5_113262.png");
$name	= "Cool man";
$author_email = "atanikov@gmail.com";

//url сервиса. Должен быть изменен при переносе сервиса в продакшн
$create_page_script = "http://casetamatic.ru/dev/cr_case32.php";

//кодирование параметров в base64
$imgs64 		= base64_encode(implode(",",$img_arr));
$author_email64 = base64_encode($author_email);
$name64 		= base64_encode($name);

//session_start();
unset($_SESSION['imgs'],$_SESSION['name'],$_SESSION['author_email']);
$_SESSION['imgs'] = $imgs64;
$_SESSION['author_email'] = $author_email64;
$_SESSION['name'] = $name64;
session_write_close();


//Debug info
ini_set('display_errors',1);
error_reporting(E_ALL);
//echo " cr-case32 session_id: ".session_id();
//print_r($_SESSION);


define('DRUPAL_ROOT', getcwd()); //адрес папки с друпалом на сервере
define('CASE_IMG_DIR','case_img'); //название папки для изображений
define('MODELS',"iPhone 5,iPhone 4/4s, Galaxy S III"); //испльзуем для создания разных продукутов
define('COLORS',"white,black"); //испльзуем для создания разных продукутов
define('PRICE',"1190");
//define('site_root',"casetamatic");
//require_once '../casetamatic/includes/bootstrap.inc'; //для API друпала
//require_once '../includes/file.inc'; //для работы с файлами
$document_root = $_SERVER['DOCUMENT_ROOT'];
//!!!!!!!!! ATTENTION !!!!!!!!!!!! CHANGE THIS FOLDER IN PRODUCTION ENV
$drupal_folder = "/dev";
require_once $document_root.$drupal_folder.'/includes/bootstrap.inc'; //для API друпала
require_once $document_root.$drupal_folder.'/includes/file.inc'; //для работы с файлами
define('DRUPAL_TMP_DIR', variable_get('file_directory_temp', '/tmp')); //определить адрес временной папки друпала. если не определена - временная папка сервера


//если редактор сохряняет картинки не на сервере друпала
function save_imgs_to_tmp_dir($save_arr){
	$imgs = explode(",",$save_arr["imgs_str"]);
	foreach($imgs as $img){
		$tmp_file_name = drupal_tempnam(DRUPAL_ROOT.DRUPAL_TMP_DIR,""); //генерит уникальное для временной папки имя файла
		$tmp_file_arr  = pathinfo($tmp_file_name);
		$tmp_file_name = $tmp_file_arr['filename']; 
		$src_file_arr  = pathinfo($img);
		//print_r($src_file_arr); //раскоментить для отладки
		$src_file_ext  = strtolower($src_file_arr['extension']); 
		//сформирвали арес с уникальным именем файла для сохранения во временной папке
		$fullpath = DRUPAL_ROOT.DRUPAL_TMP_DIR."/".$tmp_file_name.".".$src_file_ext; 
		//echo "Временный файл ".$fullpath; //раскоментить для отладки
		//копирование файла
		$ch = curl_init($img);
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_BINARYTRANSFER,1); 
		$rawdata = curl_exec($ch); 
		curl_close ($ch);	 
		if(file_exists($fullpath)){
			unlink($fullpath); //deletes file if exists with same name
			}
		file_put_contents($fullpath, $rawdata);
		//конец копирования файла
		//дописываем адрес сохраненного изображения в массив
		$tmp_imgs_arr[] = $fullpath; //массив адресов файлов для передачи в новую ноду
		}
	return 	$tmp_imgs_arr;
	}

function create_product($save_arr,$model,$color){
	
	$node = new stdClass(); 
	$node->type = 'case_prod_type';  //тип материала ноды
	node_object_prepare($node);
	$node->language = LANGUAGE_NONE;
	$node->title = $save_arr["title"]." ".$model." ".$color; //заголовок ноды
	//$node->body = 'body text'; 
	
	//$node->language ='ru'; //код языка ноды
	global $user;
	$user = user_load_by_mail($save_arr["email"]); //находим юзера по email и создаем объект
	$node-> uid = 1;//$user->uid; //ID пользователя ноды
	foreach ($save_arr["tmp_imgs_arr"] as $filepath) {
		// Create a File object
		$file_path = drupal_realpath($filepath); 
		$file = (object) array(
			  'uid' => 1, //заменить на реальный user id
			  'uri' => $file_path, //полный адрес файла во временной папке
			  'filemime' => file_get_mimetype($filepath),
			  'status' => 1,
			); 
		//для картинок каждого пользователя создается своя папка с именем = id пользователия
		$case_img_dir_path = 'public://'.CASE_IMG_DIR;
		$case_img_user_dir_path = 'public://'.CASE_IMG_DIR."/".$user->uid;
		file_prepare_directory($case_img_dir_path, FILE_CREATE_DIRECTORY); //создать папку для хранения изображений, если не существует
		file_prepare_directory($case_img_user_dir_path, FILE_CREATE_DIRECTORY); //создать папку для изображений пользователя
		//сохранение файла в папку пользователя
		$file = file_copy($file,'public://'.CASE_IMG_DIR."/".$user->uid); // Save the file to the root of the files directory. You can specify a subdirectory, for example, 'public://images' 
		//запись в поле картинки ссылки на файд
		$node->field_case_image[LANGUAGE_NONE][] = (array)$file; 
		}
	$node-> field_device     = $model;
	$node-> field_case_color = $color;
	$node-> commerce_price[]	 = $save_arr["price"];
	//загрузка контента 	
	//$node = node_submit($node); node_save($node);
	
	//$aaa = node_save($node);
	//node_submit($node);
	//var_dump ($aaa);
	//$bbb = node_submit($node);
	//node_submit($node);
	//var_dump ($bbb);
	
	if($node_submit = node_submit($node)) { // Prepare node for saving
		//сохранение загруженного контента
		node_save($node_submit);
		return $node->nid;
		}
	
	}
	
//создание ноды с референсами к продуктам
function create_product_view($save_arr){
	
	//вызываем функцию создания продукта с параметрами: картинка, название, цвет, модель
	//в название вставляем title+модель+цвет
	//создаем ноду
	//в поле product_reference вставляем id созданных продуктов
	
	$node = new stdClass(); 
	$node->title = $save_arr["title"]; //заголовок ноды
	//$node->body = 'body text'; 
	$node->type = 'case_display';  //тип материала ноды
	$node->language ='ru'; //код языка ноды
	global $user;
	$user = user_load_by_mail($save_arr["email"]); //находим юзера по email и создаем объект
	$node-> uid = $user->uid; //ID пользователя ноды
	//распарсиваем и удаляем пробелы в начале и в конце у MODELS, COLORS
	$models = explode(",",MODELS);
	$colors = explode(",",COLORS);
	
	foreach($models as $model){
		foreach($colors as $color){
			$node->field_case_product['und'][0]['product_id'] = create_product($save_arr,$model,$color);
			//$node->field_case_product['und'][1]['product_id'] = 1;
			}
		}
		
	//загрузка контента 	
	//$node = node_submit($node); node_save($node);
	if($node = node_submit($node)) { // Prepare node for saving
		//сохранение загруженного контента
		node_save($node);
		return $node->nid;
		}
	}	
	
//начало программы	
if (isset($_SESSION['imgs'])) 
	{
	$save_arr["imgs_str"] = base64_decode($_SESSION['imgs']);
	}
else {die ("no imgs");}
if (isset($_SESSION['name']))
	{
	$save_arr["title"]    = base64_decode($_SESSION['name']);	
	}
else {$save_arr["title"] = "Design";}
if (isset($_SESSION['author_email']))
	{
	$save_arr["email"]    = base64_decode($_SESSION['author_email']);
	}
else {die ("no email");}	

unset($_SESSION['imgs'],$_SESSION['author_email'],$_SESSION['name']); 

drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL); //включает API друпала
//копирование картинок с адресов, указанных внешним редактором во временную папку друпала
$save_arr["tmp_imgs_arr"]=save_imgs_to_tmp_dir($save_arr);
$save_arr["price"]=PRICE;
echo "<br/>Debug_ "; print_r($save_arr); //раскомментить для отладки
echo "<br/>";

//нужно для каждого телефона и цвета создать отдельный продукт
$node_id = create_product_view($save_arr);
$nodeurl = url('node/'. $node_id);
//$nodeurl = "ololo";
//конец программы	

?>
<? 
//header('Location: http://localhost/drupal/#overlay=admin/content');
?>
<a id="created_url" href="<? echo $nodeurl;?>"><? echo $save_arr["title"] ?></a>