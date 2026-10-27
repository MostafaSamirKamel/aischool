<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'AI School API',
    description: 'API Documentation for AI School Management System - نظام إدارة المدرسة الذكية',
    contact: new OA\Contact(email: 'support@aischool.com')
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Development Server'
)]
#[OA\Server(
    url: 'https://api.aischool.com',
    description: 'Production Server'
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'Enter token in format: Bearer {token}'
)]
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
