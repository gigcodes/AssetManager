<?php

namespace Gigcodes\AssetManager\Media;

use App\Http\Controllers\Controller;
use Gigcodes\AssetManager\Events\MediaFileOpsNotifications;
use Gigcodes\AssetManager\Flysystem\Plugin\IteratorPlugin;
use Gigcodes\AssetManager\Media\Modules\Delete;
use Gigcodes\AssetManager\Media\Modules\Download;
use Gigcodes\AssetManager\Media\Modules\GetContent;
use Gigcodes\AssetManager\Media\Modules\GlobalSearch;
use Gigcodes\AssetManager\Media\Modules\Move;
use Gigcodes\AssetManager\Media\Modules\Rename;
use Gigcodes\AssetManager\Media\Modules\Upload;
use Gigcodes\AssetManager\Media\Modules\Utils;
use Gigcodes\AssetManager\Media\Modules\Visibility;
use Gigcodes\AssetManager\Models\Media;
use Gigcodes\AssetManager\Resources\MediaIndexResource;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    use Utils,
        GetContent,
        Delete,
        Download,
        Move,
        Rename,
        Upload,
        Visibility,
        GlobalSearch;

    protected $baseUrl;
    protected $db;
    protected $fileChars;
    protected $fileSystem;
    protected $folderChars;
    protected $ignoreFiles;
    protected $LMF;
    protected $GFI;
    protected $sanitizedText;
    protected $storageDisk;
    protected $storageDiskInfo;
    protected $unallowedMimes;
    protected $paginationAmount;
    protected $config;

    public function __construct()
    {
        $config = config('asset_manager');
        $this->db = $config['model'] ?? Media::class;
        $this->fileSystem = $config['storage_disk'];
        $this->ignoreFiles = $config['ignore_files'];
        $this->fileChars = $config['allowed_fileNames_chars'];
        $this->folderChars = $config['allowed_folderNames_chars'];
        $this->sanitizedText = $config['sanitized_text'];
        $this->unallowedMimes = $config['unallowed_mimes'];
        $this->LMF = $config['last_modified_format'];
        $this->GFI = $config['get_folder_info'] ?? true;
        $this->paginationAmount = $config['pagination_amount'] ?? 50;
        $this->storageDisk = app('filesystem')->disk($this->fileSystem);
        $this->storageDiskInfo = app('config')->get("filesystems.disks.{$this->fileSystem}");
        $this->baseUrl = $this->storageDisk->url('/');

        $this->storageDisk->addPlugin(new IteratorPlugin());
    }


    public function index()
    {
        $data['title'] = 'Media Library';
        $data['url'] = '/';
        return view('backend.media.index', $data);
    }

    public function browse(Request $request, $container = 'main', $any = '')
    {
        $data['url'] = $any;
        return response()->json([
            'columns' => ['title'],
            'items' => [
                [
                    'assets' => $this->db::all()->count(),
                    'browse_url' => route('gigcodes.media.index'),
                    'edit_url' => route('gigcodes.media.index'),
                    'id' => 'main',
                    'title' => 'Main Assets'
                ]
            ]
        ]);
    }

    public function getContents(Request $request)
    {
        $path = $request->path;
        $path = explode('/', $path);
        $files = $this->getFiles($request);
        $page = $this->paginate($files, $this->paginationAmount);
        $paged = [];
        $paginations = $page->getUrlRange(0, $page->lastPage() - 1);
        foreach ($paginations as $index => $pagination) {
            $paged[] = [
                'page' => $index + 1,
                'url' => $pagination
            ];
        }


        return response()->json([
            'assets' => MediaIndexResource::collection($this->db::where('upload_path', $request->path)->get()),
            'container' => [
                'driver' => $this->storageDisk,
                'id' => 'main',
                'path' => 'app/media',
                'title' => 'Main Assets',
                'url' => route('gigcodes.media.index')
            ],
            'containers' => [
                'main' => []
            ],
            'folder' => [
                'parent_path' => $path[0] !== '' ? count($path) > 1 ? $path[0] : '/' : null,
                'path' => $files['path'] ?: '/',
                'title' => ''
            ],
            'folders' => $files['items']['folders'],
            'pagination' => [
                'totalItems' => $page->total(),
                'itemsPerPage' => $page->perPage(),
                'currentPage' => $page->currentPage(),
                'totalPages' => $page->lastPage(),
                'prevPage' => $page->previousPageUrl(),
                'nextPage' => $page->nextPageUrl(),
                'segments' => [
                    'slider' => [],
                    'last' => [],
                    'first' => $paged,
                ]
            ],
        ]);
    }

    public function newFolder(Request $request)
    {
        $path = $request->form['parent'];
        $new_folder_name = $this->cleanName($request->form['basename'], true);
        $full_path = !$path ? $new_folder_name : $this->clearDblSlash("$path/$new_folder_name");
        $message = null;

        if ($this->storageDisk->exists($full_path)) {
            $message = trans('messages.error.already_exists');
        } elseif (!$this->storageDisk->makeDirectory($full_path)) {
            $message = trans('messages.error.creating_dir');
        }

        // broadcast
        broadcast(new MediaFileOpsNotifications([
            'op' => 'new_folder',
            'path' => $path,
        ]))->toOthers();

        if (!$message) {
            return response()->json([
                'folder' => [
                    'path' => $full_path
                ]
            ]);
        } else {
            return response()->json([
                'folder' => [
                    'path' => $full_path
                ],
                'errors' => [
                    'message' => $message
                ]
            ]);
        }

    }

    public function getFolder(Request $request, $folder)
    {
        return response()->json([
            'parent_path' => null,
            'path' => $folder,
            'title' => $folder,
        ]);
    }

    public function deleteFolder(Request $request)
    {
        $item_path = $request->folders;

        $del = $this->storageDisk->deleteDirectory($item_path);
        if ($del) {
            return response()->json([
                'success' => true
            ]);
        } else {
            return response()->json([
                'success' => false
            ], 500);
        }
    }

    public function uploadFile(Request $request)
    {
        $upload_path = $request->folder;
        $random_name = true;
        $result = [];
        $one = $request->file('file');
        $container = $request->get('container');


        if ($this->allowUpload($one)) {
            $one = $this->optimizeUpload($one);
            $orig_name = $one->getClientOriginalName();
            $name_only = pathinfo($orig_name, PATHINFO_FILENAME);
            $ext_only = pathinfo($orig_name, PATHINFO_EXTENSION);
            $final_name = $random_name
                ? $this->getRandomString() . ".$ext_only"
                : $this->cleanName($name_only) . ".$ext_only";

            $file_type = $one->getClientMimeType();
            $file_size = $one->getSize();
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
                $media = $this->db::create([
                    'container' => $container,
                    'name' => $final_name,
                    'full_path' => $full_path,
                    'upload_path' => $upload_path,
                    'file_name' => $final_name,
                    'mime_type' => $file_type,
                    'disk' => $this->fileSystem,
                    'size' => $file_size,
                ]);


                return response()->json([
                    'success' => true,
                    'asset' => new MediaIndexResource($media),
                ]);
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
        return response()->json($result);
    }

    public function getFile(Request $request)
    {
        $assets = [];
        $items = $request->get('items');
        foreach ($items as $item) {
            if (strpos($item, '::') !== false) {
                $f = explode("::", $item);
                $media = new MediaIndexResource($this->db::where('name', $f[1])->first());
            } else {
                $media = new MediaIndexResource($this->db::where('id', $item)->first());
            }
            array_push($assets, $media);
        }
        return response()->json(['assets' => $assets]);
    }

    public function downloadFile($file)
    {
        return config('asset_manager')->where('id', $file)->first()->downloadFile();
    }

    public function deleteFile(Request $request)
    {
        $ids = $request->get('ids');
        foreach ($ids as $id) {
            $file_name = str_replace('main::', '', $id);
            $this->db::where('file_name', $file_name)->first()->delete();
        }
        return response()->json([
            'success' => true,
            'message' => 'Media deleted successfully'
        ]);
    }
}