<?php
    class DataBank
    {
        private $cache;
        private static $instance = NULL;
        private static $cache_dir = NULL;

        private static function instance()
        {
            if(self::$instance === NULL)
                self::$instance = new DataBank();
            
            return self::$instance;
        }

        private static function cacheDir()
        {
            if(self::$cache_dir !== NULL)
                $ret = self::$cache_dir;
            else
                $ret = __DIR__.'/';
            
            return $ret;
        }

        private function __construct()
        {
            $this->cache = array();

            if(file_exists(self::cacheDir().'/.databank'))
                $this->cache = unserialize(file_get_contents(self::cacheDir().'/.databank'));
        }

        public static function autoload($dir = NULL)
        {
            if($dir)
                self::$cache_dir = $dir;

            return function($class)
            {
                if(!$ret = DataBank::cached($class))
                {
                    $default = RocketSled::defaultAutoload();
                    $ret = DataBank::cache($class,$default($class));
                }

                
                if($ret)
                    require_once($ret);

                return $ret;
            };
        }
        
        public static function cached($class)
        {
            $ret = FALSE;

            if(isset(self::instance()->cache[$class]))
                $ret = self::instance()->cache[$class];
            
            return $ret;
        }
        
        public static function cache($class,$path)
        {
            if($path)
                self::instance()->cache[$class] = $path;

            return $path;
        }
        
        public function __destruct()
        {
            $cache_name = self::cacheDir().'/.databank';
            
            if(is_writable($cache_name))
                file_put_contents($cache_name,serialize($this->cache));
        }
    }
