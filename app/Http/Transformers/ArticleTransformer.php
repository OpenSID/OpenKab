<?php

namespace App\Http\Transformers;

use App\Models\CMS\Article;
use Illuminate\Support\Facades\Storage;
use League\Fractal\TransformerAbstract;

class ArticleTransformer extends TransformerAbstract
{
    public function transform(Article $article)
    {
        return [
            'id' => $article->id,
            'category_id' => $article->category->name,
            'published_at' => $article->localized_published_at,
            'slug' => $article->slug,
            'title' => $article->title,
            'thumbnail' => $article->thumbnail ? Storage::url($article->thumbnail) : '',
            'content' => $article->content,
            'state' => $article->labelState,
            'created_at' => $article->created_at,
            'updated_at' => $article->updated_at,
            'deleted_at' => $article->deleted_at,
        ];
    }
}
