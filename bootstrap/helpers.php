<?php

function f3($get = null)
{
	$f3 = Base::instance();
	return $get ? $f3->get($get) : $f3;
}

function base_path($inner = '')
{
	return realpath(__DIR__.'/../').'/'.$inner;
}
function storage_path($inner = '')
{
	return base_path('storage/').'/'.$inner;
}
function db_path($db = '')
{
	return storage_path('db/').$db;
}
function lang_path($lang = '')
{
	return base_path('resources/lang/').'/'.$lang;
}
function views_path()
{
	return base_path('resources/views/');
}
function config_path($config = '')
{
	return base_path('config/').'/'.$config;
}

function abort()
{
	f3()->abort();
}
function status($code = 404)
{
	f3()->error($code);
}
function reroute($where)
{
	f3()->reroute($where);
}
function is_api($path)
{
	if (is_string($path)) {
		return explode('/', $path)[1] === 'api';
	}
	return false;
}

function template($template, array $params = [], $mime = 'text/html')
{
	$f3 = f3();
	if (!empty($params)) {
		$f3->mset($params);
	}
	if (is_array($template)) {
		$layout = $template[0];
		$view = $template[1];
	}else {
		$layout = 'layouts/app.htm';
		$view = $template;
	}
	$f3->set('user', App::instance()->user());
	$f3->set('content', extension($view, 'htm'));
	echo Template::instance()->render($layout, $mime);
}

function str_contains($haystack, $needles)
{
	foreach ((array) $needles as $needle) {
		if ($needle != '' && mb_strpos($haystack, $needle) !== false) {
			return true;
		}
	}
	return false;
}
function extension($file, $default = 'json')
{
	return $file.'.'.(pathinfo($file, PATHINFO_EXTENSION) ?: $default);
}
function flash($message, $type = 'success')
{
	Flash::instance()->addMessage($message, $type);
}
function trans($key, $params = null)
{
	return f3()->format(f3()->get($key), ($params ?: ''));
}
function error($error)
{
	if (null === $error) {
		return;
	}
	if (is_array($error)) {
		foreach ($error as $err) {
			if (is_array($err)) {
				foreach ($err as $e) {
					flash($e, 'danger');
				}
			}else {
				flash($err, 'danger');
			}
		}
	}else {
		flash($error, 'danger');
	}
}
