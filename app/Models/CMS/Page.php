<?php

namespace App\Models\CMS;

use App\Models\Enums\StatusEnum;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends SluggableModel
{
    use SoftDeletes;

    public $table = 'pages';

    const STATE_STRING = [ StatusEnum::aktif => 'Aktif', StatusEnum::tidakAktif => 'Tidak Aktif'];
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

    public static array $errorMessages = [
        'title' => [
            'min' => 'Judul minimal :min karakter',
            'max' => 'Judul minimal :max karakter',
        ]
    ];
/**
     * @param $query
     *
     * @return mixed
     */
    public function scopePublished($query)
    {
        return $query->where('published_at', '<=', now())->orderBy('published_at', 'desc');
    }

    /**
     * @return string
     */
    public function getLocalizedPublishedAtAttribute(): string
    {
        return Carbon::parse($this->attributes['published_at'])->format(config('app.format.date'));
    }

    public function getPublishedAtAttribute($value)
    {
        return Carbon::parse($value)->format(config('app.format.date'));
    }

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * @return string
     */
    public function getLinkAttribute(): string
    {
        return route('page', ['aSlug' => $this->slug]);
    }

    public function getLabelStateAttribute(): string
    {
        return self::STATE_STRING[$this->state] ?? '-';
    }
}

