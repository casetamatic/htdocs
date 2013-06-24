<?php
session_start();
ini_set('display_errors',1);
error_reporting(E_ALL);

//������ �� ���������//
$save_arr = array (
			'title' => "Cool man",
			'email' => "atanikov@gmail.com",
			'user_id' => 1, //�������� � ���������� ����������� � �����
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
//����� ������ �� ���������


//������ ��������� 
//������ ��� ���������� ������� � ������-�����
define('DRUPAL_ROOT', getcwd());
//$_SERVER['REMOTE_ADDR'] = "localhost"; // Necessary if running from command line
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
require_once DRUPAL_ROOT . '/includes/file.inc'; //��� ������ � �������
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
define('DRUPAL_TMP_DIR', variable_get('file_directory_temp', '/tmp')); //���������� ����� ��������� ����� �������. ���� �� ���������� - ��������� ����� �������
define('CASE_IMG_DIR','case_img'); //�������� ����� ��� �����������	
define('CASE_PROD_TYPE',"case_prod_type");
define('PRICE',1990);
//������ �������� � ������ ��� ������ ������
//�������� ������ � ���� � �������� ��� � ���� Body ������� ����������
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

//����������� �������

//�������� commerce product
function create_product_case($save_arr,$model,$color,$product_imgs){
		//������ ��� �������� ���� � ������ � ������� Drupal Commerce 7
		$form_state = array (
						'values' => array (
										'price' => PRICE*100 , //��������� ����� �����
										'currency_code' => commerce_default_currency() , //����� ��������� ����� �����
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
		//����� ������ ��� ����

		$form = array ();
		$form[ '#parents' ] = array ();
		$user_id = user_load_by_mail($save_arr["email"])->uid;
		// Generate a new product object
		$new_product = commerce_product_new (CASE_PROD_TYPE);

		//���������� ����������
		$vocabulary=taxonomy_vocabulary_machine_name_load("device");
		$terms = taxonomy_get_tree($vocabulary->vid);
		foreach($terms as $term) {
			if($term->description == $model) { 
			 $new_product->field_device[LANGUAGE_NONE][]['tid'] = $term->tid;
			}
		}
		//����������
		//���������� ����
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
		//����

		$new_product->status 			= 1;
		$new_product->uid 				= $user_id;	
		$new_product->sku 				= $save_arr["title"]."-".$model."-".$color."-".rand(0,10000);
		$new_product->title 			= $save_arr["title"]." ".$model." ".$name;

		echo "title : ".$new_product->title;
		echo "<br/><br/>";
		$new_product->created 			= $new_product->changed = time ();

		//���������
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
		//����� ���������

		//���� img_field � ���������� ��������
		foreach ($product_imgs as $filepath) {
			// Create a File object
			$file_path = drupal_realpath($filepath); 
			$file = (object) array(
				  'uid' => $user_id, //
				  'uri' => $file_path, //������ ����� ����� �� ��������� �����
				  'filemime' => file_get_mimetype($filepath),
				  'status' => 1,
				); 
			//��� �������� ������� ������������ ��������� ���� ����� � ������ = id �������������
			$case_img_dir_path = 'public://'.CASE_IMG_DIR;
			$case_img_user_dir_path = 'public://'.CASE_IMG_DIR."/".$user_id;
			file_prepare_directory($case_img_dir_path, FILE_CREATE_DIRECTORY); //������� ����� ��� �������� �����������, ���� �� ����������
			file_prepare_directory($case_img_user_dir_path, FILE_CREATE_DIRECTORY); //������� ����� ��� ����������� ������������
			//���������� ����� � ����� ������������
			$file = file_copy($file,'public://'.CASE_IMG_DIR."/".$user_id); // Save the file to the root of the files directory. You can specify a subdirectory, for example, 'public://images' 
			//������ � ���� �������� ������ �� ����
			$new_product->field_case_image[LANGUAGE_NONE][] = (array)$file; 
			}
		//����� ���� � ����������	

		// Notify field widgets to save their field data
		field_attach_submit ( 'commerce_product' , $new_product , $form , $form_state );

		commerce_product_save ( $new_product );
		return $new_product->product_id;
		}
//����� ������� �������� Commerce Product
	
//��������� ������ �� ������
if (isset($_SESSION['save_arr']))
	{
	//������������� ���������� JSON���
	$save_arr = json_decode($json_save_arr, true);
	}
else {die ("no json");}	
unset($_SESSION['save_arr']); 
//����� ��������� ������ �� ������

//�������� ����������� ������ ��� ������ ������ � ������ ��� ����������
foreach ($save_arr['web_imgs'] as $save_arr_model => $imgs){
	foreach($model_color_imgs as $model_color => $color_imgs){
		if  ($save_arr_model == $model_color) {
			$save_arr['web_imgs'][$save_arr_model]['color_imgs'] = $color_imgs;
			//print_r($save_arr['web_imgs'][$save_arr_model]); echo "<br/>";
			break;
			}
	}
}	
//����� ���������� �������� ����� ������

//��������� �������� � ��������� �� ��������� �����. ���� �������� ��������� �������� �� �� ������� �������
foreach ($save_arr['web_imgs'] as $model => $model_imgs){
	$tmp_file_name = drupal_tempnam(DRUPAL_ROOT.DRUPAL_TMP_DIR,""); //������� ���������� ��� ��������� ����� ��� �����
	$tmp_file_arr  = pathinfo($tmp_file_name);
	$tmp_file_name = $tmp_file_arr['filename']; 
	$src_file_arr  = pathinfo($model_imgs['design_img']);
	//print_r($src_file_arr); //������������ ��� �������
	$src_file_ext  = strtolower($src_file_arr['extension']); 
	//����������� ���� � ���������� ������ ����� ��� ���������� �� ��������� �����
	$fullpath = DRUPAL_ROOT.DRUPAL_TMP_DIR."/".$tmp_file_name.".".$src_file_ext; 
	//echo "��������� ���� ".$fullpath; //������������ ��� �������
	//����������� �����
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
	//����� ����������� �����
	//���������� ����� ������������ ����������� � ������
	$tmp_imgs_arr[] = $fullpath; //������ ������� ������ ��� �������� � ����� ����
	$save_arr['web_imgs'][$model]['design_img'];
	}
//����� ���������� �������� � ���������� �� ��������� �����	

//� ���������� ������ ���������� ��� ����� ������ ��� ����������
//DEBUG
echo "111";
print_r($save_arr);
echo "222";
//end DEBUG


//������� ����
//� ���� product_reference ��������� id ��������� ���������
$node = new stdClass(); 
$node->title = $save_arr["title"]; //��������� ����
//$node->body = 'body text'; 
$node->type = 'case_display';  //��� ��������� ����
$node->language ='ru'; //��� ����� ����
global $user;
$user = user_load_by_mail($save_arr["email"]); //������� ����� �� email � ������� ������
$node-> uid = $user->uid; //ID ������������ ����
	
//�������� commerce products ��� ������� ��������� ������-�����
//���������� ������ ������ � ���������� �������
foreach($save_arr['web_imgs'] as $model => $model_imgs) {
	//��� ������� �� ������ ������ ������� ����� � 2 ����������: ��������� ����� ����� � ��������� ������
	foreach ($model_imgs['color_imgs'] as $color => $color_img) {
		$product_imgs[0]=$model_imgs['design_img']; //�������� � �������� $model_imgs['design_img'];// ��� �������� ����� ���������� �� �������� ������ ������ -> ��������
		$product_imgs[1]= $color_img;//�������� � ������ ������ 
		//������� commerce_product
		echo "debug: ";
		print_r ($model);
		print_r ($color);
		print_r ($product_imgs);
		$product_id = create_product_case($save_arr,$model,$color,$product_imgs);
		echo "node id: ".$product_id."<br/>";
		$node->field_case_product[LANGUAGE_NONE][]['product_id'] = $product_id; 
		}
	}
//�������� �������� 	
if($node = node_submit($node)) { // Prepare node for saving
	//���������� ������������ ��������
	node_save($node);
	$nodeurl = url('node/'. $node->nid);
	}
//����� �������� ����	
?>
<a id="created_url" href="<? echo $nodeurl;?>"><? echo $save_arr["title"] ?></a>