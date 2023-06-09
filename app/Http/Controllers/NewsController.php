<?php

namespace App\Http\Controllers;

use App\Models\Dislike;
use App\Models\Like;
use App\Models\News;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NewsController extends Controller
{
    public function index(): View
    {
        $news = News::withCount('likes', 'dislikes')
            ->orderByDesc('likes_count')
            ->orderByDesc('dislikes_count')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('news.index', compact('news'));
    }

    public function like($id): RedirectResponse
    {
        $news = News::findOrFail($id);
        $like = new Like();
        $like->news()->associate($news);
        $like->save();

        return redirect()->back();
    }

    public function dislike($id): RedirectResponse
    {
        $news = News::findOrFail($id);
        $dislike = new Dislike();
        $dislike->news()->associate($news);
        $dislike->save();

        return redirect()->back();
    }
}
