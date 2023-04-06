<?php
namespace PharIo\Version;
class OrVersionConstraintGroup extends AbstractVersionConstraint {
    private $constraints = [];
    public function __construct($originalValue, array $constraints) {
        parent::__construct($originalValue);
        $this->constraints = $constraints;
    }
    public function complies(Version $version) {
        foreach ($this->constraints as $constraint) {
            if ($constraint->complies($version)) {
                return true;
            }
        }
        return false;
    }
}
