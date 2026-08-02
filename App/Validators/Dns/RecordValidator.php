<?php

declare(strict_types=1);

namespace App\Validators\Dns;

use App\Exceptions\ValidationException;

final class RecordValidator
{
    /** @param array<string,mixed> $data */
    public function validate(array $data): array
    {
        $errors=[];$name=trim((string)($data['name']??'@'));$type=strtoupper(trim((string)($data['record_type']??'')));$content=trim((string)($data['content']??''));$ttl=(int)($data['ttl']??3600);
        $allowed=['A','AAAA','CAA','CNAME','DS','HTTPS','MX','NAPTR','NS','PTR','SOA','SRV','SSHFP','SVCB','TLSA','TXT'];
        if($name===''||strlen($name)>253)$errors['name'][]='Record owner name is invalid.';
        if(!in_array($type,$allowed,true))$errors['record_type'][]='Unsupported record type.';
        if($content===''||strlen($content)>65535)$errors['content'][]='Record content is required and must be <= 65535 bytes.';
        if($ttl<1||$ttl>2147483647)$errors['ttl'][]='TTL must be between 1 and 2147483647.';
        if($errors!==[])throw new ValidationException($errors);
        return ['name'=>$name,'record_type'=>$type,'content'=>$content,'ttl'=>$ttl,'priority'=>isset($data['priority'])?(int)$data['priority']:null];
    }
}
