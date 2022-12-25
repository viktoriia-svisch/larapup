<?php
namespace Zend\Diactoros;
use InvalidArgumentException;
use Psr\Http\Message\UploadedFileInterface;
use function is_array;
function normalizeUploadedFiles(array $files)
{
    $recursiveNormalize = function (
        array $tmpNameTree,
        array $sizeTree,
        array $errorTree,
        array $nameTree = null,
        array $typeTree = null
    ) use (&$recursiveNormalize) {
        $normalized = [];
        foreach ($tmpNameTree as $key => $value) {
            if (is_array($value)) {
                $normalized[$key] = $recursiveNormalize(
                    $tmpNameTree[$key],
                    $sizeTree[$key],
                    $errorTree[$key],
                    isset($nameTree[$key]) ? $nameTree[$key] : null,
                    isset($typeTree[$key]) ? $typeTree[$key] : null
                );
                continue;
            }
            $normalized[$key] = createUploadedFile([
                'tmp_name' => $tmpNameTree[$key],
                'size' => $sizeTree[$key],
                'error' => $errorTree[$key],
                'name' => isset($nameTree[$key]) ? $nameTree[$key] : null,
                'type' => isset($typeTree[$key]) ? $typeTree[$key] : null
            ]);
        }
        return $normalized;
    };
    $normalizeUploadedFileSpecification = function (array $files = []) use (&$recursiveNormalize) {
        if (! isset($files['tmp_name']) || ! is_array($files['tmp_name'])
            || ! isset($files['size']) || ! is_array($files['size'])
            || ! isset($files['error']) || ! is_array($files['error'])
        ) {
            throw new InvalidArgumentException(sprintf(
                '$files provided to %s MUST contain each of the keys "tmp_name",'
                . ' "size", and "error", with each represented as an array;'
                . ' one or more were missing or non-array values',
                __FUNCTION__
            ));
        }
        return $recursiveNormalize(
            $files['tmp_name'],
            $files['size'],
            $files['error'],
            isset($files['name']) ? $files['name'] : null,
            isset($files['type']) ? $files['type'] : null
        );
    };
    $normalized = [];
    foreach ($files as $key => $value) {
        if ($value instanceof UploadedFileInterface) {
            $normalized[$key] = $value;
            continue;
        }
        if (is_array($value) && isset($value['tmp_name']) && is_array($value['tmp_name'])) {
            $normalized[$key] = $normalizeUploadedFileSpecification($value);
            continue;
        }
        if (is_array($value) && isset($value['tmp_name'])) {
            $normalized[$key] = createUploadedFile($value);
            continue;
        }
        if (is_array($value)) {
            $normalized[$key] = normalizeUploadedFiles($value);
            continue;
        }
        throw new InvalidArgumentException('Invalid value in files specification');
    }
    return $normalized;
}
