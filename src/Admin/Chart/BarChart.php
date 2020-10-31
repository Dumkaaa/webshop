<?php

namespace App\Admin\Chart;

class BarChart
{
    /**
     * @var array<string>
     */
    private array $labels;
    /**
     * @var array<array<int|float>>
     */
    private array $bars;

    /**
     * @param array<string>           $labels
     * @param array<array<int|float>> $bars
     */
    public function __construct(array $labels, array $bars)
    {
        $this->labels = $labels;
        $this->bars = $bars;
    }

    /**
     * @return array<string>
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * @return array<array<int|float>>
     */
    public function getBars(): array
    {
        return $this->bars;
    }
}
