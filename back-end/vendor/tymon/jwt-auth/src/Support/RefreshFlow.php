<?php
namespace Tymon\JWTAuth\Support;
trait RefreshFlow
{
    protected $refreshFlow = false;
    public function setRefreshFlow($refreshFlow = true)
    {
        $this->refreshFlow = $refreshFlow;
        return $this;
    }
}
