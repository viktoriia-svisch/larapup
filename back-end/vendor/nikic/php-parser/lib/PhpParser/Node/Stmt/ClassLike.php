<?php declare(strict_types=1);
namespace PhpParser\Node\Stmt;
use PhpParser\Node;
abstract class ClassLike extends Node\Stmt
{
    public $name;
    public $stmts;
    public function getMethods() : array {
        $methods = [];
        foreach ($this->stmts as $stmt) {
            if ($stmt instanceof ClassMethod) {
                $methods[] = $stmt;
            }
        }
        return $methods;
    }
    public function getMethod(string $name) {
        $lowerName = strtolower($name);
        foreach ($this->stmts as $stmt) {
            if ($stmt instanceof ClassMethod && $lowerName === $stmt->name->toLowerString()) {
                return $stmt;
            }
        }
        return null;
    }
}
