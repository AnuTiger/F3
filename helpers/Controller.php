<?php

abstract class Controller
{
	protected $app;
    protected $viewPath;

    public function __construct()
    {
    	$this->app = App::singleton();
    }
    public function beforeroute()
    {
    	if(is_api($this->app->f3['PATH'])){
    		header('Content-Type: application/json; charset=UTF-8');
    	}
    }
    protected function template($template, $params = [])
    {
        template($this->viewPath.$template, $params);
    }
    protected function params($params = null)
    {
    	$method = Route::instance()->type();
    	if(!$params) {
    		return $this->app->f3->get($method);
    	}else{
    		return $this->app->f3->get($method . '.' . $params);
    	}
    }
}
