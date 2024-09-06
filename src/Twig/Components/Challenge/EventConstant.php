<?php

declare(strict_types=1);

/**
 * The query pending event.
 *
 * Returns no data.
 */
const QueryPendingEvent = 'challenge:query-pending';

/**
 * The query completed event.
 *
 * Returns an array with `result` field, which is an `array<array<string, mixed>>`.
 */
const QueryCompletedEvent = 'challenge:query-completed';

/**
 * The query failed event.
 *
 * Returns an array with `error` field, which is a `string`,
 * and `code` field, which is an `integer`.
 */
const QueryFailedEvent = 'challenge:query-failed';
