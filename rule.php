<?php
require __DIR__ . '/bootstrap.php';

$service = new RuleService();
$service->loadAllData();
$service->executeRuleExpression();
$expressionArray = $service->getRuleExpression();
$ruleArray = $service->getRuleArray();
$indexArray = $service->buildRuleExtendIndex();
