<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Диагностика базы данных</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .btn { padding: 10px 20px; margin: 10px; background: #007bff; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .result { margin: 20px 0; padding: 15px; border-radius: 5px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow: auto; }
    </style>
</head>
<body>
    <h1>Диагностика базы данных Sheykh Lavka</h1>
    
    <div>
        <button class="btn" onclick="checkDatabase()">Проверить базу данных</button>
        <button class="btn" onclick="createTables()">Создать таблицы</button>
        <button class="btn" onclick="seedData()">Создать тестовые данные</button>
    </div>
    
    <div id="result"></div>
    
    <script>
        function showResult(data, success = true) {
            const resultDiv = document.getElementById('result');
            const className = success ? 'success' : 'error';
            resultDiv.innerHTML = `
                <div class="result ${className}">
                    <h3>${success ? 'Успех' : 'Ошибка'}</h3>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                </div>
            `;
        }
        
        async function checkDatabase() {
            try {
                const response = await fetch('/debug/database');
                const data = await response.json();
                showResult(data, data.status === 'success');
            } catch (error) {
                showResult({error: error.message}, false);
            }
        }
        
        async function createTables() {
            try {
                const response = await fetch('/debug/create-tables', {method: 'POST'});
                const data = await response.json();
                showResult(data, data.status === 'success');
            } catch (error) {
                showResult({error: error.message}, false);
            }
        }
        
        async function seedData() {
            try {
                const response = await fetch('/debug/seed-data', {method: 'POST'});
                const data = await response.json();
                showResult(data, data.status === 'success');
            } catch (error) {
                showResult({error: error.message}, false);
            }
        }
        
        // Автоматически проверяем базу при загрузке
        window.onload = checkDatabase;
    </script>
</body>
</html>
