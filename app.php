<?php

class app extends Prefab
{
    public $db;
    public $f3;
    public $session;
    public $authenticatedUser;

    public function __construct()
    {
        $this->f3 = $this->f3 ?: Base::instance();
    }
	public function __get($property)
    {
        return null !== $this->f3->get($property) ? $this->f3->get($property) : false;
    }
    public function __set($property, $value)
    {
        $this->f3->set($property,  $value);
    }
    public function __call($method, $args) {
        if(method_exists($this->f3, $method)) {
            return $this->f3->$method($args[0], isset($args[1])?$args[1]:null);
        }
    }
    static public function __callStatic($method, $args) {
        $f3 = f3();
        if(method_exists($f3, $method)) {
            return $f3->$method($args[0], isset($args[1])?$args[1]:null);
        }
    }

    public static function singleton()
    {
        if (Registry::exists('APP')) {
            $app = Registry::get('APP');
        } else {
            $app = new self;
            Registry::set('APP', $app);
        }
        return $app;
    }

    public function db()
    {
        return $this->db ?: $this->f3->DB;
    }

    public function session()
    {
        return $this->session ?: $this->f3->SESSION;
    }

    public function user($user = null)
    {
        if($user) {
            $this->f3->set('SESSION.USER', $user);
        }

        return $this->authenticatedUser ?: $this->authenticatedUser = $this->f3->get('SESSION.USER');
    }

    public function initialized($def = null)
    {
        return null !== $def ? $this->f3->set('INITIALIZED', $def) : $this->f3->get('INITIALIZED');
    }

    public function config($key = null)
    {
        return $this->f3->get($key ?: 'CONFIG');
    }

    public function status()
    {
        return $this->f3->IS_LIVE;
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
        $this->f3->run();
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
            $this->f3->config(base_path("{path}/{$file}"));
        } else {
            foreach (glob(base_path("{$path}/*.ini")) as $file) {
                $this->f3->config($file);
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
        if (!$this->f3->DEV) {
            $this->f3->set('DEBUG', 0);
        }
    }

    public function configureDB()
    {
        $type = strtolower($this->f3->DB_TYPE);

        if ($type == 'jig') {
            $this->db = new DB\Jig($this->f3->DB_PATH, DB\Jig::FORMAT_JSON);
        } elseif ($type == 'sql') {
            $this->db = new DB\SQL($this->f3->DB, $this->f3->DB_USER, $this->f3->DB_PSWD);
        } elseif ($type == 'mongo') {
            $this->db = new DB\Mongo($this->f3->DB, $this->f3->DB_USER);
        }
        $this->f3->set('DB', $this->db);
    }

    public function configureSession()
    {
        $type = strtolower($this->f3->SESSION);

        if ($type) {
            if ($this->f3->CSRF && ('jig' == $type || 'sql' == $type || 'mongo' == $type)) {
                $this->configureCSRF($type);
            } elseif ($this->f3->CSRF) {
                $this->session = new Session(null, 'CSRF');
            } else {
                if ($type == 'jig' || $type == 'mongo' || $type == 'sql') {
                    $session = str_ireplace('/', '', 'DB\/'.$this->_getDBType($type).'\Session');
                    $this->session = new $session($this->f3->DB);
                } else {
                    $this->session = new Session();
                }
            }
            $this->f3->set('SESSION', $this->session);
        }
    }

    public function configureCSRF($type)
    {
        $session = 'DB\\'.$this->_getDBType($type).'\\Session';
        $this->session = new $session($this->f3->DB, 'SESSIONS', null, 'CSRF');
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
        $this->f3->set('ASSETS.onFileNotFound', function ($file) {
            echo 'file not found: '.$file;
        });
    }

    public function registerErrorHandler()
    {
        if ($this->f3->DEV) {
            Falsum\Run::handler($this->f3->DEBUG != 3);
        } else {
            $this->f3->set('ONERROR', 'App\Core\Controllers\ErrorController->init');
        }
    }
}
