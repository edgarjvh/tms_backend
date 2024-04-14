<?php

namespace App\Console\Commands;

use App\Mail\SaimeMailable;
use App\Models\Saime;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class SaimeCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saime:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Saime website availability';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $saime_config = Saime::query()->first();

        $url = 'https://siic.saime.gob.ve';

//        if (!filter_var($url, FILTER_VALIDATE_URL)){
//            return false;
//        }
//
//        // Initialize cURL
//        $curlInit = curl_init($url);
//
//        // Set options
//        curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
//        curl_setopt($curlInit,CURLOPT_HEADER,true);
//        curl_setopt($curlInit,CURLOPT_NOBODY,true);
//        curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);
//
//        // Get response
//        $response = curl_exec($curlInit);
//
//        // Close a cURL session
//        curl_close($curlInit);

        $client = new Client();
        $response = $client->get($url);

        $body = $response->getBody();

        $last_status = $response ? 'online' : 'offline';

        try {
            Mail::send(new SaimeMailable($url, $last_status,date('Y-m-d H:i:s'),$body));
        } catch (\Exception $e) {
        }
    }
}
