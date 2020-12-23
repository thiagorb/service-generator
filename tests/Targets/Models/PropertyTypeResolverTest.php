<?php

namespace Tests\Targets\Models;

use Tests\TestCase;
use Thiagorb\ServiceGenerator\Targets\Models\PropertyTypeResolver;

class PropertyTypeResolverTest extends TestCase
{
    /**
     * @var PropertyTypeResolver
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new PropertyTypeResolver();
    }

    public function testIsNullable()
    {
        $this->assertFalse($this->subject->isNullable(['type' => 'string']));
        $this->assertFalse($this->subject->isNullable(['type' => 'string[]']));
        $this->assertFalse($this->subject->isNullable(['type' => '?string[]']));
        $this->assertFalse($this->subject->isNullable(['type' => '\\Fake\\Class']));
        $this->assertFalse($this->subject->isNullable(['type' => '\\Fake\\Class[]']));
        $this->assertFalse($this->subject->isNullable(['type' => '?\\Fake\\Class[]']));
        $this->assertTrue($this->subject->isNullable(['type' => 'string[]|null']));
        $this->assertTrue($this->subject->isNullable(['type' => '?string[]|null']));
        $this->assertTrue($this->subject->isNullable(['type' => '\\Fake\\Class[]|null']));
        $this->assertTrue($this->subject->isNullable(['type' => '?\\Fake\\Class[]|null']));
    }

    public function testResolveTypeHint()
    {
        $this->assertEquals('string', $this->subject->resolveTypeHint(['type' => 'string']));
        $this->assertEquals('array', $this->subject->resolveTypeHint(['type' => 'string[]']));
        $this->assertEquals('array', $this->subject->resolveTypeHint(['type' => '?string[]']));
        $this->assertEquals('\\Fake\\Class', $this->subject->resolveTypeHint(['type' => '\\Fake\\Class']));
        $this->assertEquals('array', $this->subject->resolveTypeHint(['type' => '\\Fake\\Class[]']));
        $this->assertEquals('array', $this->subject->resolveTypeHint(['type' => '?\\Fake\\Class[]']));
        $this->assertEquals('array', $this->subject->resolveTypeHint(['type' => 'string[]|null']));
        $this->assertEquals('array', $this->subject->resolveTypeHint(['type' => '?string[]|null']));
        $this->assertEquals('array', $this->subject->resolveTypeHint(['type' => '\\Fake\\Class[]|null']));
        $this->assertEquals('array', $this->subject->resolveTypeHint(['type' => '?\\Fake\\Class[]|null']));
    }
}
