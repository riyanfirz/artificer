<?php namespace Mascame\Artificer;

use App;
use Mascame\Artificer\Http\Controllers\BaseController;
use Mascame\Artificer\Http\Controllers\BaseModelController;
use Mascame\Artificer\Extension\PluginManager;

class Artificer
{

    public static $booted = false;

    public static function isBooted() {
        return self::$booted;
    }

    public static function assets()
    {
        return BaseController::assets();
    }

    public static function getCurrentModelId($items)
    {
        return BaseModelController::getCurrentModelId($items);
    }

    /**
     * @param $t
     * @return bool
     */
    public static function isClosure($t)
    {
        return is_object($t) && ($t instanceof \Closure);
    }

    /**
     * @return PluginManager
     */
    public static function pluginManager()
    {
        return App::make('ArtificerPluginManager');
    }
    /**
     * @param $plugin
     * @return mixed
     */
    public static function getPlugin($plugin)
    {
        return with(App::make('ArtificerPluginManager'))->make($plugin);
    }

    // Todo is it used anywhere?
//    public static function store($filepath = null, $content, $overide = false)
//    {
//        if (!$filepath) {
//            $pathinfo = pathinfo($filepath);
//            $filepath = $pathinfo['dirname'];
//        }
//
//        $path = explode('/', $filepath);
//        array_pop($path);
//        $path = join('/', $path);
//
//        if (!file_exists($path)) {
//            \File::makeDirectory($path, 0777, true, true);
//        }
//
//        if (!file_exists($filepath) || $overide) {
//            return \File::put($filepath, $content);
//        }
//
//        return false;
//    }
}