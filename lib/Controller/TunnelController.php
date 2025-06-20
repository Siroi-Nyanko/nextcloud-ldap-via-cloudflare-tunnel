<?php
namespace OCA\LDAPViaCloudflareTunnel\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;

class TunnelController extends Controller {

    private $config;

    public function __construct($AppName, IRequest $request, \OCP\IConfig $config) {
        parent::__construct($AppName, $request);
        $this->config = $config;
    }

    /**
     * @AdminRequired
     * @NoCSRFRequired
     */
    public function start() {
        if (!function_exists('shell_exec')) {
            return new DataResponse(['error' => 'shell_exec is disabled'], 500);
        }

        $cloudflaredPath = $this->config->getAppValue('ldapvct', 'cloudflaredPath');
        $configPath = $this->config->getAppValue('ldapvct', 'configPath');
        $logFile = $this->config->getAppValue('ldapvct', 'logFile');
        $pidFile = __DIR__ . '/../../tmp/cloudflared.pid';

        // PID確認
        if (file_exists($pidFile)) {
            $pid = trim(file_get_contents($pidFile));
            if (posix_getpgid((int)$pid)) {
                return new DataResponse(['status' => 'already running', 'pid' => $pid]);
            }
        }

        $cmd = sprintf(
            'nohup %s tunnel --config %s run > %s 2>&1 & echo $!',
            escapeshellcmd($cloudflaredPath),
            escapeshellarg($configPath),
            escapeshellarg($logFile)
        );

        $pid = shell_exec($cmd);
        if ($pid && is_numeric(trim($pid))) {
            file_put_contents($pidFile, trim($pid));
            return new DataResponse(['status' => 'started', 'pid' => trim($pid)]);
        } else {
            return new DataResponse(['error' => 'Failed to start tunnel'], 500);
        }
    }

    /**
     * @AdminRequired
     * @NoCSRFRequired
     */
    public function stop() {
        $pidFile = __DIR__ . '/../../tmp/cloudflared.pid';
        if (!file_exists($pidFile)) {
            return new DataResponse(['status' => 'not running']);
        }

        $pid = trim(file_get_contents($pidFile));
        if (posix_getpgid((int)$pid)) {
            exec('kill ' . escapeshellarg($pid));
            unlink($pidFile);
            return new DataResponse(['status' => 'stopped']);
        } else {
            unlink($pidFile);
            return new DataResponse(['status' => 'not running']);
        }
    }

    /**
     * @AdminRequired
     */
    public function status() {
        $pidFile = __DIR__ . '/../../tmp/cloudflared.pid';
        if (file_exists($pidFile)) {
            $pid = trim(file_get_contents($pidFile));
            if (posix_getpgid((int)$pid)) {
                return new DataResponse(['status' => 'running', 'pid' => $pid]);
            }
        }
        return new DataResponse(['status' => 'stopped']);
    }
}
