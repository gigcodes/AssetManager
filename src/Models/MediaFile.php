<?php

namespace Gigcodes\AssetManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * App\Models\MediaFile
 *
 * @property int $id
 * @property string $uuid
 * @property int $media_collection_id
 * @property string $collection_name
 * @property int|null $media_folder_id
 * @property string $name
 * @property string $basename
 * @property string|null $mimetype
 * @property string $filesize
 * @property string|null $type
 * @property string $full_path
 * @property string $upload_path
 * @property string $disk
 * @property array|null $manipulations
 * @property array|null $custom_properties
 * @property int|null $order_column
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\ExifMeta|null $exif
 * @property-read \App\Models\MediaFolder|null $parent
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFile newQuery()
 * @method static \Illuminate\Database\Query\Builder|MediaFile onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFile query()
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFile whereBasename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFile whereCollectionName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFile whereCustomProperties($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFile whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFile whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFile whereFilesize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFile whereFullPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFile whereManipulations($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFile whereMediaCollectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFile whereMediaFolderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFile whereMimetype($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFile whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFile whereOrderColumn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFile whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFile whereUploadPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MediaFile whereUuid($value)
 * @method static \Illuminate\Database\Query\Builder|MediaFile withTrashed()
 * @method static \Illuminate\Database\Query\Builder|MediaFile withoutTrashed()
 * @mixin \Eloquent
 */
class MediaFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $manipulationKey;

    protected $fillable = [
        'uuid', 'media_collection_id', 'collection_name', 'media_folder_id', 'name', 'basename',
        'mimetype', 'filesize', 'type', 'full_path', 'upload_path', 'disk', 'manipulations', 'custom_properties',
        'order_column'
    ];

    protected $casts = [
        'manipulations' => 'json',
        'custom_properties' => 'json'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($file) {
            $file->uuid = (string)Str::uuid();
        });
    }

    public function exif(): HasOne
    {
        return $this->hasOne(ExifMeta::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(config('asset-manager.folder_class'));
    }

    public function getManipulation($key)
    {
        $this->manipulationKey = $this->manipulations[$key] ?? null;
        return $this;
    }

    public function getPublicUrl()
    {
        return asset(Storage::disk($this->disk)->url($this->upload_path . "/" . $this->manipulationKey));
    }
}
