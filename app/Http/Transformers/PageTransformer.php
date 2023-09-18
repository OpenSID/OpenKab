<?php

namespace App\Http\Transformers;

use App\Models\CMS\Page;
use Illuminate\Support\Facades\Storage;
use League\Fractal\TransformerAbstract;

class PageTransformer extends TransformerAbstract
{
    public function transform(Page $page)
    {
        return [
            'id' => $page->id,
            'published_at' => $page->local_published_at,
            'slug' => $page->slug,
            'title' => $page->title,
            'thumbnail' => $page->thumbnail ? Storage::url($page->thumbnail) : '',
            'content' => $page->content,
            'state' => $page->labelState,
            'created_at' => $page->created_at,
            'updated_at' => $page->updated_at,
            'deleted_at' => $page->deleted_at,
        ];
    }
}
