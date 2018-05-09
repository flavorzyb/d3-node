<?php
require __DIR__ . '/bootstrap.php';

$pdo = createPdo();

/**
 * @param PDO $pdo
 * @return array
 */
function getAllNodeData(PDO $pdo) {
    $sql = "SELECT id, name, weight FROM `tb_rule_node` WHERE `status` = 0";
    $query = $pdo->query($sql);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * @param PDO $pdo
 * @return array
 */
function getAllNodeRelations(PDO $pdo) {
    $sql = "SELECT id, node_id, parent_id FROM `tb_rule_node_relation` WHERE `status` = 0";
    $query = $pdo->query($sql);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * @param PDO $pdo
 * @return array
 */
function getAllNodeMap(PDO $pdo) {
    $sql = "SELECT id, r_n_id, r_id, params, `order` FROM `tb_rule_node_map` WHERE `status` = 0";
    $query = $pdo->query($sql);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function getAllRule(PDO $pdo) {
    $sql = "SELECT id, name, `type`, extend_type, expression, result FROM `tb_rule` WHERE `status` = 0";
    $query = $pdo->query($sql);
    return $query->fetchAll(PDO::FETCH_ASSOC);
}

function buildNodeTree($dataArray) {
    $result = [];
    foreach ($dataArray as $data) {
        $parent = intval($data['parent_id']);
        if (!isset($result[$parent])) {
            $result[$parent] = [];
        }
        $result[$parent][] = $data;
    }
    return $result;
}

$nodeArray = getAllNodeData($pdo);
$nodeRelationArray = getAllNodeRelations($pdo);
$nodeMapArray = getAllNodeMap($pdo);
$ruleArray = getAllRule($pdo);

$nodeTree = buildNodeTree($nodeRelationArray);

var_dump($nodeTree);
