<?php

namespace App\Tests\Unit\Admin\Chart;

use App\Admin\Chart\BarChart;
use PHPUnit\Framework\TestCase;

/**
 * @covers \App\Admin\Chart\BarChart
 */
class BarChartTest extends TestCase
{
    public function testConstruct(): void
    {
        $barChart = new BarChart([
            'label1', 'label2',
        ], [
            [0, 1, 2],
            [3, 4, 5],
        ]);

        $this->assertSame([
            'label1', 'label2',
        ], $barChart->getLabels());

        $this->assertSame([
            [0, 1, 2],
            [3, 4, 5],
        ], $barChart->getBars());
    }
}
