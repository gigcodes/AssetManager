<?php

namespace Gigcodes\AssetManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * App\Models\ExifMeta
 *
 * @property int $id
 * @property string $uuid
 * @property int $media_file_id
 * @property string|null $date_time_original
 * @property string|null $artist
 * @property int|null $height
 * @property int|null $width
 * @property string|null $x_resolution
 * @property string|null $y_resolution
 * @property int|null $color_space
 * @property string|null $camera
 * @property string|null $model
 * @property string|null $aperture_value
 * @property string|null $exposure_time
 * @property string|null $focal_length
 * @property int|null $iso
 * @property string|null $aperture_f_number
 * @property string|null $ccd_width
 * @property array|null $longitude
 * @property array|null $latitude
 * @property string|null $longitude_ref
 * @property string|null $latitude_ref
 * @property-read \App\Models\MediaFile|null $file
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta whereApertureFNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta whereApertureValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta whereArtist($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta whereCamera($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta whereCcdWidth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta whereColorSpace($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta whereDateTimeOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta whereExposureTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta whereFocalLength($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta whereIso($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta whereLatitudeRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta whereLongitudeRef($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta whereMediaFileId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta whereUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta whereWidth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta whereXResolution($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExifMeta whereYResolution($value)
 * @mixin \Eloquent
 */
class ExifMeta extends Model
{
    use HasFactory;

    protected $fillable = [
        'media_file_id', 'date_time_original', 'artist', 'height', 'width', 'x_resolution',
        'y_resolution', 'color_space', 'camera', 'model', 'aperture_value', 'exposure_time',
        'focal_length', 'iso', 'aperture_f_number', 'ccd_width', 'longitude', 'latitude',
        'longitude_ref', 'latitude_ref'
    ];

    public $timestamps = false;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $casts = [
        'longitude' => 'array',
        'latitude'  => 'array',
    ];

    /**
     * Get parent
     */
    public function file(): HasOne
    {
        return $this->hasOne(MediaFile::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string)Str::uuid();
        });
    }
}
