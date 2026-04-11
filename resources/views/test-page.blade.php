<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-gray-800">Application Info</h1>
        <div class="space-y-4">
            <div class="flex justify-between border-b pb-2">
                <span class="text-gray-600 font-medium">App Name</span>
                <span class="font-mono text-indigo-600">{{ $appName }}</span>
            </div>
            <div class="flex justify-between border-b pb-2">
                <span class="text-gray-600 font-medium">App Environment</span>
                <span class="font-mono text-indigo-600 uppercase">{{ strtoupper($appEnv) }}</span>
            </div>
        </div>
        <div class="mt-6 text-center">
            <a href="/" class="text-indigo-500 hover:text-indigo-700 text-sm">← Back to Home</a>
        </div>
    </div>
</body>
</html>
