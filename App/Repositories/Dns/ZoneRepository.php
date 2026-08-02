<?php

declare(strict_types=1);

namespace App\Repositories\Dns;

use PDO;

final class ZoneRepository
{
    public function __construct(private readonly PDO $pdo){}
    /** @return array<int,array<string,mixed>> */ public function all():array{return $this->pdo->query('SELECT * FROM zones ORDER BY name')->fetchAll();}
    public function find(int $id):?array{$s=$this->pdo->prepare('SELECT * FROM zones WHERE id=:id');$s->execute([':id'=>$id]);$r=$s->fetch();return is_array($r)?$r:null;}
    public function create(array $data):int{$s=$this->pdo->prepare('INSERT INTO zones(name,zone_type,file_path,view_id,status) VALUES(:name,:zone_type,:file_path,:view_id,:status)');$s->execute([':name'=>$data['name'],':zone_type'=>$data['zone_type'],':file_path'=>$data['file_path'],':view_id'=>$data['view_id']??null,':status'=>'draft']);return (int)$this->pdo->lastInsertId();}
    public function updateStatus(int $id,string $status):void{$s=$this->pdo->prepare('UPDATE zones SET status=:status,updated_at=CURRENT_TIMESTAMP WHERE id=:id');$s->execute([':status'=>$status,':id'=>$id]);}
}
