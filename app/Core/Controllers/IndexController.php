<?php

namespace App\Core\Controllers;

use Controller;

class IndexController extends Controller
{
    public function getIndex()
    {
        $this->template('index');
    }
}
