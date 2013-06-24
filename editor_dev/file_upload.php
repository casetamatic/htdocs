<?php
// --------------------------- Установка лимитов
set_time_limit(100);
ini_set('memory_limit', '128M');
ini_set('upload_max_filesize', '30M');
ini_set('post_max_size', '30M');
error_reporting(E_ALL);

// --------------------------- Старт сессии
//session_cache_expire(60);
session_start();

// --------------------------- Заголовки ответа
header ("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");

// --------------------------- Константы
define('BASE_PATH', './');
define('IMP_PHP_PATH', BASE_PATH.'include/php/');
define('IMP_UPLOAD_PATH', BASE_PATH.'imgs/upload/');

//phpinfo();
print_r($_FILES);
// --------------------------- Загрузка картинки
if( isset($_FILES['fileUploader']) ){

    if( !file_exists( $_FILES['fileUploader']['tmp_name']) ){
        $_SESSION['FILE_UPLOAD']['error'] = $_FILES['fileUploader']['error'];
    }else{
        // Подключаем библиотеку WideImage
        require_once IMP_PHP_PATH.'/WideImage/WideImage.php';
        // Создание название файла
        $img_name = 'img-'.mt_rand(0,1000000).'-'.time();
        $img_path = IMP_UPLOAD_PATH.$img_name;
        //
        $img_upload = WideImage::loadFromUpload('fileUploader');
        // Определение исходного размера фотки
        $img_w = $img_upload->getWidth();
        $img_h = $img_upload->getHeight();
        // Определение максимальной стороны
        $max_w = $img_w > $img_h;
        // Определение пропорции
        $ratio = $max_w? $img_h / $img_w : $img_w / $img_h;
        // Масштабирование до 2560 или максимальный размер оригинала, если меньше
        if( $max_w ){
            if( $img_w <= 2560 ){
                $new_w = $img_w;
                $new_h = $img_h;
            }else{
                $new_w = 2560;
                $new_h = ceil(2560 * $ratio);
            }
        }else{
            if( $img_h <= 2560 ){
                $new_w = $img_w;
                $new_h = $img_h;
            }else{
                $new_h = 2560;
                $new_w = ceil(2560 * $ratio);
            }
        }
        $img_2560 = $img_upload->resize( $new_w, $new_h );
        $img_2560->saveToFile( $img_path.'_2560.jpg' );
        // Масштабирование до 400x400
        if( $max_w ){
            $new_w = 400;
            $new_h = ceil(400 * $ratio);
        }else{
            $new_w = ceil(400 * $ratio);
            $new_h = 400;
        }
        $img_400 = $img_upload->resize( $new_w, $new_h );
        $img_400->saveToFile(  $img_path.'_400.jpg' );
        // Масштабирование до 150x150
        if( $max_w ){
            $img_150 = $img_upload->resize( 'center', 150 )->crop( 'center', 'center', 150, 150 );
        }else{
            $img_150 = $img_upload->resize( 150, 'center' )->crop( 'center', 'center', 150, 150 );
        }
        $img_150->saveToFile(  $img_path.'_150.jpg' );
        // Добавление в сессию данных
        $_SESSION['FILE_UPLOAD'] = array(
            'img_name'=>$img_name,
            'img_path_2560'=>$img_path.'_2560.jpg',
            'img_path_400'=>$img_path.'_400.jpg',
            'img_path_150'=>$img_path.'_150.jpg'
        );
    }
}