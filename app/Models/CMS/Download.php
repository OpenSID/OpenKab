<?php

namespace App\Models\CMS;

use App\Models\OpenKabModel as Model;
use Database\Factories\DownloadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Download extends Model
{
    use SoftDeletes, HasFactory;

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

    protected static function newFactory()
    {
        return DownloadFactory::new();
    }

    /**
     * Get the counter associated with the Download.
     */
    public function counter(): HasOne
    {
        return $this->hasOne(CounterDownload::class, 'model_id', 'id')->whereModelType(Download::class);
    }
}
