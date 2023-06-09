<?php

namespace App\Console\Commands;

use App\Jobs\AddNewsJob;
use Illuminate\Bus\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Config;
use Throwable;

class UploadNewsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upload:news';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload news from RSS Feed';

    /**
     * Execute the console command.
     */

    public const RSS_URL = 'https://lenta.ru/rss/news';

    public function handle(): void
    {
        try {
            $response = Http::get(self::RSS_URL);

            define('MAX_FILE_SIZE', 600000);
            $xml = simplexml_load_string($response->body());

            Config::set('news.currentNewNewsCount', 0);
            $jobs = [];

            foreach ($xml->channel->item as $item) {
                $imageUrl = current($item->enclosure->attributes())['url'];

                $data = [
                    'title' => $item->title,
                    'link' => $item->link,
                    'guid' => $item->guid,
                    'author' => $item->author,
                    'pubDate' => $item->pubDate,
                    'image' =>  base64_encode(file_get_contents($imageUrl)),
                    'description' => $item->description,
                    'category' => $item->category,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                $jobs[] = new AddNewsJob($data);
            }

            Bus::batch($jobs)
                ->catch(function (Batch $batch) {
                    throw new \Exception();
                })
                ->then(function (Batch $batch) {
                    $this->info('Inserted new news count:' . Config::get('news.currentNewNewsCount'));
                })
                ->dispatch();
        } catch (Throwable $e) {
            $this->error('Failed insert new news data from RSS!');
        }
    }
}
