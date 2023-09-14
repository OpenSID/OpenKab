<?php

namespace App\Http\Transformers;

use App\Models\CMS\Slide;
use App\Models\Enums\StatusEnum;
use Illuminate\Support\Facades\Storage;
use League\Fractal\TransformerAbstract;

class SlideTransformer extends TransformerAbstract
{
    public function transform(Slide $slide)
    {
        return [
            'id' => $slide->id,
            'title' => $slide->title,
            'url' => $slide->url ? link_to($slide->url, 'tautan', ['class' => 'text-primary'])->toHtml() : '',
            'thumbnail' => $slide->thumbnail ? Storage::url($slide->thumbnail) : '',
            'description' => $slide->description,
            'state' => $slide->state == StatusEnum::aktif ? 'Aktif' : 'Non Aktif',
            'created_at' => $slide->created_at,
            'updated_at' => $slide->updated_at,
            'deleted_at' => $slide->deleted_at,
        ];
    }
}
