<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error Records</title>
    <!-- Include Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4">Error Records for {{ $date }}</h1>

        @if (isset($data['errors']) && count($data['errors']) > 0)
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        @foreach (array_keys($data['errors'][0]) as $key)
                        @if ($key !== 'id' && $key !== 'created_at')
                        <th>{{ $key }}</th>
                        @endif
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data['errors'] as $record)
                    <tr>
                        @foreach ($record as $key => $value)
                        @if ($key !== 'id' && $key !== 'created_at')
                        <td>{{ is_array($value) ? implode(', ', $value) : $value }}</td>
                        @endif
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-danger">No error records found.</p>
        @endif
    </div>

    <!-- Include Bootstrap JS (optional, for interactive components) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>