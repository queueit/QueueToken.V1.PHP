<?php

namespace QueueIT\QueueToken\Models;
class TokenOrigin {
    const CONNECTOR = 'Connector';
    const INVITE_ONLY = 'InviteOnly';

    public static function getValues() {
        return [
            self::CONNECTOR,
            self::INVITE_ONLY,
        ];
    }
}
