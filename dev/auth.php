<?
session_start();
error_reporting(E_ALL);

ini_set('display_errors', 1);
/*
$ch = curl_init("http://casetamatic.ru/dev/user/login");
//curl_setopt ($ch, CURLOPT_COOKIE, "PHPSESSID=".session_id()); 
curl_setopt($ch, CURLOPT_HEADER, 0); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
curl_setopt($ch, CURLOPT_BINARYTRANSFER,1); 
$rawdata = curl_exec($ch); //ответ сервиса
curl_close ($ch);			
echo $rawdata;
*/

define('DRUPAL_ROOT', $_SERVER['DOCUMENT_ROOT']."/dev");
echo DRUPAL_ROOT;


$base_url = 'http://'.$_SERVER['HTTP_HOST'];
include_once DRUPAL_ROOT . '/includes/bootstrap.inc';
chdir(DRUPAL_ROOT);
// Bootstrap merely for session purposes:
#drupal_bootstrap(DRUPAL_BOOTSTRAP_SESSION);
// Bootstrap for all purposes (e.g. theme() function):
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL); 
echo "<br/>USER: <br/>";
global $user;
print_r($user);
if ($user->uid = 0)
 {echo "login please";}
else {echo "logged in".$user->login;}
	 

	// config and includes
   	$config = /*dirname(__FILE__) */DRUPAL_ROOT. '/sites/all/libraries/hybridauth/hybridauth/config.php';
    require_once( DRUPAL_ROOT."/sites/all/libraries/hybridauth/hybridauth/Hybrid/Auth.php" );


		try{
			$hybridauth = new Hybrid_Auth( $config );

			$adapter = $hybridauth->authenticate( "facebook" );

			$user_profile = $adapter->getUserProfile();
			}
		catch( Exception $e ){
			die( "<b>got an error!</b> " . $e->getMessage() ); 
		}
	

		
		echo "<pre>" . print_r( $user_profile, true ) . "</pre><br />";
		 $data['identifier'] = $user_profile->$identifier;
		 $data['provider']   = "Facebook";
/*
если 		 $data['identifier'] = $user_profile->$identifier;
		 $data['provider']   = "Facebook";
		 не существует в базе
		 создать запись в Hybriauth и в user
*/

function _hybridauth_identity_load($data) {
  $result = db_select('hybridauth_identity', 'ha_id')
    ->fields('ha_id')
    ->condition('provider', $data['provider'], '=')
    ->condition('provider_identifier', $data['identifier'], '=')
    ->execute()
    ->fetchAssoc();
  return $result;
}
$ha_id_result = _hybridauth_identity_load($data);
print ($ha_id_result);
/*
$account = user_load($uid)
echo $account();
*/
?>