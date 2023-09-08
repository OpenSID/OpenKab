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
        'status' => 'required|digits_between:0,1'
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source'   => 'name',
                'onUpdate' => true
            ]
        ];
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function articles(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Article::class)->published();
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
        return route('category', ['cSlug' => $this->slug]);
    }
}
