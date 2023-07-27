<?php

namespace App\Services\Utils;

use Storage;

class StorageObject
{

    public function __construct(
        protected string $path,
        protected string $disk = 's3',
    ) {}

    public static function new(...$args)
    {
        return new self(...$args);
    }

    public function __toString()
    {
        return $this->path;
    }

    public function temporaryUrl(...$args)
    {
        return Storage::disk( $this->disk )->temporaryUrl($this->path, ...$args);
    }

    public function download(...$args)
    {
        return Storage::disk( $this->disk )->download($this->path, ...$args);
    }

    public function exists(...$args)
    {
        return Storage::disk( $this->disk )->exists($this->path, ...$args);
    }

    public function get(...$args)
    {
        return Storage::disk( $this->disk )->get($this->path, ...$args);
    }

    public function lastModified(...$args)
    {
        return Storage::disk( $this->disk )->lastModified($this->path, ...$args);
    }

    public function missing(...$args)
    {
        return Storage::disk( $this->disk )->missing($this->path, ...$args);
    }

    public function path(...$args)
    {
        return Storage::disk( $this->disk )->path($this->path, ...$args);
    }

    public function put(...$args)
    {
        return Storage::disk( $this->disk )->put($this->path, ...$args);
    }

    public function size(...$args)
    {
        return Storage::disk( $this->disk )->size($this->path, ...$args);
    }

    public function url(...$args)
    {
        return Storage::disk( $this->disk )->url($this->path, ...$args);
    }

    public function allFiles(...$args)
    {
        return Storage::disk( $this->disk )->allFiles($this->path, ...$args);
    }

    public function setVisibility(...$args)
    {
        return Storage::disk( $this->disk )->setVisibility($this->path, ...$args);
    }
}


