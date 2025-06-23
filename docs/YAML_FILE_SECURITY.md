# YAML File Upload Security Implementation

## Overview

This document describes the security measures implemented for file uploads in the redalertsv2 project to prevent SQL injection and other security vulnerabilities by ensuring only valid YAML files are accepted.

## Security Features

### 1. Custom YAML Validation Rule

**File**: `app/Rules/YamlFileValidation.php`

This custom validation rule provides comprehensive YAML file validation:

- **Extension Validation**: Accepts files with `.yml`, `.yaml` extensions, or no extension
- **Content Validation**: Parses file content using PHP's native `yaml_parse()` function
- **Structure Validation**: Ensures the YAML contains structured data (arrays or objects)
- **MIME Type Validation**: Validates MIME types for files with extensions
- **Empty File Protection**: Rejects empty files

### 2. Controller Security

The following controllers have been updated with YAML validation:

#### FileUploadController
- `upload()` method - General file upload endpoint
- `uploadKubeconfig()` method - Kubernetes config upload

#### KubernetesController  
- `replaceKubeconfig()` method - Replace existing kubeconfig

#### Livewire ClusterUpload Component
- Custom `validateData()` method with YAML validation

### 3. Frontend Validation

#### React Component (`frontend/src/components/ClusterUploadForm.tsx`)
- File input restricted to `.yaml,.yml` extensions
- Client-side validation for file extensions
- Allows files without extensions (validated server-side)

#### Blade Template (`resources/views/upload.blade.php`)
- HTML file input restricted to `.yml,.yaml` extensions

### 4. Configuration Updates

#### Livewire Configuration (`config/livewire.php`)
- Added `yml` and `yaml` to preview MIME types

## Implementation Details

### Validation Logic

```php
// Extension check (if file has extension)
if (!empty($extension) && !in_array($extension, ['yml', 'yaml'])) {
    $fail('The file must be a YAML file with .yml or .yaml extension.');
}

// Content validation using PHP YAML parser
$parsed = yaml_parse($content);
if ($parsed === false) {
    $fail('The file content is not valid YAML format.');
}

// Structure validation
if (!is_array($parsed) && !is_object($parsed)) {
    $fail('The YAML file must contain structured data.');
}
```

### Allowed MIME Types

- `text/plain`
- `text/x-yaml`
- `application/x-yaml`
- `application/yaml`
- `text/yaml`
- `application/octet-stream`

## Security Benefits

1. **SQL Injection Prevention**: Only YAML files are processed, preventing malicious file uploads
2. **Content Validation**: Files must contain valid YAML structure
3. **Extension Flexibility**: Supports kubeconfig files without extensions
4. **Multi-layer Validation**: Client-side and server-side validation
5. **Native PHP YAML**: Uses built-in PHP YAML functions (no additional dependencies)

## Testing

Comprehensive unit tests are included in `tests/Unit/YamlFileValidationTest.php`:

- Valid YAML files with `.yml` extension ✓
- Valid YAML files with `.yaml` extension ✓  
- Valid YAML files without extension ✓
- Invalid file extensions ✗
- Empty files ✗
- Invalid YAML content ✗
- Non-structured content ✗

## Usage Examples

### Valid YAML Files

```yaml
# kubeconfig (no extension)
apiVersion: v1
kind: Config
clusters:
- cluster:
    server: https://kubernetes.example.com
  name: example-cluster
```

```yaml
# deployment.yml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: my-app
spec:
  replicas: 3
```

### Invalid Files

- `.txt`, `.json`, `.xml` files (wrong extension)
- Empty files
- Files with invalid YAML syntax
- Files containing only plain text strings

## Error Messages

- `"Please upload a YAML file. Only files with .yml or .yaml extensions are allowed."`
- `"Please upload a valid YAML file. The file cannot be empty."`
- `"Please upload a valid YAML file. The file content is not in proper YAML format."`
- `"Please upload a valid YAML file. The file must contain structured configuration data."`
- `"Please upload a valid YAML file. The file content contains formatting errors."`
- `"Please upload a valid YAML file. The file type is not supported."` (MIME type validation)

## Dependencies

- PHP YAML extension (already installed)
- Laravel validation system
- Native PHP file handling functions
