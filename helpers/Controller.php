<?php

abstract class Controller
{
	protected $viewPath;

	protected function view()
	{
	}

	protected function template($template)
	{
		template($this->viewPath.$template);
	}
}
