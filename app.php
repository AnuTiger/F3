<?php

class app extends Prefab
{
	public $db;
	public $app;
	public $session;
	public $authenticatedUser;

	public function __construct()
	{
		$this->app = $this->app ?: Base::instance();
	}

	public static function singleton()
	{
		if (Registry::exists('APP')) {
			$app = Registry::get('APP');
		}else {
			$app = new self;
			Registry::set('APP', $app);
		}
		return $app;
	}

	public function db()
	{
		return $this->db ?: $this->app->DB;
	}

	public function session()
	{
		return $this->session ?: $this->app->SESSION;
	}

	public function user()
	{
		return $this->authenticatedUser = $this->app->get('SESSION.USER') ?: false;
	}

	public function initialized($def = null)
	{
		return null !== $def ? $this->app->set('INITIALIZED', $def) : $this->app->get('INITIALIZED');
	}

	public function config($key = null)
	{
		return $this->app->get($key ?: 'CONFIG');
	}

	public function status()
	{
		return $this->app->IS_LIVE;
	}

	public function run()
	{
		$this->loadConfig();
		$this->loadRoutes();
		$this->checkForMaintenance();
		$this->configureDebug();
		$this->configureDB();
		$this->configureSession();
		$this->configureAssets();
		$this->registerErrorHandler();
		$this->initialized(true);
		$this->app->run();
	}

	public function loadRoutes($file = null)
	{
		$this->_load('routes', $file);
	}

	public function loadConfig($file = null)
	{
		$this->_load('config', $file);
	}

	private function _load($path, $file = null)
	{
		if ($file) {
			$this->app->config(base_path("{path}/{$file}"));
		}else {
			foreach (glob(base_path("{$path}/*.ini")) as $file) {
				$this->app->config($file);
			}
		}
	}

	public function checkForMaintenance()
	{
		if (!$this->status()) {
			template('maintenance');
			exit();
		}
	}

	public function configureDebug()
	{
		if (!$this->app->DEV) {
			$this->app->set('DEBUG', 0);
		}
	}

	public function configureDB()
	{
		$type = strtolower($this->app->DB_TYPE);

		if ($type == 'jig') {
			$this->db = new DB\Jig($this->app->DB_PATH, DB\Jig::FORMAT_JSON);
		} elseif ($type == 'sql') {
			$this->db = new DB\SQL($this->app->DB, $this->app->DB_USER, $this->app->DB_PSWD);
		} elseif ($type == 'mongo') {
			$this->db = new DB\Mongo($this->app->DB, $this->app->DB_USER);
		}
		$this->app->set('DB', $this->db);
	}

	public function configureSession()
	{
		$type = strtolower($this->app->SESSION);

		if ($type) {
			if ($this->app->CSRF && ('jig' == $type || 'sql' == $type || 'mongo' == $type)) {
				$this->configureCSRF($type);
			} elseif ($this->app->CSRF) {
				$this->session = new Session(null, 'CSRF');
			}else {
				if ($type == 'jig' || $type == 'mongo' || $type == 'sql') {
					$session = str_ireplace('/', '', 'DB\/'.$this->_getDBType($type).'\Session');
					$this->session = new $session($this->app->DB);
				}else {
					$this->session = new Session();
				}
			}
			$this->app->set('SESSION', $this->session);
		}
	}

	public function configureCSRF($type)
	{
		$session = str_ireplace('/', '', 'DB\/'.$this->_getDBType($type).'\Session');
		$this->session = new $session($this->app->DB, 'sessions', null, 'CSRF');
	}

	private function _getDBType($type)
	{
		if ($type == 'jig' || $type == 'mongo') {
			return ucfirst($type);
		} elseif ($type == 'sql') {
			return strtoupper($type);
		}
	}

	public function configureAssets()
	{
		$assets = Assets::instance();
		$this->app->set('ASSETS.onFileNotFound', function($file) {
			echo 'file not found: '.$file;
		});
	}

	public function registerErrorHandler()
	{
		if ($this->app->DEV) {
			Falsum\Run::handler($this->app->DEBUG != 3);
		}else {
			$this->app->set('ONERROR', 'App\Core\Controllers\ErrorController->init');
		}
	}
}
