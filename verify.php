<?php // questions? bugs? suggestions? support? => contact t.me/webwarp

/* The script ensures that:
 * The session is not logged-out;
 * The session is not terminated;
 * The session file is not corrupted;
 * The logged-in account is not deleted.
 */

declare(strict_types=1);
define('SCRIPT_INFO', 'VERIFY V1.0.0'); // <== Do not change!

use \danog\MadelineProto\API;
use \danog\MadelineProto\Logger;
use \danog\MadelineProto\Shutdown;

error_reporting(E_ALL);
ini_set('ignore_repeated_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('display_errors',         '1');
ini_set('log_errors',             '1');
ini_set('error_log',              'MadelineProto.log');

includeMadeline('phar');

$sessions = findSessions();
if (count($sessions) !== 1) {
    suffocateRobot('No session file or more than one!');
}

$session  = $sessions[0];
$settings = [
    'logger' => [
        'logger'       => Logger::CALLABLE_LOGGER,
        'logger_param' => 'filter',
        'logger_level' => Logger::ULTRA_VERBOSE,
    ]
];
$mp = new API($session, $settings);
Shutdown::removeCallback('restarter');
if ($mp->API->authorized === 3 && !$mp->hasAllAuth()) {
    suffocateRobot('The Logged-in account might have been deleted!');
}
$mp->async(true);
$mp->loop(function () use ($mp) {
    yield $mp->start();
});
suffocateRobot('Everythin is hunky-dory!');

function filter($entry, int $level): void
{
    if (\is_string($entry) && strpos($entry, 'Could not resend req_pq_multi') !== false) {
        suffocateRobot('Session is bad!');
    }
}

function suffocateRobot(string $message = 'Everything is OK!'): void
{
    $buffer = @\ob_get_clean() ?: '';
    $buffer .= '<html><body><h1>' . \htmlentities($message) . '</h1></body></html>';
    \ignore_user_abort(true);
    \header('Connection: close');
    \header('Content-Type: text/html');
    echo $buffer;
    \flush();
    Shutdown::removeCallback('restarter');
    Shutdown::removeCallback(0);
    Shutdown::removeCallback(1);
    Shutdown::removeCallback(2);
    exit(1);
}

function findSessions(): array
{
    $sessions = [];
    foreach (glob('*.*.lock') as $file) {
        $file = substr($file, 0, strlen($file) - 5);
        if (file_exists($file)) {
            $sessions[] = $file;
        }
    }
    return $sessions;
}

function includeMadeline(string $source = 'phar', string $param = '')
{
    switch ($source) {
        case 'phar':
            if (!\file_exists('madeline.php')) {
                \copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');
            }
            include 'madeline.php';
            break;
        case 'composer':
            include 'vendor/autoload.php';
            break;
        default:
            throw new \ErrorException("Invalid argument: '$source'");
    }
}
