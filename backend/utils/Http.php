<?php

/** Small helpers for reading the request in route handlers. */
class Http
{
    /** The JSON body of the request as an array (empty array if there is no valid body). */
    public static function body()
    {
        $data = Flight::request()->data->getData();
        return is_array($data) ? $data : [];
    }

    /** A query string value (?name=value), or null if it was not sent or is empty. */
    public static function query($name)
    {
        $value = Flight::request()->query[$name] ?? null;
        return ($value === null || $value === '') ? null : $value;
    }
}
