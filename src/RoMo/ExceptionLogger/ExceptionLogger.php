<?php

declare(strict_types=1);

namespace RoMo\ExceptionLogger;

use pocketmine\command\CommandSender;
use pocketmine\Server;

class ExceptionLogger{
    public static function handleException(\Throwable $e, ?CommandSender $usingSender = null) : void{
        $server = Server::getInstance();
        $path = $server->getDataPath() . "exception_logs/";
        if(!is_dir($path)){
            mkdir($path);
        }
        $fileName = date("D_M_j-H.i.s-T_Y") . ".log";
        $path .= $fileName;
        $content = "error message: " . $e->getMessage() . "\n--- trace ---\n" . $e->getTraceAsString();
        if($e->getPrevious() !== null){
            $previous = $e->getPrevious();
            $content .= "\n\nprevious message: " . $previous->getMessage() . "\n--- previous trace ---\n" . $previous->getTraceAsString();
        }
        $server->getLogger()->error("New exception log has been created. log name: {$fileName}");
        file_put_contents($path, $content);

        $usingSender?->sendMessage("§l§6 • §r§7오류가 발생하였습니다. ({$fileName})");
        $usingSender?->sendMessage("§l§6 • §r§7관리자에게 해당 메시지가 포함된 스크린샷과 함께 문의해주세요.");
    }
}