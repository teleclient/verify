<?php // questions? bugs? suggestions? support? => contact t.me/webwarp

declare(strict_types=1);
define('SCRIPT_INFO', 'SESSION V1.0.0'); // <== Do not change!

use \danog\MadelineProto\API;
use \danog\MadelineProto\Logger;
use \danog\MadelineProto\Shutdown;
use \danog\MadelineProto\Tools;

error_reporting(E_ALL);
ini_set('ignore_repeated_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('display_errors',         '1');
ini_set('log_errors',             '1');
ini_set('error_log',              'MadelineProto.log');

includeMadeline('phar');

$data       = \file_get_contents('madeline.madeline');
$apiWrapper = \unserialize($data);
unset($data);

//'API', 'webApiTemplate', 'gettingApiId', 'myTelegramOrgWrapper', 'storage', 'lua', 'async'
// APIWrapper
$mtProto              = Tools::getVar($apiWrapper, 'API');                  // MTProto instance. /*private ?MTProto*/
$webApiTemplate       = Tools::getVar($apiWrapper, 'webApiTemplate');       // Web API template.  /*private  string*/
$gettingApiId         = Tools::getVar($apiWrapper, 'gettingApiId');         // Getting API ID flag.  /*private  bool*/
$myTelegramOrgWrapper = Tools::getVar($apiWrapper, 'myTelegramOrgWrapper'); // My.telegram.org wrapper.  /*?MyTelegramOrgWrapper*/
$storage              = Tools::getVar($apiWrapper, 'storage');              // 
$lua                  = Tools::getVar($apiWrapper, 'lua');                  // Whether lua is being used.  /*private bool*/
$async                = Tools::getVar($apiWrapper, 'async');                // Whether async is enabled.   /*private bool*/
$session              = $apiWrapper->session;                         // Session path. /*public  string*/
$serialized           = Tools::getVar($apiWrapper, 'serialized');     // Serialization date.  /*private int*/
//$factory            = Tools::getVar($apiWrapper, 'factory');        // AbstractAPIFactory instance. /*private AbstractAPIFactory*/

// MTProto
$chats                = $mtProto->chats;
$full_chats           = $mtProto->full_chats;
$referenceDatabase    = $mtProto->referenceDatabase;
$minDatabase          = $mtProto->minDatabase;
$channel_participants = $mtProto->channel_participants;
$settings             = $mtProto->settings;                 // Settings array. /*public  array*/
$config               = Tools::getVar($mtProto, 'config');  // Config array.   /*private array*/
// Event handler
$event_handler          = Tools::getVar($mtProto, 'event_handler');
$event_handler_instance = Tools::getVar($mtProto, 'event_handler_instance');
$loop_callback          = Tools::getVar($mtProto, 'loop_callback');
$updates                = Tools::getVar($mtProto, 'updates');
$updates_key            = Tools::getVar($mtProto, 'loop_callback');
$hook_url               = Tools::getVar($mtProto, 'updates_key');

// MTProto::ReferenceDatabase
// ['db', 'API']
$db               = Tools::getVar($referenceDatabase, 'db');             // private array; is empty
$API              = Tools::getVar($referenceDatabase, 'API');            // private MTProto
$cache            = Tools::getVar($referenceDatabase, 'cache');          // private array
$cacheContexts    = Tools::getVar($referenceDatabase, 'cacheContexts');  // private array
$refreshed        = Tools::getVar($referenceDatabase, 'refreshed');      // private array
$refresh          = Tools::getVar($referenceDatabase, 'refresh');        // private bool
$refreshCount     = Tools::getVar($referenceDatabase, 'refreshCount');   // private int

// API class instance
$storage         = Tools::getVar($API, 'storage');      // Storage for externally set properties to be serialized. /*protected array*/
$oldInstance     = Tools::getVar($API, 'oldInstance');  // Whether this is an old instance. /*private bool*/
//$mtProto2      = $API->API;                           // Instance of MadelineProto. /*public MTProto */
$wrapper         = Tools::getVar($API, 'wrapper');      // API wrapper (to avoid circular references). /*private APIWrapper*/

//echo ($API->getInfo(1234) . PHP_EOL);

$appInfo = $settings['app_info'];
$logger  = $settings['logger'];
$dbser   = \serialize($db);
\file_put_contents('dbserialized.txt', $dbser);
error_log("AppInfo: " . toJSON($appInfo));
error_log("Logger: "  . toJSON($logger));
exit;

$refDB      = $mtProto->referenceDatabase;
//$api2       = Tools::getVar($mtProto, 'api');
$authorized = $mtProto->authorized;
$hasAllAuth = $mtProto->hasAllAuth();
//$refDB_db   = Tools::getVar($refDB,   'db');
//$db         = Tools::getVar($mtProto, 'db');
$msg_ids    = Tools::getVar($mtProto, 'msg_ids');

$mtproto_settings = &$mtProto->settings;
$mtproto_settings = [];

$mtproto_config = [];

$mtproto_rsakeys = &Tools::getVar($mtProto, 'rsa_keys');
$mtproto_rsakeys = [];

$initing_authorization = &Tools::getVar($mtProto, 'initing_authorization');
$initing_authorization = [];

$mtproto_feeders = &Tools::getVar($mtProto, 'feeders');
$initing_feeders = [];

$mtproto_datacenter = $mtProto->datacenter;
//$initing_datacenter = [];
$dc = $mtproto_datacenter;
$dc_sockets = &$dc->sockets;
//error_log("datacenter::sockets: " . print_r($dc_sockets, true));
$dc->sockets = [];
//error_log("datacenter::sockets: " . toJSON($dc_sockets));
//error_log("MTProto::datacenter: " . print_r($mtproto_datacenter, true));

$mtproto_api = &Tools::getVar($mtProto, 'API');
error_log("mtproto::API: " . print_r($mtproto_api, true));
//$mtProto->API = null;

//$mtProto->datacenter = null;  // cause Exception
//error_log("mtProto: " . print_r($mtProto, true));


//error_log(toJSON($appInfo));
//error_log(toJSON($logger));
//error_log("chats: "             . toJSON($chats));
//error_log("chat_participants: " . toJSON($channel_participants));
//error_log("full_chats: "        . toJSON($full_chats));
//error_log("msg_ids: "           . toJSON($msg_ids));
//error_log("db: "                . toJSON($db));
//error_log("refDB_db: "          . toJSON($refDB_db));
error_log("mtproto::settings: "   . toJSON($settings));

//error_log("MTProto: " . print_r($mtProto, true));
//error_log("Authorized: $authorized");
//error_log("Has All Authorizations: " . ($hasAllAuth ? 'true' : 'false'));

echo ('Bye, bye! <br>' . PHP_EOL);
exit();

function includeMadeline(string $source = 'phar', string $param = '')
{
    switch ($source) {
        case 'phar':
            if (!\file_exists('madeline.php')) {
                \copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');
            }
            define('MADELINE_BRANCH', 'master');
            include 'madeline.php';
            break;
        case 'composer':
            include 'vendor/autoload.php';
            break;
        default:
            throw new \ErrorException("Invalid argument: '$source'");
    }
}

function toJSON($var, bool $pretty = true): ?string
{
    if (isset($var['request'])) {
        unset($var['request']);
    }
    $opts = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
    $json = \json_encode($var, $opts | ($pretty ? JSON_PRETTY_PRINT : 0));
    $json = ($json !== '') ? $json : var_export($var, true);
    return ($json != false) ? $json : null;
}
