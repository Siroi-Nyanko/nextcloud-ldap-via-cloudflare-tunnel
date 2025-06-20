<?php
return [
  'routes' => [
    ['name' => 'admin#index','url' => '/admin','verb' => 'GET','controller' => 'Admin','action' => 'index'],
    ['name' => 'admin#saveSettings','url' => '/admin/save','verb' => 'POST','controller' => 'Admin','action' => 'saveSettings'],
    ['name' => 'admin#uploadConfig','url' => '/admin/uploadConfig','verb' => 'POST','controller' => 'Admin','action' => 'uploadConfig'],
    ['name' => 'admin#saveConfigText','url' => '/admin/saveConfigText','verb' => 'POST','controller' => 'Admin','action' => 'saveConfigText'],
    ['name' => 'tunnel#start','url' => '/tunnel/start','verb' => 'POST','controller' => 'Tunnel','action' => 'start'],
    ['name' => 'tunnel#stop','url' => '/tunnel/stop','verb' => 'POST','controller' => 'Tunnel','action' => 'stop'],
    ['name' => 'tunnel#status','url' => '/tunnel/status','verb' => 'GET','controller' => 'Tunnel','action' => 'status'],
  ],
];