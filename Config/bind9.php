<?php

declare(strict_types=1);

use App\Support\Env;

return [
    'zones_dir' => Env::get('BIND9_ZONES_DIR', '/etc/bind/zones'),
    'conf_dir' => Env::get('BIND9_CONF_DIR', '/etc/bind'),
    'named_conf' => Env::get('BIND9_NAMED_CONF', '/etc/bind/named.conf.local'),
    'checkzone' => Env::get('BIND9_CHECKZONE', '/usr/sbin/named-checkzone'),
    'checkconf' => Env::get('BIND9_CHECKCONF', '/usr/sbin/named-checkconf'),
    'rndc' => Env::get('BIND9_RNDC', '/usr/sbin/rndc'),
    'keygen' => Env::get('BIND9_KEYGEN', '/usr/sbin/dnssec-keygen'),
    'signzone' => Env::get('BIND9_SIGNZONE', '/usr/sbin/dnssec-signzone'),
    'user' => Env::get('BIND9_USER', 'bind'),
];
