<?php

if (!file_exists('madeline.php')) {
    copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');
}
include 'madeline.php';

$mp = new \danog\MadelineProto\API('madeline.madeline');
$mp->async(true);
$mp->loop(function () use ($mp) {
    yield $mp->start();
    yield processMessages($mp, '@webwarp', function ($message) use ($mp): Generator {
        // Begin your code
        yield $mp->echo("id: " . $message['id'] . " message: " . @$message['message'] . " <br>\n");
        // End your code
    });
});
echo ('Bye, bye! <br>' . PHP_EOL);


function processMessages($mp, $channel, callable $callback, int $limit = 100, int $pause = 1): Generator
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

        $mp->logger("Message Count: " . count($messages['messages']));
        if (count($messages['messages']) === 0) {
            break;
        }

        foreach ($messages['messages'] as $message) {
            yield $callback($message);
        }

        $offsetId = end($messages['messages'])['id'];
        yield $mp->sleep($pause);
    } while (true);
}
