<?php
class Swift_Mime_IdGenerator implements Swift_IdGenerator
{
    private $idRight;
    public function __construct($idRight)
    {
        $this->idRight = $idRight;
    }
    public function getIdRight()
    {
        return $this->idRight;
    }
    public function setIdRight($idRight)
    {
        $this->idRight = $idRight;
    }
    public function generateId()
    {
        return bin2hex(random_bytes(16)).'@'.$this->idRight;
    }
}
