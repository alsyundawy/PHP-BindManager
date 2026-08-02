<?php

declare(strict_types=1);

namespace App\Repositories\System;

use PDO;

final class ActivityLogRepository
{
    public function __construct(private readonly PDO $pdo){}
    /** @param array<string,mixed> $context */public function write(?int $userId,string $category,string $action,string $message,array $context=[],?string $ip=null,?string $agent=null):void{$s=$this->pdo->prepare('INSERT INTO activity_logs(user_id,category,action,message,context,ip_address,user_agent) VALUES(:user_id,:category,:action,:message,:context,:ip_address,:user_agent)');$s->execute([':user_id'=>$userId,':category'=>$category,':action'=>$action,':message'=>$message,':context'=>json_encode($context,JSON_THROW_ON_ERROR),':ip_address'=>$ip,':user_agent'=>$agent]);}
}
