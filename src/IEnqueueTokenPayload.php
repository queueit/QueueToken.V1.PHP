<?php

namespace QueueIT\QueueToken;

interface IEnqueueTokenPayload
{
    public function getKey(): ?string;

    public function getRelativeQuality(): ?float;

    public function getCustomData(): array;

    public function getTokenOrigin(): string;

    public function encryptAndEncode(#[\SensitiveParameter] string $secretKey, string $tokenIdentifier): string;

    public function serialize(): array;
}
