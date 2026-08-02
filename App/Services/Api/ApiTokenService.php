<?php

declare(strict_types=1);

namespace App\Services\Api;

use PDO;
use RuntimeException;

final class ApiTokenService
{
    public function __construct(private readonly PDO $pdo){}
    /** @return array{token:string,token_hash:string,expires_at:?string} */ public function issue(int $userId,string $name,array $scopes,?string $expiresAt=null):array{$plain='pbm_'.bin2hex(random_bytes(32));$hash=hash('sha256',$plain);$s=$this->pdo->prepare('INSERT INTO api_tokens(user_id,name,token_hash,scopes,expires_at) VALUES(:user_id,:name,:token_hash,:scopes,:expires_at)');$s->execute([':user_id'=>$userId,':name'=>$name,':token_hash'=>$hash,':scopes'=>json_encode(array_values($scopes),JSON_THROW_ON_ERROR),':expires_at'=>$expiresAt]);return ['token'=>$plain,'token_hash'=>$hash,'expires_at'=>$expiresAt];}
    public function authenticate(string $plain):?array{$s=$this->pdo->prepare('SELECT * FROM api_tokens WHERE token_hash=:token_hash AND revoked_at IS NULL AND (expires_at IS NULL OR expires_at>CURRENT_TIMESTAMP)');$s->execute([':token_hash'=>hash('sha256',$plain)]);$row=$s->fetch();if(!is_array($row))return null;$u=$this->pdo->prepare('UPDATE api_tokens SET last_used_at=CURRENT_TIMESTAMP WHERE id=:id');$u->execute([':id'=>$row['id']]);return $row;}
    public function revoke(int $id,int $userId):void{$s=$this->pdo->prepare('UPDATE api_tokens SET revoked_at=CURRENT_TIMESTAMP WHERE id=:id AND user_id=:user_id');$s->execute([':id'=>$id,':user_id'=>$userId]);}
}
