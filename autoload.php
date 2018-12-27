<?php
class AutoLoader
{

    public static $rootDir;

    public static function autoload($className)
    {
        if (strpos($className, '\\') !== false && strpos($className, 'app\\') !== false) {
            $classFile = implode(DIRECTORY_SEPARATOR, [
                static::$rootDir,
                ltrim(str_replace(['app', '\\', '/'], DIRECTORY_SEPARATOR, $className) . '.php', DIRECTORY_SEPARATOR)
            ]);
            if ($classFile === false || !is_file($classFile)) {
                return;
            }
        } else {
            return;
        }
        require_once $classFile;
        if (!class_exists($className, false) && !interface_exists($className, false) && !trait_exists($className, false)) {
            throw new Exception("Unable to find '$className' in file: $classFile. Namespace missing?");
        }
    }
}

AutoLoader::$rootDir = dirname(__FILE__);
spl_autoload_register(['AutoLoader', 'autoload'], true, true);