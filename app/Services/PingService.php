<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;

class PingService
{
    /**
     * Send a single ICMP echo request to the given IP address.
     *
     * Returns true only when the host answers. Invalid addresses and
     * unreachable hosts both return false.
     */
    public function ping(string $ip, int $timeoutInMilliseconds = 1500): bool
    {
        if (! filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }

        $command = $this->commandFor($ip, $timeoutInMilliseconds);

        try {
            return Process::timeout(8)->run($command)->successful();
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * @return list<string>
     */
    protected function commandFor(string $ip, int $timeoutInMilliseconds): array
    {
        if (PHP_OS_FAMILY === 'Windows') {
            return ['ping', '-n', '1', '-w', (string) $timeoutInMilliseconds, $ip];
        }

        $timeoutInSeconds = max(1, (int) ceil($timeoutInMilliseconds / 1000));

        return ['ping', '-c', '1', '-W', (string) $timeoutInSeconds, $ip];
    }
}
