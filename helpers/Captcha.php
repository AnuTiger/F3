<?php

class Captcha extends Prefab
{
	protected $name;
	protected $code;
	protected $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

	public function __construct($name = 'captcha-string')
	{
		$this->name = $name;
	}

	public function text($length = 4, $mock = null)
	{
		$this->setSession($code = $this->_generate($length));
		return $code;
	}

	public function img($length = 4, $class = 'captcha-img')
	{
		return '<img src="'.$this->text($length).'" class="'.$class.'" />';
	}

	public function source($length = 4, $mock = null)
	{
	}

	public function input($class = 'captcha-input')
	{
		return '<input type="text" class="'.$class.'" name="'.$this->name.'" />';
	}

	/*
    * Captcha::instance()->render(4)
    * Captcha::instance()->render(array('type'=>'img','length'=>4,'class'=>array('img'=>'captcha-img','input'=>'captcha-input')));
     */
	public function render($param, $url)
	{
		if (is_array($param)) {
			$func = null;
			$length = null;
			$imgClass = null;
			$inputClass = null;
			foreach ($param as $key => $val) {
				if ('type' == $key) {
					$func = $key;
				}
				if ('length' == $key) {
					$length = $key;
				}
				if ('class' == $key && is_array($key)) {
					if (array_key_exists('img', $key)) {
						$imgClass = $key['img'];
					} elseif (array_key_exists('input', $key)) {
						$inputClass = $key['input'];
					}
				} else {
					$imgClass = $inputClass = $key;
				}
			}
			if (method_exists($this, $func)) {
				$response = $this->$key($length, $imgClass).$this->input($inputClass);
			} else {
				throw new Exception("Error Processing Captcha Method", 1);
			}
		} elseif (is_numeric($param)) {
			$response = $this->img($param).$this->input();
		} else {
			throw new Exception("Error Processing Captcha Parameters", 1);
		}

		return '<form method="post" action="'.$url.'">'.$response.'</form>';
	}

	public function name($name)
	{
		$this->name = $name;
		return $this;
	}

	public function verify($code)
	{
		$this->startSession();

		$n = $this->name;

		$valid = isset($_SESSION[$n])
			&& isset($_POST[$n])
			&& strlen($_POST[$n])
			&& ($_SESSION[$n] === crypt(strtolower($_POST[$n]), $this->salt()));

		if (isset($_POST[$n])) {
			unset($_POST[$n]);
		}

		if ($valid && isset($_SESSION[$n])) {
			unset($_SESSION[$n]);
		}

		return $valid;
	}

	private function startSession()
	{
		session_id() || session_start();
	}

	private function setSession($string)
	{
		$this->startSession();
		$_SESSION[$this->name] = crypt(strtolower($string), $this->salt());
	}

	private function _generate($length)
	{
		return $this->code = substr(str_shuffle(str_repeat($this->pool, 5)), 0, $length);
	}

	private static function salt()
	{
		return md5(__FILE__.filemtime(__FILE__));
	}
}
