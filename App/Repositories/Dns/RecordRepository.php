<?php

declare(strict_types=1);

namespace App\Repositories\Dns;

use PDO;

final class RecordRepository
{
    public function __construct(private readonly PDO $pdo){}
    /** @return array<int,array<string,mixed>> */ public function forZone(int $zoneId):array{$s=$this->pdo->prepare('SELECT * FROM dns_records WHERE zone_id=:zone_id ORDER BY name,record_type');$s->execute([':zone_id'=>$zoneId]);return $s->fetchAll();}
    public function create(int $zoneId,array $data):int{$s=$this->pdo->prepare('INSERT INTO dns_records(zone_id,name,record_type,ttl,priority,content) VALUES(:zone_id,:name,:record_type,:ttl,:priority,:content)');$s->execute([':zone_id'=>$zoneId,':name'=>$data['name'],':record_type'=>$data['record_type'],':ttl'=>$data['ttl'],':priority'=>$data['priority'],':content'=>$data['content']]);return (int)$this->pdo->lastInsertId();}
    public function delete(int $id):void{$s=$this->pdo->prepare('DELETE FROM dns_records WHERE id=:id');$s->execute([':id'=>$id]);}
}
