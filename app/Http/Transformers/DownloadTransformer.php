<?php

namespace App\Http\Transformers;

use App\Models\CMS\Download;
use App\Models\Enums\StatusEnum;
use Illuminate\Support\Facades\Storage;
use League\Fractal\TransformerAbstract;
use Spatie\Html\Facades\Html;

class DownloadTransformer extends TransformerAbstract
{
    public function transform(Download $download)
    {
        return [
            'id' => $download->id,
            'title' => $download->title,
            'url' => $download->url ? Html::a(Storage::url($download->url), 'berkas')->class('text-primary')->toHtml() : '',
            'description' => $download->description,
            'state' => $download->state == StatusEnum::aktif ? 'Tampilkan' : 'Tidak',
            'created_at' => $download->created_at,
            'updated_at' => $download->updated_at,
            'deleted_at' => $download->deleted_at,
            'total_download' => $download->counter?->total ?? 0,
        ];
    }
}
