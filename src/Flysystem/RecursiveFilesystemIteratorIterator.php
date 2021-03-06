<?php

declare(strict_types=1);

namespace Gigcodes\AssetManager\Flysystem;

use Countable;
use Gigcodes\AssetManager\Flysystem\Options\Options;
use JsonSerializable;
use RecursiveIteratorIterator;
use SeekableIterator;
use Traversable;

/**
 * Class RecursiveFilesystemIterator
 * @method FilesystemIterator getInnerIterator()
 * @method FilesystemIterator getSubIterator($level)
 */
class RecursiveFilesystemIteratorIterator extends RecursiveIteratorIterator implements Countable, SeekableIterator, JsonSerializable
{
    use SeekableIteratorTrait, JsonSerializableIteratorTrait, CountableIteratorTrait;

    /** @var int */
    private $globalIndex = 0;

    /** @var Options|null */
    private $options;

    /**
     * RecursiveFilesystemIterator constructor.
     * @param Traversable $iterator
     * @param Options $options
     */
    public function __construct(Traversable $iterator, Options $options = null)
    {
        parent::__construct($iterator, static::SELF_FIRST, 0);
        $this->options = $options;
        $this->rewind();
    }

    public function next()
    {
        parent::next();
        ++$this->globalIndex;
    }

    public function key() : int
    {
       return $this->globalIndex;
    }

    public function rewind()
    {
        parent::rewind();
        $this->skipRootDirectory();
        $this->globalIndex = 0;
    }

    private function skipRootDirectory()
    {
        if ($this->options !== null
            && $this->options->{Options::OPTION_SKIP_ROOT_DIRECTORY} === true
            && $this->currentItemIsDirectory()
        ) {
            $this->next();
        }
    }

    /**
     * @return bool
     */
    private function currentItemIsDirectory() : bool
    {
        $item = $this->current();
        if (is_array($item)) {
            return $item['type'] === 'dir';
        }
        return preg_match('~.*/$~', $item) === 1;
    }
}
