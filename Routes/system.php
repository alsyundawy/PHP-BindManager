<?php

declare(strict_types=1);

use App\Support\View;
use Nyholm\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

return [
['method'=>'GET','path'=>'/system','auth'=>true,'rate_limit'=>'web','handler'=>static fn(ServerRequestInterface $r):Response=>new Response(200,['Content-Type'=>'text/html; charset=UTF-8'],View::render('system/index'))],
['method'=>'GET','path'=>'/api/docs','auth'=>false,'rate_limit'=>'web','handler'=>static fn(ServerRequestInterface $r):Response=>new Response(200,['Content-Type'=>'text/html; charset=UTF-8'],View::render('system/api-docs'))],
];
