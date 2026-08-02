<?php

declare(strict_types=1);

namespace App\Validators\Dns;

use App\Exceptions\ValidationException;

final class ZoneValidator
{
    /** @param array<string,mixed> $data */
    public function validate(array $data): array
    {
        $errors=[];$name=strtolower(trim((string)($data['name']??'')));$type=strtolower(trim((string)($data['zone_type']??'master')));
        if($name===''||strlen($name)>253||!preg_match('/^(?=.{1,253}\.?$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}\.?$/i',$name))$errors['name'][]='A valid fully-qualified DNS name is required.';
        $allowed=['master','slave','forward','hint','stub','static-stub','redirect','delegation-only'];if(!in_array($type,$allowed,true))$errors['zone_type'][]='Unsupported zone type.';
        if($errors!==[])throw new ValidationException($errors);
        return ['name'=>rtrim($name,'.').'.','zone_type'=>$type];
    }
}
