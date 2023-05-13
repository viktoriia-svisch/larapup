<?php
namespace Illuminate\View\Engines;
use Exception;
use Throwable;
use Illuminate\Contracts\View\Engine;
use Symfony\Component\Debug\Exception\FatalThrowableError;
class PhpEngine implements Engine
{
    public function get($path, array $data = [])
    {
        return $this->evaluatePath($path, $data);
    }
    protected function evaluatePath($__path, $__data)
    {
        $obLevel = ob_get_level();
        ob_start();
        extract($__data, EXTR_SKIP);
        try {
            include $__path;
        } catch (Exception $e) {
            $this->handleViewException($e, $obLevel);
        } catch (Throwable $e) {
            $this->handleViewException(new FatalThrowableError($e), $obLevel);
        }
        return ltrim(ob_get_clean());
    }
    protected function handleViewException(Exception $e, $obLevel)
    {
        while (ob_get_level() > $obLevel) {
            ob_end_clean();
        }
        throw $e;
    }
}
