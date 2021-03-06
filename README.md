# GIGCODES Asset Manager

Gigcodes - Asset Manager is a package for easily uploading and attaching media files to models with Laravel.

## Features

- Filesystem-driven approach is easily configurable to allow any number of upload directories with different accessibility. Easily restrict uploads by MIME type, extension and/or aggregate type (e.g. `image` for JPEG, PNG or GIF).
- Many-to-many polymorphic relationships allow any number of media to be assigned to any number of other models without any need to modify their schema.
- Attach media to models with tags, in order to set and retrieve media for specific purposes, such as `'thumbnail'`, `'featured image'`, `'gallery'` or `'download'`.
- Integrated support for integration/image for manipulating image files to create variants for different use cases.

## Example Usage

Upload a file to the server, and place it in a directory on the filesystem disk named "uploads". This will create a Media record that can be used to refer to the file.

```php
$media = MediaUploader::fromSource($request->file('thumb'))
	->toDestination('uploads', 'blog/thumbnails')
	->upload();
```

Attach the Media to another eloquent model with one or more tags defining their relationship.

```php
$post = Post::create($this->request->input());
$post->attachMedia($media, ['thumbnail']);
```

Retrieve the media from the model by its tag(s).

```php
$post->getMedia('thumbnail')->first()->getUrl();
```

## Installation

Add the package to your Laravel app using composer

```bash
composer require gigcodes/asset-manager
```

Publish the config file (`config/asset_manager.php`) of the package using artisan.

```bash
php artisan vendor:publish --provider="Gigcodes\AssetManager\AssetManagerServiceProvider" 
```

Run the migrations to add the required tables to your database.

```bash
php artisan migrate
```

## Documentation

Read the documentation [here](https://gigcodes.com).

## License

This package is released under the MIT license (MIT).
