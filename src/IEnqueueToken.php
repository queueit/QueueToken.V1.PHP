<?php

namespace QueueIT\QueueToken;

interface IEnqueueToken
{
    public function getTokenIdentifier(): string;

    public function getPayload(): ?IEnqueueTokenPayload;

    public function setPayload(?IEnqueueTokenPayload $payload): void;

    public function getTokenWithoutHash(): ?string;

    public function getToken(): string;

    public function getHashCode(): ?string;

    public function setHashCode(?string $hashCode): void;
}
