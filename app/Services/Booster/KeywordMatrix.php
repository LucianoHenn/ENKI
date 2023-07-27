<?php

namespace App\Services\Booster;
use Exception;

use App\Services\Booster\ShareProcessors\KeywordShareProcessor;


class KeywordMatrix {
    
    private $matrix = [];

    public function addKeyword(array $keyword): void
    {
        $this->validateKeyword($keyword);
        $this->matrix[$keyword["keyword"]] = $keyword;
    }

    public function getMatrix(): array
    {
        return $this->matrix;
    }
    public function getScore($keyword): float
    {
        foreach ($this->matrix as $k) {
            if ($k["keyword"] == strtolower($keyword)) {
                return $k["score"];
            }
        }
        throw new Exception("[KeywordMatrix] Score not found for $keyword");
    }
    public function getShare($keyword): int
    {
        foreach ($this->matrix as $k) {
            if ($k["keyword"] == strtolower($keyword)) {
                return $k["share"];
            }
        }
        throw new Exception("[KeywordMatrix] Share not found for $keyword");
    }

    public function resetMatrix(): void
    {
        $this->matrix = [];
    }

    public function processShare(): void
    {
        $p = new KeywordShareProcessor();
        $p->addShare($this);
    }

    public function updateShare($keyword, $share)
    {
        if (array_key_exists($keyword, $this->matrix)) {
            $this->matrix[$keyword]['share'] = $share;
        } else {
            throw new Exception("Impossible to set share, index not found for: $keyword");
        }
    }

    public function updateScore($keyword, $score)
    {
        if (array_key_exists($keyword, $this->matrix)) {
            $this->matrix[$keyword]['score'] = $score;
        } else {
            throw new Exception("Impossible to set score, index not found for: $keyword");
        }
    }

    private function validateKeyword($keyword): void
    {
        
        if (!array_key_exists("keyword", $keyword)) {
            throw new Exception("keyword not defined");
        } elseif (!is_string($keyword["keyword"])) {
            throw new Exception("keyword is not valid");
        }
        if (!array_key_exists("erank", $keyword)) {
            throw new Exception("erank not defined");
        } elseif (!is_float($keyword["erank"])) {
            throw new Exception("erank is not valid");
        } elseif ($keyword["erank"] < 0) {
            throw new Exception("erank is less than zero");
        }
        if (!array_key_exists("gross_revenue", $keyword)) {
            throw new Exception("gross_revenue not defined");
        }
        if (!array_key_exists("clicks", $keyword)) {
            throw new Exception("clicks not defined");
        } elseif (!is_int($keyword["clicks"])) {
            throw new Exception("clicks is not valid");
        }
        if (!array_key_exists("impressions", $keyword)) {
            throw new Exception("impressions not defined");
        } elseif (!is_int($keyword["impressions"])) {
            throw new Exception("impressions is not valid");
        }
        if (!array_key_exists("score", $keyword)) {
            throw new Exception("score not defined");
        } elseif (!is_float($keyword["score"])) {
            throw new Exception("score is not valid " . json_encode($keyword));
        }
        if (!array_key_exists("share", $keyword)) {
            throw new Exception("share not defined");
        } elseif (!is_int($keyword["share"])) {
            throw new Exception("share is not valid");
        }
    }

}