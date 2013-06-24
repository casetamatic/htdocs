<?php
// --------------------------- Старт сессии
//session_cache_limiter('public');
//session_cache_expire(60);
session_start();

// --------------------------- Установка лимитов
set_time_limit(30);
error_reporting(E_ALL);
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
// --------------------------- Константы
define('BASE_PATH', './');
define('IMP_PHP_PATH', BASE_PATH.'include/php/');
define('IMP_UPLOAD_PATH', BASE_PATH.'imgs/upload/');
define('IMP_DESIGN_PATH', BASE_PATH.'imgs/design/');
// --------------------------- Функция создания страницы
function create_page($img_arr, $name, $author_email){
    //url сервиса. Должен быть изменен при переносе сервиса в продакшн
    $create_page_script = "http://casetamatic.ru/dev/cr_case32.php";
	/*
	var_dump($img_arr);
	echo "<br/>";
	var_dump($name);
	echo "<br/>";
	var_dump($author_email);
	echo "<br/>";
    */
	/*
	//кодирование параметров в base64
    $imgs64 		= base64_encode(implode(",",$img_arr));
    $author_email64 = base64_encode($author_email);
    $name64 		= base64_encode($name);

    //session_start();
    unset($_SESSION['imgs'],$_SESSION['name'],$_SESSION['author_email']);
    $_SESSION['imgs'] = $imgs64;
    $_SESSION['author_email'] = $author_email64;
    $_SESSION['name'] = $name64;
    //session_write_close();

    //вызов сервиса и получение ответа
    $ch = curl_init($create_page_script);
    curl_setopt ($ch, CURLOPT_COOKIE, "PHPSESSID=".session_id());
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
    $rawdata = curl_exec($ch); //ответ сервиса
    curl_close ($ch);
    unset($img_arr, $name, $author_email);
    */
	$rawdata = "text";
	return $rawdata;
}
// --------------------------- Функция
function image_placeholder( $placeholder, $img ){
    /*
     Array
    (
        [img_name] => img-942644-1370052198
        [img_path_2560] => ./imgs/upload/img-942644-1370052198_2560.jpg
        [img_path_400] => ./imgs/upload/img-942644-1370052198_400.jpg
        [img_path_150] => ./imgs/upload/img-942644-1370052198_150.jpg
        [id] => 137005219915
    )
    Array
    (
        [id] => 137005219915
        [top] => 0
        [left] => -338
        [scale] => 1.8285714285714285
        [width] => 282
        [height] => 560
    )
     */
    // Определение переменных
    $img_path = IMP_DESIGN_PATH.$_POST['device']['template'].'_'.mt_rand(0,1000000);
    $new_w = $placeholder['width'];
    $new_h = $placeholder['height'];
    $src_x = $placeholder['left'] == 0? 0 : abs( $placeholder['left'] );
    $src_y = $placeholder['top'] == 0? 0 : abs( $placeholder['top'] );
    $scale = $placeholder['scale'];
    // Исходный дискриптор изображения
    $img_resor = WideImage::load( $img['img_path_2560'] );
    // Определение нового размера
    $img_w = ceil( $img_resor->getWidth() / $scale );
    $img_h = ceil( $img_resor->getHeight() / $scale );
    // Масштабирование исходного изоражения
    $img_resor->resize( $img_w, $img_h, 'outside' )->saveToFile( $img_path.'_resize.png' );
    // Вырезаем нужный фрагмент с масштабированного изображения
    $img_dest = WideImage::load( $img_path.'_resize.png' )->crop( $src_x, $src_y, $new_w, $new_h);
    // Сохраняет изображение для печати
    $img_dest->saveToFile( $img_path.'_print.png' );
    // Накладываем маски и сохраняем изображения для сайта
    $mask = WideImage::load( BASE_PATH.'include/img/mask-'. $_POST['device']['template'] .'-php.png' )->getMask();
    $img_overlay = WideImage::load( BASE_PATH.'include/img/overlay-'. $_POST['device']['template'] .'.png' );
    $img_dest->applyMask($mask)->merge( $img_overlay )->saveToFile( $img_path.'.png' );
    // Удаляем временное изображение
    unlink( $img_path.'_resize.png' );
    //
    return $img_path;
    /*
    // Временный исходный дискриптор изображения
    $img_temp = imagecreatefromstring( file_get_contents($img['img_path_2560']) );
    // Дискриптор конечного файла
    $img_dest = imagecreatetruecolor($new_w, $new_h);
    $img_dest2 = imagecreatetruecolor($new_w, $new_h);
    // Определение исходного размера фотки
    $img_temp_w = imagesx($img_temp);
    $img_temp_h = imagesy($img_temp);
    // Определение нового размера
    $img_w = ceil( $img_temp_w / $scale );
    $img_h = ceil( $img_temp_h / $scale );
    // Исходный дискриптор изображения
    $img_resor = imagecreatetruecolor( $img_w, $img_h );
    // Масштабирование исходного изоражения
    imagecopyresized( $img_resor, $img_temp, 0, 0, 0, 0, $img_w, $img_h, $img_temp_w, $img_temp_h);
    // Копирование части изображения
    imagecopy( $img_dest , $img_resor , 0 , 0, $src_x, $src_y, $new_w, $new_h );
    // Сохранение изображения
    imagepng($img_dest, $img_path.'_print.png', 0, PNG_ALL_FILTERS);
    // Apply mask to source
    $img_mask = imagecreatefromstring(file_get_contents( BASE_PATH.'include/img/mask-'. $_POST['device']['template'] .'-php.png' ));
    //$img_mask = WideImage::load($data);
    //imagealphamask( $img_dest2, $img_mask );
    $mask = WideImage::load( BASE_PATH.'include/img/mask-'. $_POST['device']['template'] .'-php.png' )->getMask();
    $img_overlay = WideImage::load( BASE_PATH.'include/img/overlay-'. $_POST['device']['template'] .'.png' );
    WideImage::load($img_dest)->applyMask($mask)->merge( $img_overlay )->saveToFile( $img_path.'.png' );*/
    /*$dude = new Imagick($img_dest2);
    $mask = new Imagick($img_mask);
    $dude->setImageMatte(1);
    $dude->compositeImage($mask, Imagick::COMPOSITE_DSTIN, 0, 0);
    $dude->writeImage( $img_path.'.png' );*/
    //imagepng($img_dest2, $img_path.'.png', 0, PNG_ALL_FILTERS);
    // Удаление дискрипторов
    //imagedestroy($img_temp);
    //imagedestroy($img_resor);
    //imagedestroy($img_dest);
    //imagedestroy($img_dest2);
}
//
//print_r( $_SESSION );

if( $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'){

    if( isset($_GET['last_upload_img']) ){
        // Создание пустого массива загруженных фоток, если нужно
        if( !isset($_SESSION['IMGS_UPLOAD']) )
            $_SESSION['IMGS_UPLOAD'] = array();
        // Определение id картинки
        $_SESSION['FILE_UPLOAD']['id'] = time().mt_rand(0,100);
        // Запись новой картинки
        array_unshift( $_SESSION['IMGS_UPLOAD'], $_SESSION['FILE_UPLOAD'] );
        // Вывод JSON'а с данными картинки
        echo json_encode($_SESSION['FILE_UPLOAD']);
        // Удаление вресенных записей о занруженном файле
        if( isset($_SESSION['FILE_UPLOAD']) )
            unset($_SESSION['FILE_UPLOAD']);
    }
    //
    if( isset($_GET['action']) ){
        // Возврат
        if( $_GET['action'] == "get_imgs" ){
            $imgs = isset($_SESSION['IMGS_UPLOAD'])? $_SESSION['IMGS_UPLOAD'] : array();
            if( !is_array($imgs) ){
                $imgs = array();
            }
            krsort($imgs);
            // Подстановка индификаторов
            /*$count = 0;
            foreach($imgs as $key => $img_obj ){
                $imgs[$key]['id'] = $count++;
            }*/
            // Вывод JSON'а с данными фоток
            echo json_encode( $imgs );
        }
        //
        if( $_GET['action'] == "remove_img" && isset($_GET['img_id']) ){
            // Удаление фотки
            foreach( $_SESSION['IMGS_UPLOAD'] as $key => $img_obj ){
                if( $img_obj['id'] == $_GET['img_id'] ){
                    unlink( $img_obj['img_path_2560'] );
                    unlink( $img_obj['img_path_400'] );
                    unlink( $img_obj['img_path_150'] );
                    //
                    unset( $_SESSION['IMGS_UPLOAD'][ $key ] );
                }
            }
            // Подстановка индификаторов
            /*$imgs = $_SESSION['IMGS_UPLOAD'];
            $count = 0;
            foreach($imgs as $key => $img_obj ){
                $imgs[$key]['id'] = $count++;
            }*/
            // Вывод JSON'а с данными фоток
            echo json_encode( $_SESSION['IMGS_UPLOAD'] );
        }
    }
    //
    if( isset($_POST['save_design']) && isset($_POST['placeholders']) ){
        // Подключаем библиотеку WideImage
        require_once IMP_PHP_PATH.'/WideImage/WideImage.php';
        //
        $img_arr = array();
        $name = 'Nikolay Tester';
        //$author_email = 'testing@gmail.com';
		$author_email = 'atanikov@gmail.com';
        //
        foreach( $_POST['placeholders'] as $placeholder ){
            foreach( $_SESSION['IMGS_UPLOAD'] as $img ){
                if( $img['id'] == $placeholder['id'] ){
                    $img_arr[] = $img['img_path_2560'];
                    //print_r( $img );
                    //print_r( $placeholder );
                    $img_arr[] = image_placeholder( $placeholder, $img );
                }
            }
        }
        //
		
        $created_page_url = create_page($img_arr, $name, $author_email);
        unset($img_arr, $name, $author_email);
        //
        echo '{"save": "'. $created_page_url .'"}';
    }
}