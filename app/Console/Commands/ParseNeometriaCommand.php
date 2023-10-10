<?php

namespace App\Console\Commands;

use App\Components\ParseDataClient;
use Illuminate\Console\Command;

class ParseNeometriaCommand extends Command
{
    protected $signature = 'parse:neometria';
    protected $description = 'Parse data from neometria';

    public function handle()
    {
        $limit = $this->ask('Сколько записей вывести?', 'all');
        $offset = $this->ask('С какой записи начать?', 0);
        $client = new ParseDataClient;
        $response = $client->parse($limit, $offset);
        echo $response;
    }
}
