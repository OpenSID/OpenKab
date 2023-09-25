<?php

namespace App\Models\CMS;

use App\Models\OpenKabModel as Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Download extends Model
{
    use SoftDeletes;

    public $table = 'downloads';

    public $fillable = [
        'title',
        'url',
        'description',
        'state',
    ];

    protected $casts = [
        'title' => 'string',
        'url' => 'string',
        'description' => 'string',
        'state' => 'boolean',
    ];

    public static array $rules = [
        'title' => 'required|string|max:255',
        'url' => 'nullable|string|max:255',
        'download_file' => 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,zip|max:5120|valid_file',
        'description' => 'required|string|max:65535',
        'state' => 'required|boolean',
    ];

    /**
     * Get the counter associated with the Download
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function counter(): HasOne
    {
        return $this->hasOne(CounterDownload::class, 'model_id', 'id')->whereModelType(Download::class);
    }
}
