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

/**
 * @param array $dataArray
 * @return array
 */
function buildNodeParentIndex(array $dataArray) {
    $result = [];
    foreach ($dataArray as $data) {
        $parentId = intval($data['parent_id']);
        $nodeId = intval($data['node_id']);
        $result[$nodeId] = $parentId;
    }

    return $result;
}

/**
 * @param array $indexArray
 * @return array
 */
function buildNodeTreeIndex(array $indexArray) {
    $result = [];

    foreach ($indexArray as $k => $v) {
        if ($k > 0) {
            $result[$k] = findParent($indexArray, $k, []);
        }
    }

    return $result;
}

/**
 * @param array $dataArray
 * @param $nodeId
 * @param array $path
 * @return array
 */
function findParent(array $dataArray, $nodeId, array $path) {

    if (isset($dataArray[$nodeId])) {
        $parentId = $dataArray[$nodeId];
        if ($parentId != 0) {
            $path[] = $parentId;
            return findParent($dataArray, $parentId, $path);
        }
    }

    $path[] = 0;
    return $path;
}

function formatNodeTree(array &$dataArray) {
    if (sizeof($dataArray) > 1 && isset($dataArray[0])) {
        unset($dataArray[0]);
    }

    foreach ($dataArray as $k => $v) {
        if (is_array($v)) {
            $dataArray[$k] = formatNodeTree($v);
        }
    }

    if ((sizeof($dataArray) == 1) && (isset($dataArray[0]))) {
        $dataArray = $dataArray[0];
    }

    return $dataArray;
}

function buildNodeNameMap(array $dataArray) {
    $result = [0 => ['name' => '风控根节点', 'id' => 0, 'weight' => 0]];
    foreach ($dataArray as $v) {
        $id = intval($v['id']);
        $result[$id] = ["id" => $id, 'name' => $v['name'], 'weight' => $v['weight']];
    }

    return $result;
}

$nodeArray = getAllNodeData($pdo);
$nodeNameArray = buildNodeNameMap($nodeArray);

$nodeRelationArray = getAllNodeRelations($pdo);
$nodeMapArray = getAllNodeMap($pdo);
$ruleArray = getAllRule($pdo);

$nodeParentIndexArray = buildNodeParentIndex($nodeRelationArray);
$nodeIndexArray = buildNodeTreeIndex($nodeParentIndexArray);

$dataArray = [];
$result = array_walk($nodeIndexArray,
    function ($item, $key) use (&$dataArray){
        $data = &$dataArray;
        foreach (array_reverse($item) as $v) {
            if (!isset($data[$v])) {
                $data[$v] = [$key];
            }

            $data = &$data[$v];
        }
    });
if (!$result) {
    die("build node fail.\n");
}


$dataArray = formatNodeTree($dataArray);

function output($data, array $nodeNameArray) {
    $result = "";
    if (is_array($data)) {
        foreach ($data as $k => $v) {
            $result .= "{";
            $result .= "\"name\": \"{$nodeNameArray[$k]['name']}\",";
            $result .= "\"children\": [";
            $result .= substr(output($v, $nodeNameArray), 0, -1);
            $result .= "]";
            $result .= "},";
        }
    } else {
        $result .= "{\"name\": \"{$nodeNameArray[$data]['name']}\"},";
    }

    return $result;
}

$dataArray = [0 => $dataArray];
unset($dataArray[0][1]);
$result = output($dataArray, $nodeNameArray);
$result = substr($result, 0 , -1);

$fp = fopen(__DIR__ . '/view/js/node_data.json', 'wb+');
fwrite($fp, $result);
fclose($fp);

echo "导出成功\n";
