<?php

namespace Mostafaznv\Larupload\Test;

use Illuminate\Database\Schema\Blueprint;
use Mostafaznv\Larupload\Enums\LaruploadMode;

class LaruploadBlueprintTest extends LaruploadTestCase
{
    public function testHeavyUploadHasAllColumns()
    {
        $columns = $this->getMacroColumns(LaruploadMode::HEAVY);

        $this->assertCount(10, $columns);

        $name = 'file_file_name';
        $this->assertArrayHasKey($name, $columns);
        $this->assertEquals('string', $columns[$name]['type']);
        $this->assertEquals(255, $columns[$name]['length']);
        $this->assertEquals(true, $columns[$name]['nullable']);

        $name = 'file_file_size';
        $this->assertArrayHasKey($name, $columns);
        $this->assertEquals('integer', $columns[$name]['type']);
        $this->assertEquals(true, $columns[$name]['unsigned']);
        $this->assertEquals(true, $columns[$name]['nullable']);

        $name = 'file_file_type';
        $this->assertArrayHasKey($name, $columns);
        $this->assertEquals('string', $columns[$name]['type']);
        $this->assertEquals(85, $columns[$name]['length']);
        $this->assertEquals(true, $columns[$name]['nullable']);

        $name = 'file_file_mime_type';
        $this->assertArrayHasKey($name, $columns);
        $this->assertEquals('string', $columns[$name]['type']);
        $this->assertEquals(85, $columns[$name]['length']);
        $this->assertEquals(true, $columns[$name]['nullable']);

        $name = 'file_file_width';
        $this->assertArrayHasKey($name, $columns);
        $this->assertEquals('integer', $columns[$name]['type']);
        $this->assertEquals(true, $columns[$name]['unsigned']);
        $this->assertEquals(true, $columns[$name]['nullable']);

        $name = 'file_file_height';
        $this->assertArrayHasKey($name, $columns);
        $this->assertEquals('integer', $columns[$name]['type']);
        $this->assertEquals(true, $columns[$name]['unsigned']);
        $this->assertEquals(true, $columns[$name]['nullable']);

        $name = 'file_file_duration';
        $this->assertArrayHasKey($name, $columns);
        $this->assertEquals('integer', $columns[$name]['type']);
        $this->assertEquals(true, $columns[$name]['unsigned']);
        $this->assertEquals(true, $columns[$name]['nullable']);

        $name = 'file_file_dominant_color';
        $this->assertArrayHasKey($name, $columns);
        $this->assertEquals('string', $columns[$name]['type']);
        $this->assertEquals(7, $columns[$name]['length']);
        $this->assertEquals(true, $columns[$name]['nullable']);

        $name = 'file_file_format';
        $this->assertArrayHasKey($name, $columns);
        $this->assertEquals('string', $columns[$name]['type']);
        $this->assertEquals(85, $columns[$name]['length']);
        $this->assertEquals(true, $columns[$name]['nullable']);

        $name = 'file_file_cover';
        $this->assertArrayHasKey($name, $columns);
        $this->assertEquals('string', $columns[$name]['type']);
        $this->assertEquals(85, $columns[$name]['length']);
        $this->assertEquals(true, $columns[$name]['nullable']);
    }

    public function testLightUploadHasAllColumns()
    {
        $columns = $this->getMacroColumns(LaruploadMode::LIGHT);

        $this->assertCount(2, $columns);

        $name = 'file_file_name';
        $this->assertArrayHasKey($name, $columns);
        $this->assertEquals('string', $columns[$name]['type']);
        $this->assertEquals(255, $columns[$name]['length']);
        $this->assertEquals(true, $columns[$name]['nullable']);

        $name = 'file_file_meta';
        $this->assertArrayHasKey($name, $columns);
        $this->assertFalse(array_search($columns[$name]['type'], ['json', 'text']) === false);
        $this->assertEquals(true, $columns[$name]['nullable']);
    }

    protected function getMacroColumns(LaruploadMode $mode): array
    {
        $table = new Blueprint('uploads');
        $table->upload('file', $mode);

        $columns = [];

        foreach ($table->getColumns() as $column) {
            $columns[$column->get('name')] = $column->getAttributes();
        }

        return $columns;
    }
}
