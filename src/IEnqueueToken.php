<?php

namespace QueueIT\QueueToken;

interface IEnqueueToken
{
    public function TokenIdentifier();

    public function getPayload();

    public function setPayload($payload);

    public function getTokenWithoutHash();

    public function getToken();

    public function getHashCode();

    public function setHashCode($hashCode);
}