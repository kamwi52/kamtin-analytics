<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KamTin Analytics Engine</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen py-12">

    <main class="w-full max-w-4xl bg-white p-8 rounded-xl shadow-lg">
        
        <header class="border-b pb-4 mb-6">
            <h1 class="text-3xl font-bold text-gray-800 text-center">KamTin Analytics Engine</h1>
        </header>

        @if(session('analysis_results'))
            <section id="results-section">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-gray-800">Analysis Summary: Grade {{ session('grade_level') }}</h2>
                    <div class="flex space-x-2">
                        <a href="{{ route('analysis.export') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg">Export to Excel</a>
                        <a href="{{ route('analysis.clear') }}" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg">Start New Analysis</a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Boys</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Girls</th>
                                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                            </tr>
                        </thead>
                        <!-- Inside resources/views/analysis.blade.php -->
<tbody class="bg-white divide-y divide-gray-200">
    @foreach (session('analysis_results') as $key => $value)
    <tr>
        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ ucwords(strtolower(str_replace('_', ' ', $key))) }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-center font-mono">{{ $value['B'] }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-center font-mono">{{ $value['G'] }}</td>
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-center font-mono font-bold">{{ $value['TOTAL'] }}</td>
    </tr>
    @endforeach
</tbody>
                    </table>
                </div>
            </section>
        @else
            <section id="upload-section">
                <form action="{{ route('analysis.run') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf
                    <div>
                        <label for="results_csv" class="block mb-2 text-lg font-semibold text-gray-700">Step 1: Upload Results CSV</label>
                        <input type="file" id="results_csv" name="results_csv" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
                    </div>
                    <div>
                        <label for="grade_level" class="block mb-2 text-lg font-semibold text-gray-700">Step 2: Select Grade Level</label>
                        <select id="grade_level" name="grade_level" class="w-full p-3 bg-gray-50 border border-gray-300 rounded-lg">
                            <option value="9">Grade 9</option>
                            <option value="12">Grade 12</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg text-lg">Analyze Results</button>
                </form>
            </section>
        @endif
    </main>
</body>
</html>