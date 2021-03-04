<?php // questions? bugs? suggestions? support? => contact t.me/webwarp

/* The script ensures that:
 * The session is not logged-out;
 * The session is not terminated;
 * The session file is not corrupted;
 * The logged-in account is not deleted.
 */

declare(strict_types=1);
define('SCRIPT_INFO', 'VERIFY V1.0.1'); // <== Do not change!

use \danog\MadelineProto\API;
use \danog\MadelineProto\Logger;
use \danog\MadelineProto\Shutdown;
use \danog\MadelineProto\Magic;

error_reporting(E_ALL);
ini_set('ignore_repeated_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('display_errors',         '0');
ini_set('log_errors',             '1');
ini_set('error_log',              'MadelineProto.log');

includeMadeline('phar');

$session  = temporarySession();
$settings = [
    'logger' => [
        'logger'       => Logger::CALLABLE_LOGGER,
        'logger_param' => 'filter',
        'logger_level' => Logger::ULTRA_VERBOSE,
    ]
];
$mp = new API($session, $settings);
if ($mp->API->authorized === 3 && !$mp->hasAllAuth()) {
    suffocateRobot('The Logged-in account might have been deleted!');
}
$mp->async(true);
$mp->loop(function () use ($mp) {
    yield $mp->start();
});
suffocateRobot('Everything is hunky-dory!');

function filter($entry, int $level): void
{
    $entry = is_array($entry) ? toJSON($entry) : $entry;
    if (\is_string($entry) && strpos($entry, 'Could not resend ') !== false) {
        error_log((string)$entry);
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
    if (file_exists('madeline.madeline.tmp')) {
        unlink('madeline.madeline.tmp');
    }
    if (file_exists('madeline.madeline.tmp.lock')) {
        unlink('madeline.madeline.tmp.lock');
    }
    Shutdown::removeCallback('restarter');
    Shutdown::removeCallback(0);
    Shutdown::removeCallback(1);
    Shutdown::removeCallback(2);
    ini_set('log_errors', '0');
    Magic::$signaled = true;
    if (\defined(STDIN::class)) {
        \Amp\ByteStream\getStdin()->unreference();
    }
    \Amp\ByteStream\getInputBufferStream()->unreference();
    $driver = \Amp\Loop::get();
    $reflectionClass = new ReflectionClass(\Amp\Loop\Driver::class);
    $reflectionProperty = $reflectionClass->getProperty('watchers');
    $reflectionProperty->setAccessible(true);
    foreach (\array_keys($reflectionProperty->getValue($driver)) as $key) {
        try {
            $driver->unreference($key);
        } catch (Throwable $e) {
        }
    }
    \Amp\Loop::stop();
    die();
}

function temporarySession(): string
{
    $sessions = [];
    foreach (glob('*.*.lock') as $file) {
        $file = substr($file, 0, strlen($file) - 5);
        $tmp = substr($file, -4) === '.tmp';
        if (!$tmp && file_exists($file)) {
            $sessions[] = $file;
        }
    }
    if (count($sessions) === 0) {
        suffocateRobot('No session file founc!');
    } elseif (count($sessions) > 1) {
        foreach ($sessions as $session) {
            echo ("$session <br>" . PHP_EOL);
        }
        suffocateRobot('There are more than one session files!');
    }
    $session = $sessions[0];
    $tmpsess = $session . '.tmp';
    \copy($session, $tmpsess);

    return $tmpsess;
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
