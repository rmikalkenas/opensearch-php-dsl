<?php declare(strict_types=1);

/*
 * This file is part of the ONGR package.
 *
 * (c) NFQ Technologies UAB <info@nfq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace OpenSearchDSL\Tests\Unit\Aggregation\Bucketing;

use OpenSearchDSL\Aggregation\Bucketing\DateRangeAggregation;

/**
 * @internal
 */
class DateRangeAggregationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test if exception is thrown when both range parameters are null.
     */
    public function testIfExceptionIsThrownWhenBothRangesAreNull(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Either from or to must be set. Both cannot be null.');
        $agg = new DateRangeAggregation('test_agg', 'test');
        $agg->addRange(null, null);
    }

    /**
     * Test getArray method.
     */
    public function testDateRangeAggregationGetArray(): void
    {
        $agg = new DateRangeAggregation('foo', 'baz');
        $agg->addRange('10', '20');
        static::assertSame(['field' => 'baz',  'ranges' => [['from' => '10', 'to' => '20']], 'keyed' => false], $agg->getArray());
        static::assertSame([
            'date_range' => [
                'field' => 'baz',
                'ranges' => [['from' => '10', 'to' => '20']],
                'keyed' => false,
            ],
        ], $agg->toArray());
        static::assertSame('foo', $agg->getName());
        $agg->setFormat('bar');
        $agg->setKeyed(true);
        $result = $agg->getArray();
        $expected = [
            'format' => 'bar',
            'field' => 'baz',
            'ranges' => [['from' => 10, 'to' => 20]],
            'keyed' => true,
        ];
        static::assertSame('bar', $agg->getFormat());
        static::assertEquals($expected, $result);
    }

    /**
     * Tests getType method.
     */
    public function testDateRangeAggregationGetType(): void
    {
        $aggregation = new DateRangeAggregation('foo', 'test', null, [], true);
        $aggregation->addRange('10', '20');
        $result = $aggregation->getType();
        static::assertEquals('date_range', $result);
        static::assertTrue($aggregation->getArray()['keyed']);
    }

    public function testDateRangeNeedsRanges(): void
    {
        static::expectException(\LogicException::class);

        $aggregation = new DateRangeAggregation('foo', 'test');
        $aggregation->toArray();
    }

    public function getDateRangeAggregationConstructorProvider(): array
    {
        return [
            // Case #0. Minimum arguments.
            [],
            // Case #1. Provide field.
            ['field' => 'fieldName'],
            // Case #2. Provide format.
            ['field' => 'fieldName', 'format' => 'formatString'],
            // Case #3. Provide empty ranges.
            ['field' => 'fieldName', 'format' => 'formatString', 'ranges' => []],
            // Case #4. Provide 1 range.
            [
                'field' => 'fieldName',
                'format' => 'formatString',
                'ranges' => [['from' => 'value']],
            ],
            // Case #4. Provide 2 ranges.
            [
                'field' => 'fieldName',
                'format' => 'formatString',
                'ranges' => [['from' => 'value'], ['to' => 'value']],
            ],
            // Case #5. Provide 3 ranges.
            [
                'field' => 'fieldName',
                'format' => 'formatString',
                'ranges' => [['from' => 'value'], ['to' => 'value'], ['from' => 'value', 'to' => 'value2']],
            ],
        ];
    }

    /**
     * Tests constructor method.
     *
     * @param string $field
     * @param string $format
     * @param array $ranges
     *
     * @dataProvider getDateRangeAggregationConstructorProvider
     */
    public function testDateRangeAggregationConstructor($field = null, $format = null, ?array $ranges = null): void
    {
        $aggregation = $this->getMockBuilder(\OpenSearchDSL\Aggregation\Bucketing\DateRangeAggregation::class)
            ->onlyMethods(['setField', 'setFormat', 'addRange'])
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $aggregation->expects(static::once())->method('setField')->with($field);
        $aggregation->expects(static::once())->method('setFormat')->with($format);
        $aggregation->expects(static::exactly(count($ranges ?? [])))->method('addRange');

        if ($field !== null) {
            if ($format !== null) {
                if ($ranges !== null) {
                    $aggregation->__construct('mock', $field, $format, $ranges);
                } else {
                    $aggregation->__construct('mock', $field, $format);
                }
            } else {
                $aggregation->__construct('mock', $field);
            }
        } else {
            $aggregation->__construct('mock', '');
        }
    }
}
