<?php
namespace Psy\VersionUpdater;
interface Checker
{
    const ALWAYS  = 'always';
    const DAILY   = 'daily';
    const WEEKLY  = 'weekly';
    const MONTHLY = 'monthly';
    const NEVER   = 'never';
    public function isLatest();
    public function getLatest();
}
