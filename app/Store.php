<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;

class Store{

    // Store Session
    public function setSession($gkey, $gval)
    {
        Session::put($gkey, $gval);
    }

    // Get Session
    public function getSession($gkey)
    {
        return Session::get($gkey);
    }

}
