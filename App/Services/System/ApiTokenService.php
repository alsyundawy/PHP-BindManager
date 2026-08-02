<?php

declare(strict_types=1);

namespace App\Services\System;

use PDO;
use RuntimeException;

final class ApiTokenService
{
    public function __construct(private readonly PDO $pdo){}
    /** @return array{token:string,id:int} */public function issue(int $userId,string $name,array $scopes,?string $expiresAt=null):array{$plain='pbm_'.bin2hex(random_bytes(32));$hash=hash('sha256',$plain,true);$s=$this->pdo->prepare('INSERT INTO api_tokens(user_id,name,token_hash,scopes,expires_at) VALUES(:user_id,:name,:token_hash,:scopes,:expires_at)');$s->execute([':user_id'=>$userId,':name'=>$name,':token_hash'=>base64_encode($hash),':scopes'=>json_encode(array_values($scopes),JSON_THROW_ON_ERROR),':expires_at'=>$expiresAt]);return ['token'=>$plain,'id'=>(int)$this->pdo->lastInsertId()];}
    public function authenticate(string $plain):?array{$hash=base64_encode(hash('sha256',$plain,true));$s=$this->pdo->prepare('SELECT * FROM api_tokens WHERE token_hash=:token_hash AND revoked_at IS NULL AND (expires_at IS NULL OR expires_at>CURRENT_TIMESTAMP) LIMIT 1');$s->execute([':token_hash'=>$hash]);$r=$s->fetch();if(!is_array($r))return null;$u=$this->pdo->prepare('UPDATE api_tokens SET last_used_at=CURRENT_TIMESTAMP WHERE id=:id');$u->execute([':id'=>$r['id']]);return $r;}
}
