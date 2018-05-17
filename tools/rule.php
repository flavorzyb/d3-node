<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require __DIR__.'/../bootstrap.php';

$service = new RuleService();
$service->loadAllData();
$service->executeRuleExpression();
$expressionArray = $service->getRuleExpression();
$ruleArray = $service->getRuleArray();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$titleArray = ["ID", "NAME", "VALUE"];
foreach ($titleArray as $k => $v) {
    $sheet->setCellValueByColumnAndRow($k + 1, 1, $v);
}

$row = 2;
foreach ($expressionArray as $id => $v) {
    $v = floatval($v);
    $sheet->setCellValueByColumnAndRow( 1, $row, $id);
    $sheet->setCellValueByColumnAndRow( 2, $row, trim($ruleArray[$id]['name']));
    $sheet->setCellValueByColumnAndRow( 3, $row, $v);
    $row++;
}

$writer = new Xlsx($spreadsheet);
$writer->save(__DIR__ . '/rule.xlsx');


$service->executeRuleExtendNameExpression();
$expressionArray = $service->getRuleExtendNameExpression();
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$titleArray = ["ID", "NAME", "EXPRESSION"];
foreach ($titleArray as $k => $v) {
    $sheet->setCellValueByColumnAndRow($k + 1, 1, $v);
}

$row = 2;
foreach ($expressionArray as $id => $v) {
    $sheet->setCellValueByColumnAndRow( 1, $row, $id);
    $sheet->setCellValueByColumnAndRow( 2, $row, trim($ruleArray[$id]['name']));
    $sheet->setCellValueByColumnAndRow( 3, $row, $v);
    $row++;
}

$writer = new Xlsx($spreadsheet);
$writer->save(__DIR__ . '/rule_extend.xlsx');
