<?php

namespace App\Models\CMS;

use App\Models\Enums\StatusEnum;
use Carbon\Carbon;
use Database\Factories\PageFactory;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends SluggableModel
{
    use SoftDeletes, HasFactory;

    public $table = 'pages';

    const STATE_STRING = [StatusEnum::aktif => 'Aktif', StatusEnum::tidakAktif => 'Tidak Aktif'];

    public $fillable = [
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
        'state' => 'boolean',
    ];

    public static array $rules = [
        'published_at' => 'required',
        'title' => 'required|string|max:255',
        'thumbnail' => 'nullable|string|max:255',
        'content' => 'required|string|max:65535',
        'state' => 'required|boolean',
    ];

    protected static function newFactory()
    {
        return PageFactory::new();
    }

    /**
     * @return mixed
     */
    public function scopePublished($query)
    {
        return $query->where('published_at', '<=', now())->orderBy('published_at', 'desc');
    }

    public function scopeActivePublished($query)
    {
        return $this->published()->whereState(StatusEnum::aktif);
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
        return  \Str::replaceFirst(url('/'), '', route('page', ['pSlug' => $this->slug]));
    }

    public function getLabelStateAttribute(): string
    {
        return self::STATE_STRING[$this->state] ?? '-';
    }
}
