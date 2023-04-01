<?php
namespace PharIo\Manifest;
class InvalidApplicationNameException extends \InvalidArgumentException implements Exception {
    const NotAString    = 1;
    const InvalidFormat = 2;
}
