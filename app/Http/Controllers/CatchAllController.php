<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;


class CatchAllController extends Controller{
    public function handleRequest($gateway, Request $request)
    {
        $any = $request->get('any', '');
        if ($config = config('proxy.'.$gateway, [])){
            $method = $requestMethod = $request->method();
            if(!in_array($method, ['get', 'post'])) {
                $requestMethod = 'all';
            }
            $headers = $request->header();
            $data = $request->$requestMethod();

            if(isset($headers['cookie'])) {
                unset($headers['cookie']);
            }
            if(isset($headers['host'])) {
                $headers['host'] = parse_url($config['url'], PHP_URL_HOST);
            }

            $response = Http::withHeaders($headers)
                ->$method($config['url'].'/'.$any, $data);
        
            return response($response->body())
                ->withHeaders($headers);
        }

        abort(404);
    }
}