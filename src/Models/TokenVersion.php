<?php

namespace QueueIT\QueueToken\Models;
class TokenVersion {
    const QT1 = 'QT1';

    public static function getValues() {
        return [
            self::QT1,
        ];
    }
}
