<?php

declare(strict_types=1);

namespace RoMo\ExceptionLogger;

use naeng\DiscordCore\DiscordCore;
use naeng\DiscordCore\webhook\Webhook;
use pocketmine\command\CommandSender;
use pocketmine\Server;

class ExceptionLogger{

    private const WEBHOOK_URL = "https://discord.com/api/webhooks/1297461542039719986/B4DrsKkZVuXXIV_-mIKOi08wUJXhJtkqtXc9l3cxbHz1vD2tW72xggafBZdigRKCAtiv";

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
                // 1144216952311648328: 운영지 Role ID
                // 1145239939303350374: 개발자 Role ID
                $webhook = new Webhook();
                $webhook->setName("ExceptionLogger" . ($usingSender === null ? "" : ": " . $usingSender->getName()))
                    ->setContent("**오류 메시지**: {$e->getMessage()}\n\n자세한 오류는 **{$fileName}**를 확인해주세요.\n\n<@&1144216952311648328> <@&1145239939303350374>")
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
