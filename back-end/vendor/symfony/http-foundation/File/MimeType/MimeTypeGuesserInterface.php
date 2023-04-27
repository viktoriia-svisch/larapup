<?php
namespace Symfony\Component\HttpFoundation\File\MimeType;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
interface MimeTypeGuesserInterface
{
    public function guess($path);
}
