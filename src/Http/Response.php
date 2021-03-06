<?php

/*
 * Http/Response.php: define and send http responses
 *
 * Copyright (C) 2021 Eric Marty
 */

namespace Http;

class Response
{
    /*
     * Content type.
     * @var string
     */
    const JSON_CONTENT_TYPE = 'Content-Type: application/json; charset=utf-8';

    /*
     * Http status codes.
     * @vars numeric
     */
    const HTTP_200_OK = 200;
    const HTTP_201_CREATED = 201;
    const HTTP_204_DELETED = 204;
    const HTTP_400_BAD_REQUEST = 400;
    const HTTP_401_UNAUTHORIZED = 401;
    const HTTP_403_FORBIDDEN = 403;
    const HTTP_404_NOT_FOUND = 404;
    const HTTP_422_UNPROCESSABLE_ENTITY = 422;
    const HTTP_500_INTERNAL_SERVER_ERROR = 500;

    /*
     * You can't explicitly delete cookies so just set them as expired. But
     * gnerally, this would be triggered because the client-side cookie had
     * expired and has already been deleted by the browser.
     * @param map - key => value pair to map to the cookie.
     */
    public static function addExpiredCookie($map)
    {
        foreach ($map as $key => $value)
        {
            setcookie($key, $value, strtotime('-30 days'), "/");
        }
    }

    /*
     * Set cookie.
     * @param map - key => value pair to map to the cookie.
     */
    public static function addCookie($map)
    {
        foreach ($map as $key => $value)
        {
            setcookie($key, $value, strtotime('+30 days'), "/");
        }
    }

    /*
     * Send an Http response.
     * @param $code - numeric
     * @param $date - string
     */
    public static function send($code, $data = null)
    {
        header(self::JSON_CONTENT_TYPE);
        /*
         * Currenly, CORS policy is set in the Nginx config. To set policy in
         * PHP, you can set it here using:
         *
         * header("Access-Control-Allow-Origin: " . $_ENV['ORIGIN']);
         */
        http_response_code($code);
        if (!is_null($data))
            echo json_encode($data);
    }

    /*
     * Send an Http response and exit program.
     * @param $code - numeric
     * @param $date - string
     */
    public static function sendAndExit($code, $data = null)
    {
        self::send($code, $data);
        exit;
    }

    /*
     * Send a default Http response.
     */
    public static function sendDefault()
    {
        return self::send(Response::HTTP_404_NOT_FOUND, "Not found");
    }
}
