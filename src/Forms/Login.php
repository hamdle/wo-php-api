<?php

/*
 * Forms/Login.php: handle login form tasks
 *
 * Copyright (C) 2021 Eric Marty
 */

namespace Forms;

use Http\Request;

class Login
{
    use \Traits\Attributes;
    use \Traits\Messages;

    private $config = [];

    public function __construct($attributes = [])
    {
        $this->config = [
            'email' => function ($entry) {
                if (empty($entry))
                    return "Email address should not be empty.";
                return true;
            },
            'password' => function ($entry) {
                if (empty($entry))
                    return "Password should not be empty.";
                return true;
            },
        ];
        $this->filter($attributes);
    }

    /*
     * Filter out attributes that are not in the config.
     * @return void
     */
    private function filter($attributes)
    {
        foreach ($attributes as $key => $attribute)
        {
            if (array_key_exists($key, $this->config))
                $this->attributes[$key] = $attribute;
        }
    }

    public function validate()
    {
        foreach ($this->config as $key => $validator)
        {
            if (array_key_exists($key, $this->attributes))
            {
                if (($validationResponse = $validator($this->attributes[$key])) !== true)
                    $this->messages[] = $validationResponse;
            }
        }

        return empty($this->messages) ? true : false;
    }
}