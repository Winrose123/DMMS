<?php

class TestCase
{
    public function setUp(): void
    {
        // Base setup method that can be overridden by test classes
    }

    public function tearDown(): void
    {
        // Base teardown method that can be overridden by test classes
    }

    public function assertTrue($condition, $message = '')
    {
        if (!$condition) {
            throw new Exception($message ?: 'Failed asserting that condition is true');
        }
    }

    public function assertFalse($condition, $message = '')
    {
        if ($condition) {
            throw new Exception($message ?: 'Failed asserting that condition is false');
        }
    }

    public function assertEquals($expected, $actual, $message = '')
    {
        if ($expected !== $actual) {
            throw new Exception($message ?: "Failed asserting that '$actual' matches expected '$expected'");
        }
    }

    public function assertNotFalse($actual, $message = '')
    {
        if ($actual === false) {
            throw new Exception($message ?: 'Failed asserting that value is not false');
        }
    }
}
