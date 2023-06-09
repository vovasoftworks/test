<?php

namespace App\Jobs;

use App\Models\News;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Throwable;

class AddNewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected array $news)
    {
    }

    public function handle(): void
    {
        try {
            $news = News::where('title', $this->news['title'])->first();

            $currentNewNewsCount = Config::get('news.currentNewNewsCount');

            if (!$news) {
                News::insert($this->news);
                Config::set('news.current_news.count', ++$currentNewNewsCount);
            }
        } catch (Throwable $e) {
            dd($e->getMessage());
        }
    }
}
