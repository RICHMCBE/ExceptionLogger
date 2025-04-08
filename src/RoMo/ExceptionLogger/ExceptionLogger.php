<?php

declare(strict_types=1);

namespace RoMo\ExceptionLogger;

use naeng\DiscordCore\DiscordCore;
use naeng\DiscordCore\webhook\Webhook;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class ExceptionLogger{

    private const WEBHOOK_URL = "https://discord.com/api/webhooks/1359086375911161976/Pn9KuQEtAeVC_KbNbqeRsQ8uWU_768IKE8CNyGY93fELETGYo6RfO-H4mmldUVdwyUxT";
    private const ROLE_ID = "1359085817783390248"

    public static function handleException(\Throwable $e, ?CommandSender $usingSender = null) : void{
        try{
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

            if(class_exists(DiscordCore::class)){
                $webhook = new Webhook();
                $webhook->setName("ExceptionLogger" . ($usingSender === null ? "" : ": " . $usingSender->getName()))
                    ->setContent("**오류 메시지**: {$e->getMessage()}\n\n자세한 오류는 **{$fileName}**를 확인해주세요.\n\n<@&" . self::ROLE_ID . ">")
                    ->send(self::WEBHOOK_URL);
            }
        }catch(\Throwable $error){
            Server::getInstance()->getLogger()->error("An error occurred while logging the exception. Error: {$error->getMessage()}");
            Server::getInstance()->getLogger()->error($error->getTraceAsString());
            Server::getInstance()->getLogger()->error("Original error: {$e->getMessage()}");
            Server::getInstance()->getLogger()->error($e->getTraceAsString());
        }
    }
}
