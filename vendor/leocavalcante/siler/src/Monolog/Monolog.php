<?php declare(strict_types=1);

namespace Siler\Monolog;

use Exception;
use InvalidArgumentException;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

const MONOLOG_DEFAULT_CHANNEL = 'log';

/**
 * Stores to any stream resource
 * Can be used to store into php://stderr, remote and local files, etc.
 *
 * @param resource|string $stream
 * @param int $level The minimum logging level at which this handler will be triggered
 * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
 * @param int|null $filePermission Optional file permissions (default (0644) are only for owner read/write)
 * @param bool $useLocking Try to lock log file before doing any writes
 *
 * @return StreamHandler
 * @throws InvalidArgumentException If stream is not a resource or string
 *
 * @throws Exception                If a missing directory is not buildable
 */
function stream(
    $stream,
    int $level = Logger::DEBUG,
    bool $bubble = true,
    ?int $filePermission = null,
    bool $useLocking = false
): StreamHandler {
    return new StreamHandler($stream, $level, $bubble, $filePermission, $useLocking);
}

/**
 * Adds a log record at an arbitrary level.
 * This function allows for compatibility with common interfaces.
 *
 * @param int $level The log level.
 * @param string $message The log message.
 * @param array $context The log context.
 * @param string $channel The log channel.
 *
 * @return void
 */
function log(int $level, string $message, array $context = [], string $channel = MONOLOG_DEFAULT_CHANNEL): void
{
    if (Loggers::gate($level)) {
        $logger = Loggers::getLogger($channel);
        $logger->log($level, $message, $context);
    }
}

/**
 * Add a gate to the log level.
 *
 * @param int $level
 * @param bool|callable():bool $predicate
 */
function log_if(int $level, $predicate): void
{
    Loggers::logIf($level, $predicate);
}

/**
 * Pushes a handler on to the stack.
 *
 * @param HandlerInterface $handler The handler to be pushed.
 * @param string $channel The Logger channel.
 *
 * @return Logger Returns the Logger.
 */
function handler(HandlerInterface $handler, string $channel = MONOLOG_DEFAULT_CHANNEL): Logger
{
    $logger = Loggers::getLogger($channel);

    return $logger->pushHandler($handler);
}

/**
 * Detailed debug information.
 *
 * @param string $message The log message.
 * @param array $context The log context.
 * @param string $channel The log channel.
 *
 * @return void
 */
function debug(string $message, array $context = [], string $channel = MONOLOG_DEFAULT_CHANNEL): void
{
    log(Logger::DEBUG, $message, $context, $channel);
}

/**
 * @param bool|callable():bool $predicate
 */
function debug_if($predicate): void
{
    log_if(Logger::DEBUG, $predicate);
}

/**
 * Interesting events. Examples: User logs in, SQL logs.
 *
 * @param string $message The log message.
 * @param array $context The log context.
 * @param string $channel The log channel.
 *
 * @return void
 */
function info(string $message, array $context = [], string $channel = MONOLOG_DEFAULT_CHANNEL): void
{
    log(Logger::INFO, $message, $context, $channel);
}

/**
 * Normal but significant events.
 *
 * @param string $message The log message.
 * @param array $context The log context.
 * @param string $channel The log channel.
 *
 * @return void
 */
function notice(string $message, array $context = [], string $channel = MONOLOG_DEFAULT_CHANNEL): void
{
    log(Logger::NOTICE, $message, $context, $channel);
}

/**
 * Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
 *
 * @param string $message The log message.
 * @param array $context The log context.
 * @param string $channel The log channel.
 *
 * @return void
 */
function warning(string $message, array $context = [], string $channel = MONOLOG_DEFAULT_CHANNEL): void
{
    log(Logger::WARNING, $message, $context, $channel);
}

/**
 * Runtime errors that do not require immediate action but should typically be logged and monitored.
 *
 * @param string $message The log message.
 * @param array $context The log context.
 * @param string $channel The log channel.
 *
 * @return void
 */
function error(string $message, array $context = [], string $channel = MONOLOG_DEFAULT_CHANNEL): void
{
    log(Logger::ERROR, $message, $context, $channel);
}

/**
 * Critical conditions. Example: Application component unavailable, unexpected exception.
 *
 * @param string $message The log message.
 * @param array $context The log context.
 * @param string $channel The log channel.
 *
 * @return void
 */
function critical(string $message, array $context = [], string $channel = MONOLOG_DEFAULT_CHANNEL): void
{
    log(Logger::CRITICAL, $message, $context, $channel);
}

/**
 * Action must be taken immediately. Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.
 *
 * @param string $message The log message.
 * @param array $context The log context.
 * @param string $channel The log channel.
 *
 * @return void
 */
function alert(string $message, array $context = [], string $channel = MONOLOG_DEFAULT_CHANNEL): void
{
    log(Logger::ALERT, $message, $context, $channel);
}

/**
 * Emergency: system is unusable.
 *
 * @param string $message The log message.
 * @param array $context The log context.
 * @param string $channel The log channel.
 *
 * @return void
 */
function emergency(string $message, array $context = [], string $channel = MONOLOG_DEFAULT_CHANNEL): void
{
    log(Logger::EMERGENCY, $message, $context, $channel);
}
