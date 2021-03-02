<?php

declare(strict_types=1);

\date_default_timezone_set('UTC');
\ignore_user_abort(true);
\set_time_limit(0);
\error_reporting(E_ALL);
ini_set('ignore_repeated_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('display_errors',         '1');
ini_set('default_charset',        'UTF-8');
ini_set('precision',              '18');
ini_set('log_errors',             '1');
ini_set('error_log',              'MadelineProto.log');

if (!file_exists('madeline.php')) {
    copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');
}
include 'madeline.php';
//include 'vendor/autoload.php';

//\danog\MadelineProto\Magic::classExists();
$settings = []; // = ['logger' => ['logger' => \danog\MadelineProto\Logger::FILE_LOGGER, 'logger-param' => 'MadelineProto.log']];
//\danog\MadelineProto\Logger::getLoggerFromSettings($settings);

$mp = new \danog\MadelineProto\API('madeline.madeline', $settings);
$mp->updateSettings($settings);
$mp->async(true);
$mp->loop(function () use ($mp) {
    yield $mp->start();
    yield processMessages($mp, '@blablabber', function ($message) use ($mp) {

        yield $mp->echo("id: " . $message['id'] . " message: " . @$message['message'] . " <br>\n"); // <= put your code here

    });
});
echo ('Bye, bye!');

function processMessages($mp, $channel, callable $callback, int $limit = 100, int $pause = 5): Generator
{
    $offsetId = 0;
    do {
        $messages = yield $mp->messages->getHistory([
            'peer'        => $channel,
            'offset_id'   => $offsetId,
            'offset_date' => 0,
            'add_offset'  => 0,
            'limit'       => $limit,
            'max_id'      => 0,
            'min_id'      => 0,
            'hash'        => 0
        ]);

        if (count($messages['messages']) == 0) {
            break;
        }

        foreach ($messages['messages'] as $message) {
            $callback($message);
        }

        $offsetId = end($messages['messages'])['id'];
        yield $mp->sleep($pause);
    } while (true);
}
