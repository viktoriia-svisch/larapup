<?php
namespace Symfony\Component\HttpKernel;
final class KernelEvents
{
    const REQUEST = 'kernel.request';
    const EXCEPTION = 'kernel.exception';
    const VIEW = 'kernel.view';
    const CONTROLLER = 'kernel.controller';
    const CONTROLLER_ARGUMENTS = 'kernel.controller_arguments';
    const RESPONSE = 'kernel.response';
    const TERMINATE = 'kernel.terminate';
    const FINISH_REQUEST = 'kernel.finish_request';
}
