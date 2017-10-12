<?php

use DB\Cortex;
use DB\SoftErase;
use DB\SQL\Schema;

abstract class Model extends Cortex {
    use Softerase;

    public $validator, $app, $db = 'DB';
    public $fieldConf = array(
        'created_at' => array(
            'type' => Schema::DT_TIMESTAMP,
            'default' => Schema::DF_CURRENT_TIMESTAMP
            ),
        'updated_at' => array(
            'type' => Schema::DT_TIMESTAMP,
            'default' => '0-0-0 0:0:0'
            )
        );

    public function __construct()
    {
        if (property_exists($this, 'fields')) {
            $this->fieldConf = array_merge($this->fields, $this->fieldConf);
        }

        parent::__construct();

        $this->app = f3();

        if($this->app->get('VALIDATE.MODELS')) {
            $saveHandler = function() {
                foreach($this->getFieldConfiguration() as $field => $conf) {
                    if(isset($conf['validate'])) {
                        $rules[$field] = $conf['validate'];
                        $data[$field] = $this->get($field);
                    }
                }

                $this->validator = Validator::instance()->validate($data, $rules);

                return $this->validator->passed() && count($this->validator->errors()) > 0;
            };

            $this->onload($saveHandler);
            $this->beforeinsert($saveHandler);
            $this->beforeupdate($saveHandler);
            $this->beforesave($saveHandler);
        }
    }

    public function set_token()
    {
        return hash_hmac('sha256', Str::random(40), $this->app->get('SALT'));
    }

    public function set_expireAt()
    {}
}
