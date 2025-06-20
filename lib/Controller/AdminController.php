<?php
namespace OCA\LDAPViaCloudflareTunnel\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Http\DataResponse;
use OCP\IRequest;
use OCP\IConfig;
use OCP\Util;

class AdminController extends Controller
{

    private $config;

    public function __construct($AppName, IRequest $request, IConfig $config)
    {
        parent::__construct($AppName, $request);
        $this->config = $config;
    }

    /**
     * @AdminRequired
     */
    public function index()
    {
        // 管理画面テンプレートに設定値を渡す
        $data = [
            'cloudflaredPath' => $this->config->getAppValue('ldapvct', 'cloudflaredPath', __DIR__ . '/../../bin/cloudflared'),
            'tunnelName' => $this->config->getAppValue('ldapvct', 'tunnelName', 'ldap-tunnel'),
            'configPath' => $this->config->getAppValue('ldapvct', 'configPath', __DIR__ . '/../../config/config.yml'),
            'logFile' => $this->config->getAppValue('ldapvct', 'logFile', __DIR__ . '/../../tmp/cloudflared.log'),
        ];
        return new TemplateResponse('ldapvct', 'admin', $data);
    }

    /**
     * @AdminRequired
     * @NoCSRFRequired
     */
    public function saveSettings()
    {
        $cloudflaredPath = $this->request->getParam('cloudflaredPath');
        $tunnelName = $this->request->getParam('tunnelName');
        $configPath = $this->request->getParam('configPath');
        $logFile = $this->request->getParam('logFile');

        // 簡易バリデーション（省略可）

        $this->config->setAppValue('ldapvct', 'cloudflaredPath', $cloudflaredPath);
        $this->config->setAppValue('ldapvct', 'tunnelName', $tunnelName);
        $this->config->setAppValue('ldapvct', 'configPath', $configPath);
        $this->config->setAppValue('ldapvct', 'logFile', $logFile);

        Util::addSuccess('設定を保存しました。');
        return new DataResponse(['status' => 'success']);
    }


    /**
     * @AdminRequired
     * @NoCSRFRequired
     */
    public function uploadConfig()
    {
        $file = $_FILES['configFile'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return new DataResponse(['error' => 'ファイルのアップロードに失敗しました'], 400);
        }

        $targetPath = __DIR__ . '/../../config/config.yml';
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return new DataResponse(['error' => '保存に失敗しました'], 500);
        }

        return new DataResponse(['status' => 'uploaded']);
    }

    /**
     * @AdminRequired
     * @NoCSRFRequired
     */
    public function saveConfigText()
    {
        $content = $this->request->getParam('configContent');
        $targetPath = __DIR__ . '/../../config/config.yml';

        if (file_put_contents($targetPath, $content) === false) {
            return new DataResponse(['error' => 'ファイル保存に失敗しました'], 500);
        }

        return new DataResponse(['status' => 'saved']);
    }
}
