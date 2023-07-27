<?php

namespace App\Services\Booster\ShareProcessors;

use stdClass;
use App\Services\Booster\KeywordMatrix;
use Exception;

use Log;

abstract class ShareProcessor
{
    protected $matrix;
    protected $keywordMatrix;

    abstract protected function addScore(): void;
    abstract protected function addShare(): void;

    public function setMatrix(KeywordMatrix $keywordMatrix)
    {

        $this->keywordMatrix = $keywordMatrix;
        $this->matrix = $keywordMatrix->getMatrix();
        //we check current status
        // if (!$this->checkShare()) {
        //     Log::error($this->matrix);
        //     throw new Exception("Invalid share distribution detected");
        // }
        //check if all erank are at zero
        $this->orderByERank();
    }

    public function resetMatrix()
    {
        $this->keywordMatrix = [];
        $this->matrix = [];
    }

    protected function orderByERank(): void
    {
        uasort($this->matrix, function ($a, $b) {
            return $a['erank'] <=> $b['erank'];
        });
    }

    protected function checkShare(): bool
    {
        $total_percentage = 0;
        foreach ($this->matrix as $k => $data) {
            if ($data["share"] < 0) {
                Log::debug("[Shareprocessor]: Share lower than zero:", [$data]);
                return false;
            }
            $total_percentage += $data["share"];
        }
        if ($total_percentage > 100 || $total_percentage < 0) {
            \Log::debug("[Shareprocessor]: Total percentage higher than 100:", [$this->matrix]);
            return false;
        }
        return true;
    }

    protected function updateScore(): void
    {
        foreach ($this->matrix as $k => $data) {
            $newscore = $data['erank'];
            $this->matrix[$k]['score'] = $newscore;
            $this->keywordMatrix->updateScore($data['keyword'], $newscore);
        }
    }

    public function evaluateThreshold(int $impression_threshold): bool
    {
        $totalElements = count($this->matrix);
        //should not happen but let's check anyway
        if ($totalElements < 1) {
            return false;
        }
        $totalShare = array_reduce($this->matrix, function($t, $e){
            return $t += $e['erank'];
        });
        //se sono tutti a zero
        if($totalShare == 0)
        {
            return false;
        }
        //check if all keywords which value is different than zero are better than the threshold
        foreach ($this->matrix as $element) {
            if ($element['erank'] > 0) {
                if ($element['impressions'] < $impression_threshold) {
                    return false;
                }
            }
        }
        return true;
    }
}
