<?php

namespace QueueIT\QueueToken\Tests;

use PHPUnit\Framework\TestCase;

use QueueIT\QueueToken\IEnqueueTokenPayload;
use QueueIT\QueueToken\Token;
use QueueIT\QueueToken\Exceptions\TokenDeserializationException;
use QueueIT\QueueToken\Exceptions\TokenSerializationException;

class SecretKeyRedactionTest extends TestCase
{
    private $previousExceptionIgnoreArgs;

    protected function setUp(): void
    {
        // Production php.ini files strip all arguments from stack traces; development setups keep them
        $this->previousExceptionIgnoreArgs = ini_set('zend.exception_ignore_args', '0');
    }

    protected function tearDown(): void
    {
        if ($this->previousExceptionIgnoreArgs !== false) {
            ini_set('zend.exception_ignore_args', $this->previousExceptionIgnoreArgs);
        }
    }

    public function testSecretKeyIsRedactedFromParseStackTraces()
    {
        try {
            Token::parse(SignedTokens::withUndecryptablePayload(), SampleTokenValues::$secretKey);
            $this->fail("Expected a TokenDeserializationException");
        } catch (TokenDeserializationException $ex) {
            $this->assertSecretKeyRedacted($ex);
            $this->assertSecretKeyRedacted($ex->internalException);
        }
    }

    public function testSecretKeyIsRedactedFromGenerateStackTraces()
    {
        $payload = $this->createStub(IEnqueueTokenPayload::class);
        $payload->method('encryptAndEncode')->willThrowException(new \Exception("payload failure"));

        try {
            Token::enqueue(SampleTokenValues::$customerId)
                ->withPayload($payload)
                ->generate(SampleTokenValues::$secretKey);
            $this->fail("Expected a TokenSerializationException");
        } catch (TokenSerializationException $ex) {
            $this->assertSecretKeyRedacted($ex);
        }
    }

    public static function secretKeyParameters(): array
    {
        $parameters = [];
        $sourceDirectory = dirname(__DIR__) . '/src';
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($sourceDirectory));

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = substr($file->getPathname(), strlen($sourceDirectory) + 1, -strlen('.php'));
            $class = new \ReflectionClass('QueueIT\\QueueToken\\' . str_replace('/', '\\', $relativePath));
            foreach ($class->getMethods() as $method) {
                foreach ($method->getParameters() as $parameter) {
                    if ($parameter->getName() === 'secretKey') {
                        $parameters[$class->getName() . '::' . $method->getName()] = [$parameter];
                    }
                }
            }
        }

        return $parameters;
    }

    /**
     * @dataProvider secretKeyParameters
     */
    public function testEverySecretKeyParameterIsSensitive(\ReflectionParameter $parameter)
    {
        $this->assertNotEmpty($parameter->getAttributes(\SensitiveParameter::class));
    }

    private function assertSecretKeyRedacted(\Throwable $ex): void
    {
        $redactedArguments = 0;

        foreach ($ex->getTrace() as $frame) {
            $class = $frame['class'] ?? '';
            if (!str_starts_with($class, 'QueueIT\\QueueToken\\') || str_starts_with($class, 'QueueIT\\QueueToken\\Tests\\')) {
                continue;
            }

            foreach ($frame['args'] ?? [] as $argument) {
                $this->assertNotSame(
                    SampleTokenValues::$secretKey,
                    $argument,
                    "The secret key is in the stack trace of {$class}::{$frame['function']}"
                );
                if ($argument instanceof \SensitiveParameterValue) {
                    $redactedArguments++;
                }
            }
        }

        $this->assertGreaterThan(0, $redactedArguments, "No redacted argument in the stack trace");
    }
}
