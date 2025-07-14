<?php

namespace App\Models\CMS;

use Carbon\Carbon;
use Database\Factories\ArticleFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends SluggableModel
{
    use HasFactory;
    use SoftDeletes;

    public $table = 'articles';

    const DRAFT = 0;

    const PUBLISH = 1;

    const STATE_STRING = [self::DRAFT => 'Draft', self::PUBLISH => 'Terbitkan'];

    public $fillable = [
        'category_id',
        'published_at',
        'slug',
        'title',
        'thumbnail',
        'content',
        'state',
    ];

    protected $casts = [
        'published_at' => 'date',
        'slug' => 'string',
        'title' => 'string',
        'thumbnail' => 'string',
        'content' => 'string',
    ];

    public static array $rules = [
        'category_id' => 'required',
        'published_at' => 'required',
        'slug' => 'string|max:255',
        'title' => 'required|string|max:200|min:5',
        'content' => 'required|string|max:65535',
        'state' => 'required|numeric|digits_between:0,1',
        'foto' => 'nullable|image|max:1024|mimes:png,jpg',
    ];

    // menyebabkan error ketika validasi js
    public static array $errorMessages = [
        // 'title' => [
        //     'min' => 'Judul minimal :min karakter',
        //     'max' => 'Judul minimal :max karakter',
        // ]
    ];

    protected static function newFactory()
    {
        return ArticleFactory::new();
    }

    /**
     * Carbon instance fields.
     *
     * @var array
     */
    protected $dates = ['published_at'];

    public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return mixed
     */
    public function scopePublished($query)
    {
        return $query->where('published_at', '<=', now())->orderBy('published_at', 'desc');
    }

    public function getLocalizedPublishedAtAttribute(): string
    {
        return Carbon::parse($this->attributes['published_at'])->format(config('app.format.date'));
    }

    public function getLocalPublishedAtAttribute($value)
    {
        return isset($this->attributes['published_at']) ? Carbon::parse($this->attributes['published_at'])->format(config('app.format.date')) : null;
    }

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function getLinkAttribute(): string
    {
        return route('article', ['aSlug' => $this->slug]);
    }

    public function getLabelStateAttribute(): string
    {
        return self::STATE_STRING[$this->state] ?? '-';
    }
}
