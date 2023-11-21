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
 *     url="http://ec2-16-171-116-255.eu-north-1.compute.amazonaws.com/",
 *     description="API server"
 * )
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
