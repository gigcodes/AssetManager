<?php

namespace Gigcodes\AssetManager\Media\Modules;

use Gigcodes\AssetManager\Events\MediaFileOpsNotifications;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

trait Upload
{
    /**
     * upload new files.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function upload(Request $request)
    {
        $upload_path = $request->upload_path;
        $random_name = filter_var($request->random_names, FILTER_VALIDATE_BOOLEAN);
        $result = [];
        $broadcast = false;
        $custom_attr = collect(json_decode($request->custom_attrs));

        foreach ($request->file as $one) {
            if ($this->allowUpload($one)) {
                $one = $this->optimizeUpload($one);
                $orig_name = $one->getClientOriginalName();
                $name_only = pathinfo($orig_name, PATHINFO_FILENAME);
                $ext_only = pathinfo($orig_name, PATHINFO_EXTENSION);
                $final_name = $random_name
                    ? $this->getRandomString() . ".$ext_only"
                    : $this->cleanName($name_only) . ".$ext_only";

                $file_options = optional($custom_attr->firstWhere('name', $orig_name))->options;
                $file_type = $one->getMimeType();
                $destination = !$upload_path ? $final_name : $this->clearDblSlash("$upload_path/$final_name");

                try {
                    // check for mime type
                    if (Str::contains($file_type, $this->unallowedMimes)) {
                        throw new \Exception(
                            trans('messages.not_allowed_file_ext', ['attr' => $file_type])
                        );
                    }

                    // check existence
                    if ($this->storageDisk->exists($destination)) {
                        throw new \Exception(
                            trans('messages.error.already_exists')
                        );
                    }

                    // save file
                    $full_path = $this->storeFile($one, $upload_path, $final_name);

                    // fire event
                    event('MMFileUploaded', [
                        'file_path' => $full_path,
                        'mime_type' => $file_type,
                        'options' => $file_options,
                    ]);

                    $broadcast = true;
                    $result[] = [
                        'success' => true,
                        'file_name' => $final_name,
                    ];
                } catch (\Exception $e) {
                    $result[] = [
                        'success' => false,
                        'message' => "\"$final_name\" " . $e->getMessage(),
                    ];
                }
            } else {
                $result[] = [
                    'success' => false,
                    'message' => trans('messages.error.cant_upload'),
                ];
            }
        }

        // broadcast
        if ($broadcast) {
            broadcast(new MediaFileOpsNotifications([
                'op' => 'upload',
                'path' => $upload_path,
            ]))->toOthers();
        }

        return response()->json($result);
    }

    /**
     * save cropped image.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function uploadEditedImage(Request $request)
    {
        if ($this->allowUpload()) {
            $type = $request->mime_type;
            $path = $request->path;
            $original = $request->name;
            $data = explode(',', $request->data)[1];

            $name_only = pathinfo($original, PATHINFO_FILENAME) . '_' . $this->getRandomString();
            $ext_only = pathinfo($original, PATHINFO_EXTENSION);
            $file_name = "$name_only.$ext_only";
            $destination = !$path ? $file_name : $this->clearDblSlash("$path/$file_name");

            try {
                // check existence
                if ($this->storageDisk->exists($destination)) {
                    throw new \Exception(
                        trans('messages.error.already_exists')
                    );
                }

                // save file
                $this->storageDisk->put($destination, base64_decode($data));

                // fire event
                event('MMFileSaved', [
                    'file_path' => $destination,
                    'mime_type' => $type,
                ]);

                // broadcast
                broadcast(new MediaFileOpsNotifications([
                    'op' => 'upload',
                    'path' => $path,
                ]))->toOthers();

                $result = [
                    'success' => true,
                    'message' => $file_name,
                ];
            } catch (\Exception $e) {
                $result = [
                    'success' => false,
                    'message' => "\"$file_name\" " . $e->getMessage(),
                ];
            }
        } else {
            $result = [
                'success' => false,
                'message' => trans('messages.error.cant_upload'),
            ];
        }

        return response()->json($result);
    }

    /**
     * save image from link.
     *
     * @param Request $request [description]
     *
     * @return [type] [description]
     */
    public function uploadLink(Request $request)
    {
        if ($this->allowUpload()) {
            $url = $request->url;
            $path = $request->path;
            $random_name = $request->random_names;

            $original = substr($url, strrpos($url, '/') + 1);
            $name_only = pathinfo($original, PATHINFO_FILENAME);
            $ext_only = pathinfo($original, PATHINFO_EXTENSION);
            $file_name = $random_name
                ? $this->getRandomString() . ".$ext_only"
                : $this->cleanName($name_only) . ".$ext_only";

            $destination = !$path ? $file_name : $this->clearDblSlash("$path/$file_name");
            $file_type = image_type_to_mime_type(@exif_imagetype($url));

            try {
                // check for mime type
                if (Str::contains($file_type, $this->unallowedMimes)) {
                    throw new \Exception(
                        trans('messages.not_allowed_file_ext', ['attr' => $file_type])
                    );
                }

                // check existence
                if ($this->storageDisk->exists($destination)) {
                    throw new \Exception(
                        trans('messages.error.already_exists')
                    );
                }

                // save file
                $this->storageDisk->put($destination, file_get_contents($url));

                // fire event
                event('MMFileSaved', [
                    'file_path' => $destination,
                    'mime_type' => $file_type,
                ]);

                // broadcast
                broadcast(new MediaFileOpsNotifications([
                    'op' => 'upload',
                    'path' => $path,
                ]))->toOthers();

                $result = [
                    'success' => true,
                    'message' => $file_name,
                ];
            } catch (\Exception $e) {
                $result = [
                    'success' => false,
                    'message' => "$file_name\\" . $e->getMessage(),
                ];
            }
        } else {
            $result = [
                'success' => false,
                'message' => trans('messages.error.cant_upload'),
            ];
        }

        return response()->json($result);
    }

    /**
     * save file to disk.
     *
     * @param UploadedFile $file
     * @param $upload_path
     * @param $file_name
     * @return false|file|string
     */
    protected function storeFile(UploadedFile $file, $upload_path, $file_name)
    {
        return $file->storeAs($upload_path, $file_name, $this->fileSystem);
    }

    /**
     * allow/disallow user upload.
     *
     * @param (Symfony\Component\HttpFoundation\File\UploadedFile || null) $file
     *
     * @return [boolean]
     */
    protected function allowUpload($file = null)
    {
        return true;
    }

    /**
     * do something to file b4 its saved to the server.
     *
     * @param UploadedFile $file
     * @return UploadedFile $file
     */
    protected function optimizeUpload(UploadedFile $file)
    {
        return $file;
    }
}