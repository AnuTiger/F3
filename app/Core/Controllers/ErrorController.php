<?php

namespace App\Core\Controllers;

use Controller;

class ErrorController extends Controller
{
    public function init()
    {
        /*switch(f3()->get('ERROR.code')) {
            case 401:
                break;
            default :
                template('error');
        }*/
        template('error');
    }
}
