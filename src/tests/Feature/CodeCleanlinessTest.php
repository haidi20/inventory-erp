<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\File;

class CodeCleanlinessTest extends TestCase
{
    /** @test */
    public function no_go_code_remains_in_service_files()
    {
        $servicePath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'modules';
        $files = File::allFiles($servicePath);
        
        $filesWithGoCode = [];
        $patterns = [
            'func \(', 
            'err != nil', 
            'type \w+ struct', 
            'struct \{',
            'import \(',
            'chan \w+',
            'go func\('
        ];

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }
            $content = File::get($file->getRealPath());
            foreach ($patterns as $pattern) {
                if (preg_match("/$pattern/", $content)) {
                    $filesWithGoCode[] = $file->getRelativePathname() . " (Pattern: $pattern)";
                    break;
                }
            }
        }

        $this->assertEmpty($filesWithGoCode, "Go code remnants found in: " . implode(', ', $filesWithGoCode));
    }
}
