<?php
/**
 * Formats a duration in seconds into a human-readable string (e.g., "1h 30m", "45m").
 *
 * @param int $seconds
 * @return string
 */
function formatDuration($seconds)
{
    if ($seconds < 60) {
        return $seconds . 's';
    }

    $minutes = floor($seconds / 60);
    $hours = floor($minutes / 60);
    $minutes = $minutes % 60;

    if ($hours > 0) {
        return $hours . 'h ' . ($minutes > 0 ? $minutes . 'm' : '');
    }

    return $minutes . 'm';
}
?>