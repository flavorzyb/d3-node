<?php
require __DIR__ . '/bootstrap.php';

$service = new RuleService();
$service->loadAllData();
$service->executeRuleExpression();
$expressionArray = $service->getRuleExpression();
$ruleArray = $service->getRuleArray();
$service->executeRuleExtendNameExpression();
$nameArray = $service->getRuleExtendNameExpression();
$service->findRuleChildren();
$rootExtendArray = $service->buildRuleExtendRoot();
foreach ($rootExtendArray as $v) {
    echo sprintf("%d, %s\n", $v['id'], $v['name']);
}
