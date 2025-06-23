<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class YamlFileValidation implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$value instanceof UploadedFile) {
            $fail('The file must be a valid uploaded file.');
            return;
        }

        // Check file extension first (if it has one)
        $originalName = $value->getClientOriginalName();
        $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        
        // If file has an extension, it must be yml or yaml
        if (!empty($extension) && !in_array($extension, ['yml', 'yaml'])) {
            $fail('Please upload a YAML file. Only files with .yml or .yaml extensions are allowed.');
            return;
        }

        // Validate file content as YAML regardless of extension
        $content = file_get_contents($value->getRealPath());
        
        if (empty($content)) {
            $fail('Please upload a valid YAML file. The file cannot be empty.');
            return;
        }

        // Try to parse the content as YAML
        try {
            $parsed = yaml_parse($content);
            
            // yaml_parse returns false on failure
            if ($parsed === false) {
                $fail('Please upload a valid YAML file. The file content is not in proper YAML format.');
                return;
            }

            // Additional security check: ensure it's actually structured data
            if (!is_array($parsed) && !is_object($parsed)) {
                $fail('Please upload a valid YAML file. The file must contain structured configuration data.');
                return;
            }
            
        } catch (\Exception $e) {
            $fail('Please upload a valid YAML file. The file content contains formatting errors.');
            return;
        }

        // Additional MIME type check for files with extensions
        if (!empty($extension)) {
            $mimeType = $value->getMimeType();
            $allowedMimeTypes = [
                'text/plain',
                'text/x-yaml',
                'application/x-yaml',
                'application/yaml',
                'text/yaml',
                'application/octet-stream' // Some systems may report this for YAML files
            ];
            
            if (!in_array($mimeType, $allowedMimeTypes)) {
                $fail('Please upload a valid YAML file. The file type is not supported.');
                return;
            }
        }
    }
}
