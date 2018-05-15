<?php

class NodeService
{
    /**
     * @var PDO
     */
    private $pdo = null;

    /**
     * @var array
     */
    private $nodeArray = [];

    /**
     * @var array
     */
    private $nodeRelationArray = [];

    /**
     * @var array
     */
    private $nodeMapArray = [];

    /**
     * @var array
     */
    private $ruleArray = [];

    /**
     * NodeService constructor.
     */
    public function __construct()
    {
        $this->pdo = createPdo();
    }

    /**
     * 加载所有需要的数据
     */
    public function loadAllData() {
        $this->loadNode();
        $this->loadNodeRelations();
        $this->loadNodeMap();
        $this->loadRule();
    }

    /**
     * 加载node数据表
     */
    protected function loadNode() {
        $sql = "SELECT id, name, weight FROM `tb_rule_node` WHERE `status` = 0";
        $query = $this->pdo->query($sql);
        $this->nodeArray = $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 加载node_relation数据表
     */
    protected function loadNodeRelations() {
        $sql = "SELECT id, node_id, parent_id FROM `tb_rule_node_relation` WHERE `status` = 0";
        $query = $this->pdo->query($sql);
        $this->nodeRelationArray = $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 加载tb_rule_node_map数据表
     */
    protected function loadNodeMap() {
        $sql = "SELECT id, r_n_id, r_id, params, `order` FROM `tb_rule_node_map` WHERE `status` = 0";
        $query = $this->pdo->query($sql);
        $this->nodeMapArray = $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 加载tb_rule数据表
     */
    protected function loadRule() {
        $sql = "SELECT id, name, `type`, extend_type, expression, result FROM `tb_rule` WHERE `status` = 0";
        $query = $this->pdo->query($sql);
        $this->ruleArray = $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 构建node id的名称映射关系map
     * @return array
     */
    public function buildNodeNameMap() {
        $result = [0 => ['name' => '风控根节点', 'id' => 0, 'weight' => 0]];
        foreach ($this->nodeArray as $v) {
            $id = intval($v['id']);
            $result[$id] = ["id" => $id, 'name' => $v['name'], 'weight' => $v['weight']];
        }

        return $result;
    }

    /**
     * 构建 节点=》父节点 映射关系map
     * @return array
     */
    public function buildNodeParentIndex() {
        $result = [];
        foreach ($this->nodeRelationArray as $data) {
            $parentId = intval($data['parent_id']);
            $nodeId = intval($data['node_id']);
            $result[$nodeId] = $parentId;
        }

        return $result;
    }

    /**
     * 构建node的父节点树
     * @param array $indexArray
     * @return array
     */
    protected function buildNodeTreeIndex(array $indexArray) {
        $result = [];

        foreach ($indexArray as $k => $v) {
            if ($k > 0) {
                $result[$k] = $this->findParent($indexArray, $k, []);
            }
        }

        return $result;
    }

    /**
     * 根据节点ID查找其所有父节点
     * @param array $dataArray
     * @param int $nodeId
     * @param array $path
     * @return array
     */
    protected function findParent(array $dataArray, $nodeId, array $path) {

        if (isset($dataArray[$nodeId])) {
            $parentId = $dataArray[$nodeId];
            if ($parentId != 0) {
                $path[] = $parentId;
                return $this->findParent($dataArray, $parentId, $path);
            }
        }

        $path[] = 0;
        return $path;
    }

    /**
     * 构建节点树
     * @return array
     */
    public function buildNodeTree() {
        $indexArray = $this->buildNodeParentIndex();
        $nodeIndexArray = $this->buildNodeTreeIndex($indexArray);
        $result = [];
        array_walk($nodeIndexArray,
            function ($item, $key) use (&$result){
                $data = &$result;
                foreach (array_reverse($item) as $v) {
                    if (!isset($data[$v])) {
                        $data[$v] = [$key];
                    }

                    $data = &$data[$v];
                }
            });

        $result = $this->formatNodeTree($result);

        $result = [0 => $result];
        unset($result[0][1]);
        return $result;
    }

    /**
     * 格式化节点树，去除空节点及构建过程中产生的垃圾节点数据
     *
     * @param array $dataArray
     * @return array|mixed
     */
    protected function formatNodeTree(array &$dataArray) {
        if (sizeof($dataArray) > 1 && isset($dataArray[0])) {
            unset($dataArray[0]);
        }

        foreach ($dataArray as $k => $v) {
            if (is_array($v)) {
                $dataArray[$k] = $this->formatNodeTree($v);
            }
        }

        if ((sizeof($dataArray) == 1) && (isset($dataArray[0]))) {
            $dataArray = $dataArray[0];
        }

        return $dataArray;
    }

    /**
     * @return array
     */
    public function getNodeArray(): array
    {
        return $this->nodeArray;
    }

    /**
     * @return array
     */
    public function getNodeRelationArray(): array
    {
        return $this->nodeRelationArray;
    }

    /**
     * @return array
     */
    public function getNodeMapArray(): array
    {
        return $this->nodeMapArray;
    }

    /**
     * @return array
     */
    public function getRuleArray(): array
    {
        return $this->ruleArray;
    }

    /**
     * 格式化为json数据
     *
     * @param mixed $data
     * @param array $nodeNameArray
     * @return string
     */
    public function output($data, array $nodeNameArray) {
        $result = "";
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $result .= "{";
                $result .= "\"name\": \"{$nodeNameArray[$k]['name']}\",";
                $result .= "\"children\": [";
                $result .= substr($this->output($v, $nodeNameArray), 0, -1);
                $result .= "]";
                $result .= "},";
            }
        } else {
            $result .= "{\"name\": \"{$nodeNameArray[$data]['name']}\"},";
        }

        return $result;
    }
}
