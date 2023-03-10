<?php

namespace Gigcodes\AssetManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * App\Models\MediaFolder
 *
 * @property int $id
 * @property string $uuid
 * @property int $media_collection_id
 * @property string $collection_name
 * @property string|null $parent_id
 * @property string $name
 * @property string|null $emoji
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|MediaFolder[] $children
 * @property-read int|null $children_count
 * @property-read MediaFolder|null $parent
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFolder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFolder newQuery()
 * @method static \Illuminate\Database\Query\Builder|MediaFolder onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFolder query()
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFolder whereCollectionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFolder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFolder whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFolder whereEmoji($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFolder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFolder whereMediaCollectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFolder whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFolder whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFolder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFolder whereUuid($value)
 * @method static \Illuminate\Database\Query\Builder|MediaFolder withTrashed()
 * @method static \Illuminate\Database\Query\Builder|MediaFolder withoutTrashed()
 * @mixin \Eloquent
 */
class MediaFolder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid', 'media_collection_id', 'collection_name', 'parent_id', 'name', 'emoji'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($folder) {
            $folder->uuid = (string)Str::uuid();
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id', 'uuid');
    }

    public function deepParent()
    {
        return $this->belongsTo(self::class, 'parent_id', 'uuid')->with('parent');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'uuid');
    }

    public function childrenFolders(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id', 'uuid')->with('children');
    }
}
