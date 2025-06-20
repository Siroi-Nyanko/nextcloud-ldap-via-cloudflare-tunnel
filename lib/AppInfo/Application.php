<?php
namespace OCA\LDAPViaCloudflareTunnel\AppInfo;

use OCP\AppFramework\App;
use OCP\Util;

class Application extends App {
    public function __construct(array $params = []) {
        parent::__construct('ldapvct', $params);
        $container = $this->getContainer();

        $config = $container->getServer()->getConfig();

        // cloudflared のパスを取得
        $cloudflaredPath = $config->getAppValue('ldapvct', 'cloudflaredPath', __DIR__ . '/../../bin/cloudflared');

        $appManager = $container->getServer()->getAppManager();
        $appManager->registerEventListener('OCP\\App\\IAppManager::postEnableApp', function ($event) {
            if ($event->getAppName() === 'ldapvct') {
                $downloadUrl = 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64';
            }
        });
        // 実行ファイルが存在しない場合は警告
        if (!is_executable($cloudflaredPath)) {
            Util::writeLog('ldapvct', 'cloudflared 実行ファイルが見つからない、または実行できません: ' . $cloudflaredPath, Util::WARN);
            Util::addStyle('ldapvct', 'admin');
            Util::addScript('ldapvct', 'admin');
        }

    }

    private function Initialize()
    {
        // Create config directory if it doesn't exist
        $configDir = __DIR__ . '/../../config';
        if (!is_dir($configDir)) {
            if (!mkdir($configDir, 0755, true) && !is_dir($configDir)) {
                Util::writeLog('ldapvct', 'config ディレクトリの作成に失敗しました。', Util::ERROR);
                return;
            }
        }
        // Create config file if it doesn't exist
        $configFile = $configDir . '/config.yml';
        if (!file_exists($configFile)) {
            $defaultConfig = "tunnel:\n  name: ldap-tunnel\n  config: /var/www/html/config/config.yml\n";
            if (file_put_contents($configFile, $defaultConfig) === false) {
                Util::writeLog('ldapvct', 'config.yml の作成に失敗しました。', Util::ERROR);
                return;
            }
            Util::writeLog('ldapvct', 'config.yml を作成しました。', Util::INFO);
        }
        // Create bin directory if it doesn't exist
        $binDir = __DIR__ . '/../../bin';
        if (!is_dir($binDir)) {
            if (!mkdir($binDir, 0755, true) && !is_dir($binDir)) {
                Util::writeLog('ldapvct', 'bin ディレクトリの作成に失敗しました。', Util::ERROR);
                return;
            }
        }
        // Download cloudflared if it doesn't exist
        $cloudflaredPath = $binDir . '/cloudflared';
        if (!file_exists($cloudflaredPath)) {
            $downloadUrl = 'https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-linux-amd64';
            $this->downloadCloudflared($downloadUrl, $cloudflaredPath);
        } else {
            Util::writeLog('ldapvct', 'cloudflared は既に存在します。', Util::INFO);
        }
        // Set default config values
        $config = $this->getContainer()->getServer()->getConfig();
        $config->setAppValue('ldapvct', 'cloudflaredPath', $cloudflaredPath);
        $config->setAppValue('ldapvct', 'tunnelName', 'ldap-tunnel');
        $config->setAppValue('ldapvct', 'configPath', $configFile);
        $config->setAppValue('ldapvct', 'logFile', __DIR__ . '/../../tmp/cloudflared.log');
        Util::writeLog('ldapvct', '初期化が完了しました。', Util::INFO);

    }

    private function downloadCloudflared(string $url, string $targetPath): void {
        $data = @file_get_contents($url);
        if ($data === false) {
            Util::writeLog('ldapvct', 'cloudflared のダウンロードに失敗しました。', Util::ERROR);
            return;
        }

        if (@file_put_contents($targetPath, $data) === false) {
            Util::writeLog('ldapvct', 'cloudflared の保存に失敗しました。', Util::ERROR);
            return;
        }

        @chmod($targetPath, 0755);
        Util::writeLog('ldapvct', 'cloudflared を自動でダウンロードしました。', Util::INFO);
    }

}
