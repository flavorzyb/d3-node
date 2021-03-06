<?php

class RuleService
{
    /**
     * @var PDO
     */
    private $pdo = null;

    /**
     * @var array
     */
    private $ruleArray = [];

    /**
     * @var array
     */
    private $ruleExpression = [];

    /**
     * @var array
     */
    private $ruleExtendMapArray = [];

    /**
     * @var array
     */
    private $ruleExtendNameExpression = [];

    /**
     * @var array
     */
    private $ruleExtendChildren = [];

    /**
     * NodeService constructor.
     */
    public function __construct()
    {
        $this->pdo = createPdo();
    }

    /**
     * 加载tb_rule数据表
     */
    protected function loadRule() {
        $sql = "SELECT `id`, `name`, `type`, `url`, `params`, `extend_type`, `expression`, `result` FROM `tb_rule` WHERE `status` = 0";
        $query = $this->pdo->query($sql);
        $dataArray = $query->fetchAll(PDO::FETCH_ASSOC);
        $this->ruleArray = [];
        foreach ($dataArray as $v) {
            $id = intval($v['id']);
            $this->ruleArray[$id] = $v;
        }
    }

    protected function loadRuleExtendMap() {
        $sql = "SELECT `id`, `rule_id`, `expression`, `result`, `order` FROM `tb_rule_extend_mapping` WHERE `status` = 0 ORDER BY `rule_id`, `order`";
        $query = $this->pdo->query($sql);
        $dataArray = $query->fetchAll(PDO::FETCH_ASSOC);
        $this->ruleExtendMapArray = [];
        foreach ($dataArray as $v) {
            $id = intval($v['id']);
            $this->ruleExtendMapArray[$id] = $v;
        }
    }

    /**
     * 加载所有需要的数据
     */
    public function loadAllData() {
        $this->loadRule();
        $this->loadRuleExtendMap();
    }

    /**
     * @return array
     */
    public function getRuleArray(): array
    {
        return $this->ruleArray;
    }

    /**
     * @return array
     */
    public function getRuleExtendMapArray(): array
    {
        return $this->ruleExtendMapArray;
    }

    public function executeRuleExpression() {
        foreach ($this->ruleArray as $v) {
            $id = intval($v['id']);
            if (!isset($this->ruleExpression[$id])) {
                $this->parseRuleExpression($this->ruleArray[$id]);
            }
        }
    }

    private function parseRuleExpression(array $rule) {
        $id = intval($rule['id']);
        $expression = trim($rule['expression']);
        if ('' == $expression) {
            $result = trim($rule['result']);
            if (intval($result) == $result) {
                $this->ruleExpression[$id] = intval($result);
            } else {
                $this->ruleExpression[$id] = '';
            }
        } else {
            $str = $this->parseRuleExpressionStr($expression);
            $result = '';
            eval('$result = ' . $str. ';');
            $this->ruleExpression[$id] = $result;
        }
    }

    private function parseRuleExpressionStr($str) {
        //解析表达式
         return preg_replace_callback(
            "/@[0-9]+/",
            function ($matches) {
                $id = intval(str_replace('@', '', $matches[0]));
                if (!isset($this->ruleExpression[$id])) {
                    if (!isset($this->ruleArray[$id])) {
                        throw new Exception("can not found rule id({$id}).");
                    }

                    $this->parseRuleExpression($this->ruleArray[$id]);
                }

                return $this->ruleExpression[$id];
            },
            $str);
    }

    /**
     * @return array
     */
    public function getRuleExpression(): array
    {
        return $this->ruleExpression;
    }

    public function executeRuleExtendNameExpression() {
        foreach ($this->ruleExtendMapArray as $v) {
            $this->ruleExtendNameExpression[$v['rule_id']][] = $this->parseRuleExtendNameExpressionStr($v['expression']);
        }
    }

    private function parseRuleExtendNameExpressionStr($str) {
        //解析表达式
        return preg_replace_callback(
            "/@[0-9]+/",
            function ($matches) {
                $id = intval(str_replace('@', '', $matches[0]));
                $result = '';
                if (isset($this->ruleArray[$id])) {
                    $result = $this->ruleArray[$id]['name'];
                }

                return $result;
            },
            $str);
    }

    /**
     * @return array
     */
    public function getRuleExtendNameExpression(): array
    {
        return $this->ruleExtendNameExpression;
    }

    public function buildRuleExtendIndex() {
        return [];
    }

    public function findRuleChildren() {
        foreach ($this->ruleExtendMapArray as $value) {
            $children = $this->parseRuleExtendChildrenExpressionStr($value['expression']);
            foreach ($children as $v) {
                $this->ruleExtendChildren[$v] = $v;
            }
        }
    }

    private function parseRuleExtendChildrenExpressionStr($str) {
        //解析表达式
        $result = [];
        preg_replace_callback(
            "/@[0-9]+/",
            function ($matches) use (&$result){
                $id = intval(str_replace('@', '', $matches[0]));
                $result[] = $id;
                return $id;
            },
            $str);

        return $result;
    }

    /**
     * @return array
     */
    public function getRuleExtendChildren(): array
    {
        return $this->ruleExtendChildren;
    }

    /**
     * @return array
     */
    public function buildRuleExtendRoot(): array  {
        $result = [];
        foreach ($this->ruleExtendMapArray as $v) {
            $id = $v['rule_id'];
            if ((!isset($result[$id])) && (!isset($this->ruleExtendChildren[$id]))) {
                $name = $this->ruleArray[$id]['name'] ?? '';
                $result[$id] = ['id' => $id, 'name' => $name];
            }
        }

        return $result;
    }
}
