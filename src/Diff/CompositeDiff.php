<?php
namespace SemanticDiff\Diff;

use SemanticDiff\Diff;
use SemanticDiff\Status;
use PhpParser\Node\Stmt\Property;

class CompositeDiff implements Diff
{
    private $factory;
    private $base;
    private $head;
    private $defaultStatus = Status::NO_CHANGES;
    private $status;
    private $innerDiffs;
    
    public function __construct(Factory $factory, array $base, array $head)
    {
        $this->factory = $factory;
        $this->head = $head;
        $this->base = $base;
    }
    
    public function getStatus()
    {
        if (is_null($this->status)) {
            $innerDiffs = $this->getInnerDiffs();
            $status = $this->defaultStatus;

            foreach ($innerDiffs as $diff) {
                $status = max($status, $diff->getStatus());
            }

            $this->status = $status;
        }
        
        return $status;
    }
    
    public function getInnerDiffs()
    {
        if (is_null($this->innerDiffs)) {
            $base = $this->flattenNodes($this->base);
            $head = $this->flattenNodes($this->head);
            
            $diffs = $this->extractAndDiffNamedNodes($base, $head);

            if (count($base) !== count($head)) {
                $this->defaultStatus = Status::INTERNAL_CHANGES;
            }
            
            $this->innerDiffs = array_merge($diffs, $this->getDiffs($base, $head));
            unset($this->base);
            unset($this->head);
        }
        
        return $this->innerDiffs;
    }
    
    private function flattenNodes(array $nodes)
    {
        $flattened = [];
        
        foreach ($nodes as $node) {
            if (!is_object($node)) {
                $flattened[] = $node;
                continue;
            }
            
            switch ($node->getType()) {
                case 'Stmt_Const':
                case 'Stmt_ClassConst':
                    foreach ($node->consts as $const) {
                        $flattened[] = $const;
                    }
                    break;
                    
                case 'Stmt_Property':
                    foreach ($node->props as $prop) {
                        $_prop = new Property($node->type, []);
                        $_prop->name = $prop->name;
                        $_prop->default = $prop->default;
                        $flattened[] = $_prop;
                    }
                    break;
                    
                default:
                    $flattened[] = $node;
                    break;
            }
        }
        
        return $flattened;
    }
    
    private function getDiffs(array $baseNodes, array $headNodes)
    {
        $diffs = [];
        
        $maxCount = max(count($baseNodes), count($headNodes));
        for ($i = 0; $i < $maxCount; $i++) {
            $diffs[] = $this->factory->createDiff(
                isset($baseNodes[$i]) ? $baseNodes[$i] : null,
                isset($headNodes[$i]) ? $headNodes[$i] : null
            );
        }
        
        return $diffs;
    }
    
    private function extractAndDiffNamedNodes(array &$baseNodes, array &$headNodes)
    {
        $diffs = [];
        
        foreach ($this->extractNamedNodes($baseNodes, $headNodes) as $nodes) {
            foreach ($nodes as $baseAndHeadNodes) {
                $diffs[] = $this->factory->createDiff(
                    isset($baseAndHeadNodes[0]) ? $baseAndHeadNodes[0] : null,
                    isset($baseAndHeadNodes[1]) ? $baseAndHeadNodes[1] : null
                );
            }
        }
        
        return $diffs;
    }
    
    private function extractNamedNodes(array &$baseNodes, array &$headNodes)
    {
        $namedNodes = [];
        
        foreach ([&$baseNodes, &$headNodes] as $nodeSetKey => &$nodeSet) {
            foreach ($nodeSet as $nodeKey => $node) {
                if (!isset($node->name) || !is_string($node->name)) {
                    continue;
                }
                
                if ('Param' === $node->getType()) { // params are identified by their index, not by their name
                    $namedNodes[$node->getType()][$nodeKey][$nodeSetKey] = $node;
                } else {
                    $namedNodes[$node->getType()][(string)$node->name][$nodeSetKey] = $node;
                }
                
                unset($nodeSet[$nodeKey]);
            }
        }
        
        return $namedNodes;
    }
}
