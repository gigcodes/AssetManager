<?php

namespace Gigcodes\AssetManager\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;

class Media extends Model
{
    use HasFactory;

    protected $table = 'media';
    protected $manipulationKey;
    protected $fillable = [
        'name', 'file_name', 'full_path', 'upload_path', 'container',
        'mime_type', 'disk', 'size', 'manipulations', 'custom_properties'
    ];

    protected $casts = [
        'custom_properties' => 'json',
        'manipulations' => 'json'
    ];

    protected static function boot()
    {
        parent::boot();
        //create thumbnail
        static::creating(function ($model) {
            $model->manipulations = (new self)->generateThumb($model);
        });
        //delete file also from server
        static::deleting(function ($model) {
            (new self)->deleteFile($model);
        });
    }

    public function deleteFile(self $media)
    {
        foreach ($media->manipulations as $manipulation) {
            Storage::disk($media->disk)->delete($media->upload_path . '/' . $manipulation);
        }
        return Storage::disk($media->disk)->delete($media->upload_path . '/' . $media->file_name);
    }

    public function generateThumb(self $media)
    {
        $path = $media->full_path;
        $storageUrl = Storage::disk($media->disk)->path($path);
        $image = Image::make($storageUrl)->resize(300, null, function ($constraint) {
            $constraint->aspectRatio();
        });

        $toenail = Image::make($storageUrl)->resize(1000, 1000, function ($constraint) {
            $constraint->aspectRatio();
        });

        $pathinfo = pathinfo($storageUrl);

        $thumbName = $pathinfo['filename'] . '-thumb.' . $pathinfo['extension'];
        $toenailName = $pathinfo['filename'] . '-large.' . $pathinfo['extension'];

        $image->save(Storage::disk($media->disk)->path($media->upload_path . '/' . $thumbName));
        $toenail->save(Storage::disk($media->disk)->path($media->upload_path . '/' . $toenailName));

        return [
            'thumb' => $thumbName,
            'toenail' => $toenailName
        ];
    }

    public function setManipulationAttribute($key, $value = null)
    {
        $manipulation = $this->manipulations;
        if (isset($manipulation[$key])) {
            $this->manipulation[$key] = $value;
        } else {
            $this->manipulation = array_merge($manipulation, [
                $key => $value
            ]);
        }
    }

    public function downloadFile()
    {
        $headers = array(
            "Content-Type: {$this->mime_type}",
        );
        $file = Storage::disk($this->disk)->path($this->upload_path . '/' . $this->full_path);
        return response()->download($file, $this->file_name, $headers);
    }

    public function getManipulation($key)
    {
        $this->manipulationKey = isset($this->manipulations[$key]) ? $this->manipulations[$key] : null;
        return $this;
    }

    public function getPublicUrl()
    {
        $path = $this->upload_path === '/' ? '' : $this->upload_path . '/';
        return asset(Storage::disk($this->disk)->url($path . $this->manipulationKey));
    }

    public function getStoragePath()
    {
        $path = $this->upload_path === '/' ? '' : $this->upload_path . '/';
        return Storage::disk($this->disk)->path($path . $this->manipulationKey);
    }
}
