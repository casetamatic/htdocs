<?php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);

//данные из редактора//
$save_arr = array (
			'title' => "Cool man",
			'email' => "atanikov@gmail.com",
			'user_id' => 1, //получаем в результате авторизации в сайте
			'print_img' => "http://casetamatic.ru/editor_dev/imgs/design/iphone5_9481_print.png",
			'web_imgs' => array (
							'iPhone 5' => array (
												'design_img' => "http://casetamatic.ru/editor_dev/imgs/design/iphone5_9481.png",			
												),
							'iPhone 4/4S' => array (
												'design_img' => "http://casetamatic.ru/editor_dev/imgs/design/iphone5_9481.png",			
												),										
							),
			);
		
$json_save_arr = json_encode($save_arr);
unset($_SESSION['save_arr']);
$_SESSION['save_arr'] = json_encode($save_arr);
session_write_close();
//конец данных из редактора


//начало программы 
//скрипт для сохранения дизайна в друпал-сайте
define('DRUPAL_ROOT', getcwd());
//$_SERVER['REMOTE_ADDR'] = "localhost"; // Necessary if running from command line
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
require_once DRUPAL_ROOT . '/includes/file.inc'; //для работы с файлами
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
define('DRUPAL_TMP_DIR', variable_get('file_directory_temp', '/tmp')); //определить адрес временной папки друпала. если не определена - временная папка сервера
define('CASE_IMG_DIR','case_img'); //название папки для изображений	
define('CASE_PROD_TYPE',"case_prod_type");
define('PRICE',1990);
//адреса картинок с цветом для каждой модели
//название модели и цвет в точности как в поле Body термина таксономии
$model_color_imgs = array (
					'iPhone 5' => array (
									'white' => "sites/default/files/case_color_frosty_white_m_side.png",
									'black' => "sites/default/files/case_color_frosty_charcoal_m_side.png",
									),
					'iPhone 4/4S' => array (
									'white' => "sites/default/files/case_color_frosty_white_m_side.png",
									'black' => "sites/default/files/case_color_frosty_charcoal_m_side.png",
									),
						);

//определение функций

//создание commerce product
function create_product_case($save_arr,$model,$color,$product_imgs){
		//массив для хранения цены и валюты в формате Drupal Commerce 7
		$form_state = array (
						'values' => array (
										'price' => PRICE*100 , //непонятно зачем нужно
										'currency_code' => commerce_default_currency() , //также непонятно зачем нужно
										'commerce_price'=> array (
															LANGUAGE_NONE => array (
																				0 => array (
																						'amount' => PRICE*100  ,
																						'currency_code' => commerce_default_currency(),
																							) 
																				) 
																)
										)
						);
		//конец массив для цены

		$form = array ();
		$form[ '#parents' ] = array ();
		$user_id = user_load_by_mail($save_arr["email"])->uid;
		// Generate a new product object
		$new_product = commerce_product_new (CASE_PROD_TYPE);

		//таксономия устройство
		$vocabulary=taxonomy_vocabulary_machine_name_load("device");
		$terms = taxonomy_get_tree($vocabulary->vid);
		foreach($terms as $term) {
			if($term->description == $model) { 
			 $new_product->field_device[LANGUAGE_NONE][]['tid'] = $term->tid;
			}
		}
		//устройство
		//таксономия цвет
		$vocabulary=taxonomy_vocabulary_machine_name_load("case_colors");
		$terms = taxonomy_get_tree($vocabulary->vid);
		foreach($terms as $term) {
			if($term->description == $color) {
			echo $color. "<br/>";
			 $new_product->field_case_color[LANGUAGE_NONE][]['tid'] = $term->tid;
			 print_r($term);
			 
			 $name = $term->name;
			 echo $name;
			}
		}
		//цвет

		$new_product->status 			= 1;
		$new_product->uid 				= $user_id;	
		$new_product->sku 				= $save_arr["title"]."-".$model."-".$color."-".rand(0,10000);
		$new_product->title 			= $save_arr["title"]." ".$model." ".$name;

		echo "title : ".$new_product->title;
		echo "<br/><br/>";
		$new_product->created 			= $new_product->changed = time ();

		//неведомая
		if ( ! empty( $values[ 'original_order' ] ) ) {
			// field_original_order[und][0][target_id]
			$order = array ( LANGUAGE_NONE => array ( 0 => array ( 'target_id' => $values[ 'original_order' ] ) ) );
			$form_state[ 'values' ][ 'field_original_order' ] = $order;
		}

		if ( ! empty( $values[ 'original_line_item' ] ) ) {
			// field_original_line_item[und][0][target_id]
			$line_item = array ( LANGUAGE_NONE => array ( 0 => array ( 'target_id' => $values[ 'original_line_item' ] ) ) );
			$form_state[ 'values' ][ 'field_original_line_item' ] = $line_item;
		}

		if ( ! empty( $values[ 'original_product' ] ) ) {
			$product = array ( LANGUAGE_NONE => array ( 0 => array ( 'target_id' => $values[ 'original_product' ] ) ) );
			$form_state[ 'values' ][ 'field_original_product' ] = $product;
		}
		//конец неведомой

		//поле img_field с множеством картинок
		foreach ($product_imgs as $filepath) {
			// Create a File object
			$file_path = drupal_realpath($filepath); 
			$file = (object) array(
				  'uid' => $user_id, //
				  'uri' => $file_path, //полный адрес файла во временной папке
				  'filemime' => file_get_mimetype($filepath),
				  'status' => 1,
				); 
			//для картинок каждого пользователя создается своя папка с именем = id пользователия
			$case_img_dir_path = 'public://'.CASE_IMG_DIR;
			$case_img_user_dir_path = 'public://'.CASE_IMG_DIR."/".$user_id;
			file_prepare_directory($case_img_dir_path, FILE_CREATE_DIRECTORY); //создать папку для хранения изображений, если не существует
			file_prepare_directory($case_img_user_dir_path, FILE_CREATE_DIRECTORY); //создать папку для изображений пользователя
			//сохранение файла в папку пользователя
			$file = file_copy($file,'public://'.CASE_IMG_DIR."/".$user_id); // Save the file to the root of the files directory. You can specify a subdirectory, for example, 'public://images' 
			//запись в поле картинки ссылки на файд
			$new_product->field_case_image[LANGUAGE_NONE][] = (array)$file; 
			}
		//конец поля с картинками	

		// Notify field widgets to save their field data
		field_attach_submit ( 'commerce_product' , $new_product , $form , $form_state );

		commerce_product_save ( $new_product );
		return $new_product->product_id;
		}
//конец функции создание Commerce Product
	
//получение данных из сессии
if (isset($_SESSION['save_arr']))
	{
	//раскодировать полученный JSONчик
	$save_arr = json_decode($json_save_arr, true);
	}
else {die ("no json");}	
unset($_SESSION['save_arr']); 
//конец получения данных из сессии

//добавить изображения цветов для каждой модели в массив для сохранения
foreach ($save_arr['web_imgs'] as $save_arr_model => $imgs){
	foreach($model_color_imgs as $model_color => $color_imgs){
		if  ($save_arr_model == $model_color) {
			$save_arr['web_imgs'][$save_arr_model]['color_imgs'] = $color_imgs;
			//print_r($save_arr['web_imgs'][$save_arr_model]); echo "<br/>";
			break;
			}
	}
}	
//конец добавления картинок цвета товара

//Скачиваем картинки и сохраняем во временную папку. если редактор сохряняет картинки не на сервере друпала
foreach ($save_arr['web_imgs'] as $model => $model_imgs){
	$tmp_file_name = drupal_tempnam(DRUPAL_ROOT.DRUPAL_TMP_DIR,""); //генерит уникальное для временной папки имя файла
	$tmp_file_arr  = pathinfo($tmp_file_name);
	$tmp_file_name = $tmp_file_arr['filename']; 
	$src_file_arr  = pathinfo($model_imgs['design_img']);
	//print_r($src_file_arr); //раскоментить для отладки
	$src_file_ext  = strtolower($src_file_arr['extension']); 
	//сформирвали арес с уникальным именем файла для сохранения во временной папке
	$fullpath = DRUPAL_ROOT.DRUPAL_TMP_DIR."/".$tmp_file_name.".".$src_file_ext; 
	//echo "Временный файл ".$fullpath; //раскоментить для отладки
	//копирование файла
	$ch = curl_init($model_imgs['design_img']);
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
	$save_arr['web_imgs'][$model]['design_img'];
	}
//конец скачивания картинок и сохранения во временную папку	

//в результате должен получиться вот такой массив для сохранения
//DEBUG
echo "111";
print_r($save_arr);
echo "222";
//end DEBUG


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
	
//создание commerce products для каждого сочетания модели-цвета
//перебираем каждую модель в переданном массиве
foreach($save_arr['web_imgs'] as $model => $model_imgs) {
	//для каждого из цветов модели создаем товар с 2 картинками: картинкой этого цвета и картинкой модели
	foreach ($model_imgs['color_imgs'] as $color => $color_img) {
		$product_imgs[0]=$model_imgs['design_img']; //картинка с дизайном $model_imgs['design_img'];// эта картинка будет выводиться на карточке товара первой -> основной
		$product_imgs[1]= $color_img;//картинка с цветом модели 
		//создаем commerce_product
		echo "debug: ";
		print_r ($model);
		print_r ($color);
		print_r ($product_imgs);
		$product_id = create_product_case($save_arr,$model,$color,$product_imgs);
		echo "node id: ".$product_id."<br/>";
		$node->field_case_product[LANGUAGE_NONE][]['product_id'] = $product_id; 
		}
	}
//загрузка контента 	
if($node = node_submit($node)) { // Prepare node for saving
	//сохранение загруженного контента
	node_save($node);
	$nodeurl = url('node/'. $node->nid);
	}
//конец создания ноды	
?>
<a id="created_url" href="<? echo $nodeurl;?>"><? echo $save_arr["title"] ?></a>