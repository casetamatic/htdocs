<?
//Редактор картинок
//при нажатии кнопки Save
//сохраняет картинки куда-либо
//вызывает сервис создания страниц на основе созданных в редакторе $create_page_script и передает ему параметры
// imgs - строка с url картинок, разделенные запятой, кодированная base64_encode()
// author_email - строка с email, под которым авторизован автор картинки, кодированная в base64_encode()
// name - строка-название дизайна чехла, кодированая в base64_encode()
//сервис создания cтраниц возвращает:
// - url страницы 
// - "no imgs", если не получены url траниц
// - "no email", если не получен email автора страницы
// - "not authorized", если пользователь с переданным author_email не авторизован на сайте для создания страницы
// - "email not found", если польозватель с таким email не найден

//Sample values
$img_arr = array(
	"http://localhost/my/img/img2.png",
	"http://localhost/my/img/img1.png"
	);
$name = "Trol design на русском";
$author_email = "atanikov+test@gmail.com";
//End Sample values

//функция вызова сервиса, создающего страницу
function create_page($img_arr, $name, $author_email)
{
//url сервиса. Должен быть изменен при переносе сервиса в продакшн
$create_page_script = "http://casetamatic.ru/dev/cr_case32.php";

//кодирование параметров в base64
$imgs64 		= base64_encode(implode(",",$img_arr));
$author_email64 = base64_encode($author_email);
$name64 		= base64_encode($name);

session_start();
unset($_SESSION['imgs'],$_SESSION['name'],$_SESSION['author_email']);
$_SESSION['imgs'] = $imgs64;
$_SESSION['author_email'] = $author_email64;
$_SESSION['name'] = $name64;
session_write_close();

//вызов сервиса и получение ответа
$ch = curl_init($create_page_script);
curl_setopt ($ch, CURLOPT_COOKIE, "PHPSESSID=".session_id()); 
curl_setopt($ch, CURLOPT_HEADER, 0); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_BINARYTRANSFER,1); 
$rawdata = curl_exec($ch); //ответ сервиса
curl_close ($ch);			
unset($img_arr, $name, $author_email);
return $rawdata;
}
//конец функции создания страницы


//ответ сервиса (либо url страницы в виде гиперссылки либо код ошибки в UTF-8 без BOM)
$created_page_url = create_page($img_arr, $name, $author_email);
unset($img_arr, $name, $author_email);

//вывод для отладки
echo $created_page_url;
?>