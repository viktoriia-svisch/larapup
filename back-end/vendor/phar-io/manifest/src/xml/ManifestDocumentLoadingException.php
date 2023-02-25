<?php
namespace PharIo\Manifest;
use LibXMLError;
class ManifestDocumentLoadingException extends \Exception implements Exception {
    private $libxmlErrors;
    public function __construct(array $libxmlErrors) {
        $this->libxmlErrors = $libxmlErrors;
        $first              = $this->libxmlErrors[0];
        parent::__construct(
            sprintf(
                '%s (Line: %d / Column: %d / File: %s)',
                $first->message,
                $first->line,
                $first->column,
                $first->file
            ),
            $first->code
        );
    }
    public function getLibxmlErrors() {
        return $this->libxmlErrors;
    }
}
