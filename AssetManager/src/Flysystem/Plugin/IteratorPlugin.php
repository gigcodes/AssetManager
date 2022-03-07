<?php

declare(strict_types=1);

namespace Gigcodes\AssetManager\Flysystem\Plugin;

use Gigcodes\AssetManager\Flysystem\FilesystemFilterIterator;
use Gigcodes\AssetManager\Flysystem\FilesystemIterator;
use Gigcodes\AssetManager\Flysystem\IteratorException;
use Gigcodes\AssetManager\Flysystem\Options\Options;
use Gigcodes\AssetManager\Flysystem\RecursiveFilesystemIteratorIterator;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\PluginInterface;

/**
 * Class FlysystemIteratorPlugin
 * @package Gigcodes\AssetManager\Flysystem\FlysystemIterator\Plugin
 */
class IteratorPlugin implements PluginInterface
{
    /** @var Filesystem $filesystem */
    private $filesystem;

    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod()
    {
        return 'createIterator';
    }

    /**
     * @param array $options
     * @param string [optional] $dir
     * @return FilesystemIterator
     * @throws IteratorException
     */
    public function handle(array $options = [], $dir = '/')
    {
        $iterator = new FilesystemIterator($this->filesystem, $dir, $options);
        $options = Options::fromArray($options);
        if ($options->{Options::OPTION_IS_RECURSIVE}) {
            $iterator = new RecursiveFilesystemIteratorIterator($iterator, $options);
        }
        if ($options->{Options::OPTION_FILTER} !== null) {
            if ($options->{Options::OPTION_RETURN_VALUE} !== Options::VALUE_LIST_INFO) {
                throw new IteratorException('Filters only work on list info return values.');
            }
            $iterator = new FilesystemFilterIterator($iterator, $options->{Options::OPTION_FILTER});
        }
        return $iterator;
    }

    /**
     * Set the Filesystem object.
     *
     * @param FilesystemInterface $filesystem
     */
    public function setFilesystem(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }
}
