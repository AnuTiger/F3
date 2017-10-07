<?php

class Route extends Prefab
{
	private $app;

	public function __construct()
	{
		if ($this->app == null) {
			$this->app = f3();
		}
	}

	public function uri()
	{
		return $this->app['URI'];
	}

	public function type()
	{
		return $this->app['VERB'];
	}

	public function has($route)
	{
		if (str_contains($route, '/')) {
			return array_key_exists($route, $this->getRoutes());
		}

		return $this->getNamedRoute($route);
	}

	public function current()
	{
		return $this->app['PATH'];
	}

	public function is($route)
	{
		return Str::contains($this->current(), $route);
	}

	public function currentRouteName()
	{
		return $this->getRoutes()[$this->current()][0][$this->type()][3];
	}

	public function getRouteName($route)
	{
		$response = null;
		foreach ($this->getNamedRoutes() as $name => $url) {
			if ($url == $route) {
				$response[] = $name;
			}
		}
		return $response;
		//return array_search($this->getNamedRoutes(), $route);
	}

	public function getNamedRoute($route)
	{
		return array_key_exists($route, $this->getNamedRoutes());
	}

	public function getNamedRoutes()
	{
		return $this->app['ALIASES'];
	}

	public function getRoutes()
	{
		return $this->app['ROUTES'];
	}

	public function hasParameter($parameter)
	{
		return (bool) $this->parameter($parameter);
	}

	public function parameters()
	{
		return $this->app[$this->type()];
	}

	public function parameter($parameter)
	{
		return $this->app[$this->type().'.'.$parameter];
	}
}
