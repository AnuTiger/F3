<?php

namespace App\Core\Models;

use DB\SQL\Schema;

class Hashtag extends Model
{
    protected $table = 'hashtags';
    protected $fields = array(
        'tag' => array(
            'type' => Schema::DT_TEXT,
            'required' => true,
            'nullable' => false
            )
        );
}
