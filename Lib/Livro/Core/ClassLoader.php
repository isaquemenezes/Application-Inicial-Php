<?php
    namespace Livro\Core;

    class ClassLoader {
        protected $prefixes = array();

        public function register (){
            spl_autoload_register (array($this, 'loadClass'));
        }

        public function addNamespace($prefix, $base_dir, $prepend = false) {
            //normalize namespace prefix
            $prefix = trim($prefix, '\\'). '\\';

            //normalizar o diretório base com um separador à direita
            $base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR). '/';

            // inicializar o namespace prefix array
            if(isset($this->prefixes[$prefix]) === false) {
               $this->prefixes[$prefix] = array(); 
            }

            //retém o diretório base para o prefixo do namespace
            if ($prepend) {
                array_unshift($this->prefixes[$prefix], $base_dir);
            } else {
                array_push($this->prefixes[$prefix], $base_dir);
            }

        } 

        public function loadClass($class) {
            $prefix = $class;

            while (false !== $pos = strrpos($prefix, '\\')) {
                $prefix = substr($class, 0, $pos +1);

                $relative_class = substr($class, $pos +1);

                $mapped_file = $this->loadMappedFile($prefix, $relative_class);
                if ($mapped_file) {
                    return $mapped_file;
                }

                $prefix = rtrim($prefix, '\\');
            }

            return false;
            
        }

        protected function LoadMappedFile($prefix, $relative_class) {
            if(isset($this->prefixes[$prefix]) === false) {
                return false;
            }

            foreach($this->prefixes[$prefix] as $base_dir) {
                $files = $base_dir
                        . str_replace('\\', '/', $relative_class)
                        . '.php';

                if($this->requireFile($file)) {
                    return $file;
                }
            }

            return false;
        }

        protected function requireFile($file){
            if(file_exists($file)) {
                require $file;
                return true;
            }
            return false;
        }
    }