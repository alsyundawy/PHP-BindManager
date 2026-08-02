<?php

declare(strict_types=1);

namespace App\Repositories\Api;

use PDO;

final class ApiTokenRepository
{
    public function __construct(private readonly PDO $pdo){}
    public function create(int $userId,string $name,string $hash,array $scopes,?string $expiresAt):int{$s=$this->pdo->prepare('INSERT INTO api_tokens(user_id,name,token_hash,scopes_json,expires_at) VALUES(:user_id,:name,:token_hash,:scopes_json,:expires_at)');$s->execute([':user_id'=>$userId,':name'=>$name,':token_hash'=>$hash,':scopes_json'=>json_encode(array_values($scopes),JSON_THROW_ON_ERROR),':expires_at'=>$expiresAt]);return(int)$this->pdo->lastInsertId();}
    public function findByHash(string $hash):?array{$s=$this->pdo->prepare('SELECT * FROM api_tokens WHERE token_hash=:token_hash LIMIT 1');$s->execute([':token_hash'=>$hash]);$r=$s->fetch();return is_array($r)?$r:null;}
    public function touch(int $id):void{$s=$this->pdo->prepare('UPDATE api_tokens SET last_used_at=CURRENT_TIMESTAMP WHERE id=:id');$s->execute([':id'=>$id]);}
}
