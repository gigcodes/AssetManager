<?php

namespace Gigcodes\AssetManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * App\Models\MediaCollection
 *
 * @property int $id
 * @property string $uuid
 * @property string $name
 * @property string $title
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MediaFile[] $files
 * @property-read int|null $files_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\MediaFolder[] $folders
 * @property-read int|null $folders_count
 * @method static \Illuminate\Database\Eloquent\Builder|MediaCollection newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MediaCollection newQuery()
 * @method static \Illuminate\Database\Query\Builder|MediaCollection onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MediaCollection query()
 * @method static \Illuminate\Database\Eloquent\Builder|MediaCollection whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaCollection whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaCollection whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaCollection whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaCollection whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaCollection whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaCollection whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaCollection whereUuid($value)
 * @method static \Illuminate\Database\Query\Builder|MediaCollection withTrashed()
 * @method static \Illuminate\Database\Query\Builder|MediaCollection withoutTrashed()
 * @mixin \Eloquent
 */
class MediaCollection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', 'name', 'title', 'description'
    ];

    public function folders(): HasMany
    {
        return $this->hasMany(MediaFolder::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(MediaFile::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($collection) {
            $collection->uuid = (string)Str::uuid();
        });
    }
}
