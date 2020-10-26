<?php

/*
 * Utils/Router.php: match endpoints with the request's
 * uri path
 *
 * Copyright (C) 2020 Eric Marty
 */

namespace Utils;

use Http\Request;
use Controllers\Controller;

class Router
{
    private $endpoints;

    public function
    __construct($endpoints)
    {
        $this->endpoints = $endpoints;
    }

    public function
    getController() : \Controllers\Controller
    {
        $endpoints = $this->filterEndpoints();
        if (empty($endpoints))
            return new Controller('ErrorController', 'get');

        $requestParts = Request::path();
        foreach ($endpoints as $key => $value) {
            $match = true;
            $uriArgs = [];
            $keyParts = explode('/', $key);
            for ($n = 0; $n < count($requestParts); $n++) {
                if ($requestParts[$n] == $keyParts[$n])
                    continue;

                if (intval($requestParts[$n]) === 0)
                    $match = false;
                else {
                    $argKey = str_replace(['{', '}'], '', $keyParts[$n]);
                    $uriArgs[$argKey] = intval($requestParts[$n]);
                }
            }

            if ($match) {
                $parts = explode('.', $value);
                $class= $parts[0];
                $method = $parts[1];
                $args = [];
                $args['uri'] = $uriArgs;
                $args['data'] = Request::data();
                $args['post'] = Request::post();

                return new Controller($class, $method, $args);
            }
        }

        return new Controller('ErrorController', 'get');
    }

    private function
    filterEndpoints(): array
    {
        $filteredEndpoints = [];
        $requestParts = Request::path();

        foreach ($this->endpoints as $uri => $controller) {
            $uriParts = explode('/', $uri);
            $controllerParts = explode('.', $controller);
            $class = $controllerParts[0];
            $method = $controllerParts[1];

            if (count($requestParts) === count($uriParts) &&
                ($method == strtolower(Request::method()))) {
                $filteredEndpoints[$uri] = $controller;
            }
        }

        return $filteredEndpoints;
    }
}
