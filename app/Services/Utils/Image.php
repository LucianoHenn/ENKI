<?php
namespace App\Services\Utils;

use Imagick;

class Image
{
    protected Imagick $imagick;

    public function __construct( string $content )
    {
        $this->imagick = new Imagick();
        $this->imagick->readImageBlob( $content );
    }

    public function getSize(): int
    {
        return $this->imagick->getImageLength();
    }

    public function getContent(): string
    {
        return $this->imagick->getImageBlob();
    }

    public function getWidth(): int
    {
        return $this->imagick->getImageWidth();
    }

    public function getHeight(): int
    {
        return $this->imagick->getImageHeight();
    }

    public function getMimeType(): string
    {
        return $this->imagick->getImageMimeType();
    }

    public function getExtension(): string
    {
        return $this->imagick->getImageFormat();
    }

    public function stripMeta(): self
    {
        $clone = clone $this->imagick;
        $clone->stripImage();
        return new self( $clone->getImageBlob());
    }
}
