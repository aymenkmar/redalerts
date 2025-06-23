<!DOCTYPE html>
<html>
<head>
    <title>Upload File</title>
</head>
<body>
    <h1>Upload a File</h1>
    <form action="{{ url('/api/upload') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div>
            <input type="file" name="file" accept=".yml,.yaml" required>
            <p style="font-size: 12px; color: #666; margin-top: 5px;">
                Please upload a YAML file (.yml or .yaml extension only)
            </p>
        </div>
        <button type="submit">Upload</button>

        @if ($errors->any())
            <div style="color: red; margin-top: 10px;">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
    </form>
</body>
</html>
