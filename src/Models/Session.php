<?php

/*
 * Models/Session.php: user session log
 *
 * Copyright (C) 2021 Eric Marty
 */

namespace Models;

use Http\Request;
use Http\Response;
use Database\Query;

class Session
{
    use \Traits\Attributes;
    use \Traits\Messages;

    /*
     * The Entry attributes defined in the database are:
     *
     * id
     * user_id
     * key
     * value
     */

    protected const SESSIONS_TABLE = 'sessions';
    const COOKIE_KEY = 'Session-Id';

    public function __construct($attributes = [])
    {
        $this->config = [
            'user_id' => function ($entry) {
                return $entry;
            },
            'token' => function ($entry) {
                return $entry;
            },
        ];
        $this->attributes = $attributes;
    }

    public function load()
    {
        $this->filter($this->attributes);
        $this->transform();

        $results = Query::select(self::SESSIONS_TABLE, "*", $this->attributes);

        if (array_key_exists(0, $results))
        {
            // Add to attributes
            foreach ($results[0] as $key => $value)
            {
                $this->attributes[$key] = $value;
            }
        }
        else
        {
            $this->messages[] = "Session not found.";
            return false;
        }

        return true;
    }

    public function setExpiredCookie()
    {
        Response::addExpiredCookie([self::COOKIE_KEY => $this->cookie]);
    }

    public function delete()
    {
        Query::delete(self::SESSIONS_TABLE, ['id' => $this->id]);
    }

    public function save()
    {
        Query::insert(
            self::SESSIONS_TABLE,
            ["user_id", "token"],
            [$this->user_id, $this->token]);
    }

    public function createNewCookie($user)
    {
        // Generate token and mac hash
        $token = bin2hex(random_bytes(128));
        $cookie = $user->email.":".$token;
        $mac = hash_hmac('sha256', $cookie, $_ENV['COOKIE_KEY']);
        $cookie .= ":".$mac;

        // Save token to the database
        $this->user_id = $user->id;
        $this->token = $token;
        $this->save();

        $this->cookie = $cookie;
    }

    public function addCookie()
    {
        Response::addCookie([self::COOKIE_KEY => $this->cookie]);
    }

    /**
     * Check to make sure a cookie sent from the client is valid.
     * @return false or \Models\User
     */
    public function verify()
    {
        $cookie = Request::cookie();
        foreach ($cookie as $key => $value) {
            if (strcmp($key, self::COOKIE_KEY) !== 0)
                continue;

            $parts = explode(":", $value);
            if (count($parts) !== 3)
                return false;

            $user = new User(['email' => $parts[0]]);
            if (!$user->load())
                return false;

            $this->user_id = $user->id;
            if (!$this->load())
            {
                $this->setExpiredCookie();
                return false;
            }
            $this->user = $user;

            $this->cookie = $user->email.":".$this->token;
            $mac = hash_hmac('sha256', $this->cookie, $_ENV['COOKIE_KEY']);

            if (hash_equals($mac, $parts[2]))
                return true;
        }

        return false;
    }
}
