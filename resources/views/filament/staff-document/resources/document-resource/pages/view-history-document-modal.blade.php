<div class="grid grid-cols-2 gap-4">
    <div>
        <h3 class="text-lg font-bold mb-2">Old Data</h3>
        <pre class="bg-gray-100 p-2 rounded text-sm overflow-x-auto">
{{ json_encode($oldData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
        </pre>
    </div>
    <div>
        <h3 class="text-lg font-bold mb-2">New Data</h3>
        <pre class="bg-gray-100 p-2 rounded text-sm overflow-x-auto">
{{ json_encode($newData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
        </pre>
    </div>
</div>
