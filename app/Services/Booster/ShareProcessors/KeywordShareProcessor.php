<?php

namespace App\Services\Booster\ShareProcessors;

use App\Services\Booster\KeywordMatrix;
use Illuminate\Support\Facades\Log;
class KeywordShareProcessor extends ShareProcessor
{
    private $rank_total;
    //best erank in matrix
    private $topERank;
    //this is the threshold that split lanes based on erank
    private $share_threshold = 80;
    //this is the threshold that split lanes based on erank
    private $bad_racer_share = 5;
    //racers with ERank above SHARE_THRESHOLD
    private $good_racers_lane;
    //racers with ERank below SHARE_THRESHOLD
    private $bad_racers_lane;


    /*
    this function should update the keyword matrix
    setting the new share
    */
    public function addScore(): void
    {
        foreach ($this->matrix as $k => $data) {
            $newscore = (float) ($data['erank']);
            $this->matrix[$k]['score'] = $newscore;
            $this->keywordMatrix->updateScore($data['keyword'], $newscore);
        }
    }
    public function addShare(): void
    {
        //find top ERank
        $this->setTopERank();
        //if topERank is zero just leave it
        if($this->topERank == 0)
        {
            return;
        }
        //divide best performers from lowest ones
        $this->setLanes();
        //find the share score total

        //bad racers get X%
        $reducePercentage = 0;
        foreach ($this->bad_racers_lane as $data) {

            if($data['erank'] == 0 && $data['impressions'] > 0)
            {
                Log::info("[KeywordShareProcessor] No coverage kwd detected: " . $data['keyword']);
                $this->keywordMatrix->updateShare($data['keyword'], 1);
                $reducePercentage += 1;
            }
            else
            {
                $this->keywordMatrix->updateShare($data['keyword'], $this->bad_racer_share);
                $reducePercentage += $this->bad_racer_share;
            }
        }
        //we split most of the traffic traffic between good racers
        $this->rank_total = $this->processScoreTotal();
        $shared = 0;
        $total_elements = count($this->good_racers_lane);
        $count = 1;
        foreach ($this->good_racers_lane as $data) {
            $share = (int) ($data['score'] / $this->rank_total * (100-$reducePercentage));
            $shared += $share;
            //on the last item we add what's missing to reach 100 (minus bad racer share), as a bonus
            if ($count == $total_elements) {
                $share = $share + (100 - $reducePercentage - $shared);
            }
            $this->keywordMatrix->updateShare($data['keyword'], $share);
            $count++;
        }
    }

    protected function processScoreTotal(): float
    {
        return array_reduce($this->good_racers_lane, function ($total, $k) {
            return $total += $k['score'];
        });
    }

    //last item has the highest E
    protected function setTopERank(): void
    {
        $this->topERank = array_reduce($this->matrix, function ($max, $k) {
            if ($k['score'] > $max) {
                return $k['score'];
            } else {
                return $max;
            }
        });
    }

    protected function setLanes(): void
    {
        $this->good_racers_lane = [];
        $this->bad_racers_lane = [];
        foreach ($this->matrix as $data) {
            if (($data['score'] / $this->topERank * 100) >= $this->share_threshold) {
                $this->good_racers_lane[] = $data;
            } else {
                $this->bad_racers_lane[] = $data;
            }
        }

    }
}
