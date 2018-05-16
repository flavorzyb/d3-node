<?php
require __DIR__ . '/bootstrap.php';

$service = new RuleService();
$service->loadAllData();
$service->executeRuleExpression();
$expressionArray = $service->getRuleExpression();
$ruleArray = $service->getRuleArray();

$fp = fopen(__DIR__ . '/view/js/rule_data.json', 'wb+');
$result = '[';
foreach ($expressionArray as $id => $v) {
    $str = '{';
    $v = floatval($v);
    $name = trim($ruleArray[$id]['name']);
    $str .= "\"id\": \"{$id}\",\"value\":\"{$v}\",\"name\":\"{$name}\"";
    $str.= '},';
    $result .= $str;
}

$result = substr($result, 0, -1);
$result .= ']';
fwrite($fp, $result);
fclose($fp);
