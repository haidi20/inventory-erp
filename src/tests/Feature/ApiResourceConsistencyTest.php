<?php

namespace Tests\Feature;

use App\Http\Resources\ApiDataResource;
use Illuminate\Support\Facades\File;
use Tests\TestCase;
use ReflectionClass;

class ApiResourceConsistencyTest extends TestCase
{
    /** @test */
    public function all_api_resources_must_use_has_formatted_timestamps_concern()
    {
        $resourcePath = app_path('Http/Resources');
        $files = File::allFiles($resourcePath);
        
        $missingTrait = [];
        $targetTrait = 'App\Http\Resources\Concerns\HasFormattedTimestamps';

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php' || str_contains($file->getRelativePathname(), 'Concerns')) {
                continue;
            }

            $className = 'App\\Http\\Resources\\' . str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());
            
            if (class_exists($className)) {
                $reflection = new ReflectionClass($className);
                $traits = $reflection->getTraitNames();
                
                if (!in_array($targetTrait, $traits)) {
                    $missingTrait[] = $className;
                }
            }
        }

        $this->assertEmpty($missingTrait, "The following API Resources are missing HasFormattedTimestamps trait: " . implode(', ', $missingTrait));
    }

    /** @test */
    public function api_data_resource_automatically_adds_formatted_fields()
    {
        $data = [
            'id' => 1,
            'name' => 'Test Item',
            'created_at' => 1713261600, // 16 April 2024
            'sale_date' => '2024-04-16'
        ];

        $resource = new ApiDataResource($data);
        $request = new \Illuminate\Http\Request();
        $response = $resource->toArray($request);

        $this->assertArrayHasKey('created_at_formatted', $response);
        $this->assertArrayHasKey('created_at_formatted_long', $response);
        $this->assertEquals('16-04-2024 18:00:00', $response['created_at_formatted']);
        
        $this->assertArrayHasKey('sale_date_formatted', $response);
        $this->assertEquals('16-04-2024', $response['sale_date_formatted']);
    }
}
