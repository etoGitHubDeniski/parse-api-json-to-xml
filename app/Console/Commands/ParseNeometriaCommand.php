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
        $client = new ParseDataClient;
        $response = $client->parse();
        echo $response;
    }
}
