<?php

namespace App\Models\CMS;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends SluggableModel
{
    use HasFactory;
    use SoftDeletes;

    public $table = 'categories';

    public $fillable = [
        'slug',
        'name',
        'status',
    ];

    protected $casts = [
        'slug' => 'string',
        'name' => 'string',
    ];

    public static array $rules = [
        // 'slug' => 'required|string|max:255',
        'name' => 'required|string|max:255',
        'status' => 'required|digits_between:0,1',
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'onUpdate' => true,
            ],
        ];
    }

    public function articles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function publishArticles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Article::class)->published();
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
        return \Str::replaceFirst(url('/'), '', route('category', ['cSlug' => $this->slug]));
    }
}
