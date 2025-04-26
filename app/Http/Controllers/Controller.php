<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[
    OA\Info(
        title: 'Sales & Order Management API',
        version: '1.0.0',
        description: 'Technical test for backend developer position at PT. Dibuiltadi Teknologi Kreatif',
    ),
    OA\Server(url: 'http://localhost:8000/api/v1', description: 'Local V1'),
    OA\Contact(
        name: 'Abel Ardhana Simanungkalit',
        url: 'https://github.com/abela-a',
        email: 'work.abelardhana@gmail.com',
    )
]
abstract class Controller
{
    //
}
