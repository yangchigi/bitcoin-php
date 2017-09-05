<?php

namespace BitWasp\Bitcoin\Script\Path;

use BitWasp\Bitcoin\Script\Parser\Operation;
use BitWasp\Bitcoin\Script\ScriptFactory;
use BitWasp\Bitcoin\Script\ScriptInterface;

class ScriptBranch
{
    /**
     * @var ScriptInterface
     */
    private $fullScript;

    /**
     * @var array|\array[]
     */
    private $segments;

    /**
     * @var array|\bool[]
     */
    private $branch;

    /**
     * ScriptBranch constructor.
     * @param ScriptInterface $fullScript
     * @param array $logicalPath
     * @param array $segments
     */
    public function __construct(ScriptInterface $fullScript, array $logicalPath, array $segments)
    {
        $this->fullScript = $fullScript;
        $this->branch = $logicalPath;
        $this->segments = $segments;
    }

    /**
     * @return ScriptInterface
     */
    public function getFullScript()
    {
        return $this->fullScript;
    }

    /**
     * @return array|\bool[]
     */
    public function getPath()
    {
        return $this->branch;
    }

    /**
     * @return array|\array[]
     */
    public function getSegments()
    {
        return $this->segments;
    }

    /**
     * @return array
     */
    public function getOps()
    {
        $sequence = [];
        foreach ($this->segments as $segment) {
            $sequence = array_merge($sequence, $segment);
        }
        return $sequence;
    }

    /**
     * @return ScriptInterface
     */
    public function getNeuteredScript()
    {
        return ScriptFactory::fromOperations($this->getOps());
    }

    /**
     * @return ScriptInterface
     */
    public function getScript()
    {
        return ScriptFactory::fromOperations(array_filter($this->getOps(), function (Operation $operation) {
            return !$operation->isLogical();
        }));
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        $m = [];
        foreach ($this->segments as $segment) {
            $m[] = ScriptFactory::fromOperations($segment->all());
        }

        $path = [];
        foreach ($this->branch as $flag) {
            $path[] = $flag ? 'true' : 'false';
        }

        return [
            'branch' => implode(", ", $path),
            'segments' => $m,
        ];
    }

    /**
     * @return array[]
     */
    public function getSignSteps()
    {
        $steps = [];
        foreach ($this->segments as $segment) {
            /** @var Operation[] $segment */
            if (!(count($segment) === 1 && $segment[1]->isLogical())) {
                $steps[] = $segment;
            }
        }

        return $steps;
    }
}
