<?php

abstract class Model extends Cortex
{
	protected $app;
	protected $db = 'DB';
	protected $fieldConf = array(
		'created_at' => array(
			'type' => Schema::DT_TIMESTAMP,
			'default' => Schema::DF_CURRENT_TIMESTAMP
			),
		'updated_at' => array(
			'type' => Schema::DT_TIMESTAMP,
			'default' => '0-0-0 0:0:0'
			),
		'deleted_at' => array(
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
		//$this->beforesave($this->validate(get_called_class()));
	}

	/*private function validate($caller, $parent) {
        $valid = true;
        foreach($this->getFieldConfiguration() as $field => $conf) {
            if(isset($conf['type']) && !isset($conf['relType'])){
                $val = $this->get($field);
                $model = strtolower(str_replace('\\','.',$class));
                // check required fields
                if ($valid && isset($conf['required']))
                    $valid = \Validation::instance()->required($val,$field,'error.'.$model.'.'.$field);
                // check unique
                if ($valid && isset($conf['unique']))
                    $valid = \Validation::instance()->unique($self,$val,$field,'error.'.$model.'.'.$field);
                if (!$valid)
                    break;
            }
        }
        return $valid;
    }*/
}
