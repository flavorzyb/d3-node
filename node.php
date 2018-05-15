<?php
require __DIR__ . '/bootstrap.php';

$service = new NodeService();
$service->loadAllData();

$nodeNameArray = $service->buildNodeNameMap();
$dataArray = $service->buildNodeTree();

$result = $service->output($dataArray, $nodeNameArray);
$result = substr($result, 0 , -1);

$fp = fopen(__DIR__ . '/view/js/node_data.json', 'wb+');
fwrite($fp, $result);
fclose($fp);

echo "导出成功\n";
