<?php
class CoveredParentClass
{
    public function publicMethod(): void
    {
        $this->protectedMethod();
    }
    protected function protectedMethod(): void
    {
        $this->privateMethod();
    }
    private function privateMethod(): void
    {
    }
}
class CoveredClass extends CoveredParentClass
{
    public function publicMethod(): void
    {
        parent::publicMethod();
        $this->protectedMethod();
    }
    protected function protectedMethod(): void
    {
        parent::protectedMethod();
        $this->privateMethod();
    }
    private function privateMethod(): void
    {
    }
}
