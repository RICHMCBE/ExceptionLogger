<?php

declare(strict_types=1);

namespace RoMo\ExceptionLogger;

use pocketmine\Server;

class ExceptionLogger{
    public static function handleException(\Throwable $e) : void{
        $server = Server::getInstance();
        $path = $server->getDataPath() . "exception_logs/";
        if(!is_dir($path)){
            mkdir($path);
        }
        $path .= date("D_M_j-H.i.s-T_Y") . ".log";
        $content = "error message: " . $e->getMessage() . "\n--- trace ---" . $e->getTraceAsString();
        if($e->getPrevious() !== null){
            $previous = $e->getPrevious();
            $content .= "\n\nprevious message: " . $previous->getMessage() . "\n--- previous trace ---" . $previous->getTraceAsString();
        }
        $server->getLogger()->error("New exception log has been created. log path: {$path}");
        file_put_contents($path, $content);
    }
}