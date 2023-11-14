<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Tag(
 *     name="Auth",
 *     description="Authentication related operations"
 * )
 * @OA\Info(
 *     title="Bleepus API",
 *     version="1.0.0",
 *     description="This is the Bleepus API.",
 * )
 * @OA\Server(
 *     url="http://127.0.0.1:8000/",
 *     description="API server"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
