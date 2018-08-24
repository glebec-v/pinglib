<?php

namespace GlebecV\Model\Ping;

use Bestnetwork\Telnet\TelnetClient;
use Bestnetwork\Telnet\TelnetException;
use GlebecV\Exceptions\UhpHubTelnetException;
use GlebecV\HostInterface;
use GlebecV\PingInterface;

/**
 * Class UhpHubPinger
 * @package GlebecV\Model\Ping
 * uhpPrompt : '(SAVE)(NMS)29#' [chr(0x23).chr(0x20)]
 *
 * @property TelnetClient $telnetClient
 * @property string       $prompt
 */
class UhpHubPinger implements PingInterface
{
    private const PACKET_SIZE = 40;
    private const UHP_TRAILING_COMMAND = '';

    private $telnetClient;
    private $prompt;

    /**
     * UhpHubPinger constructor.
     */
    public function __construct()
    {}

    /**
     * @param string $sourceHost
     * @param int $port
     * @param string $prompt
     * @throws UhpHubTelnetException
     */
    public function connect(string $sourceHost, int $port = 23, string $prompt = '#'): void
    {
        $this->prompt = $prompt;
        try {
            $this->telnetClient = new TelnetClient($sourceHost, $port, 10, $prompt);
        } catch (TelnetException $exception) {
            throw new UhpHubTelnetException('Unable to connect '.$sourceHost.PHP_EOL, 1);
        }
    }

    /**
     * @param HostInterface $host -- destination host
     * @param int $count          -- packets count
     * @param int $timeout        -- waiting timeout
     * @param int $attempts       -- count ping cycle attempts
     * @return array
     * @throws UhpHubTelnetException
     */
    public function ping(HostInterface $host, int $count, int $timeout, int $attempts): array
    {
        $size = self::PACKET_SIZE;
        $timeout *= 1000; // in milliseconds
        $pingCommand = "ping {$host->ipAddress()} {$count} {$size} {$timeout} {$host->vlan()}";

        $counter = 0;
        do {
            $counter++;
            try {
                $this->telnetClient->execute($pingCommand, $this->prompt);
                $response = $this->telnetClient->execute(self::UHP_TRAILING_COMMAND, $this->prompt);
            } catch (TelnetException $exception) {
                throw new UhpHubTelnetException('Unable to ping '.$host->ipAddress().PHP_EOL, 1);
            }
            $result = $this->parseResponse($response);
            if ($attempts === $counter) {
                break;
            }
        } while (!$result['ok']);

        return [
            'ok'       => $result['ok'],
            'res'      => $result['info'],
            'attempts' => $counter
        ];
    }

    /**
     * @param string $data
     * @return array
     * @throws UhpHubTelnetException
     */
    private function parseResponse(string $data): array
    {
        $arr = explode("\r\n", $data);
        if (2 < count($arr) && isset($arr[count($arr)-2])) {
            $transmitted = (int)explode(';', $arr[count($arr)-2])[0];
            $lost = (int)explode(';', $arr[count($arr)-2])[1];
            return [
                'ok'   => ($transmitted - $lost) !== 0,
                'info' => $arr[count($arr)-1],
            ];
        } else {
            throw new UhpHubTelnetException('Connection lost', 1);

            // debug section:

            //$a = 1;
            //return [
            //    'ok'   => false,
            //    'info' => '',
            //];
        }
    }
}