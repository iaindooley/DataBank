<?php
    class DataBank
    {
        private $cache;
        private static $instance = NULL;

        private static function instance()
        {
            if(self::$instance === NULL)
                self::$instance = new DataBank();
            
            return self::$instance;
        }

        private function __construct()
        {
            $this->cache = array();

            if(file_exists(__DIR__.'/.databank'))
                $this->cache = unserialize(file_get_contents(__DIR__.'/.databank'));
        }

        public static function autoload()
        {
            return function($class)
            {
                if(!$ret = DataBank::cached($class))
                {
                    $default = RocketSled::defaultAutoload();
                    $ret = DataBank::cache($class,$default($class));
                }
            
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
            self::instance()->cache[$class] = $path;
            return $path;
        }
        
        public function __destruct()
        {
            file_put_contents(__DIR__.'/.databank',serialize($this->cache));
        }
    }
