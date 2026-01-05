<?php

namespace QueueIT\QueueToken\Tests;

class SampleTokenValues
{
    public static string $secretKey = "5ebbf794-1665-4d48-80d6-21ac34be7faedf9e10b3-551a-4682-bb77-fee59d6355d6";

    public static string $customerId = "ticketania";

    public static string $eventId = "myevent";

    public static string $customDataKey = "somekey";

    public static array $customData = [
        "color" => "blue",
        "size" => "medium",
    ];

    public static string $originConnector = "connector";

    public static ?float $relativeQuality = 0.45678663514;

    public static string $testToken = "eyJ0eXAiOiJRVDEiLCJlbmMiOiJBRVMyNTYiLCJpc3MiOjE1MzQ3MjMyMDAwMDAsImV4cCI6MTUzOTEyOTYwMDAwMCwidGkiOiJhMjFkNDIzYS00M2ZkLTQ4MjEtODRmYS00MzkwZjZhMmZkM2UiLCJjIjoidGlja2V0YW5pYSIsImUiOiJteWV2ZW50In0.0rDlI69F1Dx4Twps5qD4cQrbXbCRiezBd6fH1PVm6CnVY456FALkAhN3rgVrh_PGCJHcEXN5zoqFg65MH8WZcxl-G7_FAsZgEyBPRqsoJoylWJjVe-e1HI-voBaV7x6Q.bRyvSKj3Dn0w_HPer62N0mVQP4dNPl5OIcXw7DrTH5g";
}
