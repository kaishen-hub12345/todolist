<?php
$db = new SQLite3('weather.db');
$db->exec('CREATE TABLE IF NOT EXISTS weather_history (id INTEGER PRIMARY KEY, city TEXT, date TEXT, temp_min REAL, temp_max REAL, forecast TEXT, humidity REAL, wind_speed REAL, timestamp TEXT)');

$malaysia_regions = [
    'Kuala Lumpur', 'Penang', 'Selangor', 'Perak', 'Pahang', 'Kedah', 'Kelantan',
    'Terengganu', 'Perlis', 'Negeri Sembilan', 'Malacca', 'Johor', 'Sabah',
    'Sarawak', 'Labuan', 'Putrajaya'
];
$selected_city = isset($_GET['city']) && in_array($_GET['city'], $malaysia_regions) ? $_GET['city'] : 'Kuala Lumpur';
$data = null;

// 尝试从 API 获取数据
$api_url = 'https://api.data.gov.my/weather/forecast?contains=' . urlencode($selected_city) . '@location__location_name&limit=1';
$response = @file_get_contents($api_url);
if ($response) {
    $api_data = json_decode($response, true);
    if (is_array($api_data) && !empty($api_data)) {
        $forecast = $api_data[0];
        $city = $forecast['location']['location_name'];
        $date = $forecast['date'];
        $temp_min = $forecast['min_temp'];
        $temp_max = $forecast['max_temp'];
        $forecast_text = $forecast['summary_forecast'] . ' (' . $forecast['summary_when'] . ')';
        $humidity = rand(60, 90); // 模拟湿度
        $wind_speed = rand(5, 15); // 模拟风速 (km/h)
        $data = ['city' => $city, 'date' => $date, 'temp_min' => $temp_min, 'temp_max' => $temp_max, 'forecast' => $forecast_text, 'humidity' => $humidity, 'wind_speed' => $wind_speed];

        // 保存到数据库
        $city = SQLite3::escapeString($city);
        $date = SQLite3::escapeString($date);
        $temp_min = (float)$temp_min;
        $temp_max = (float)$temp_max;
        $forecast_text = SQLite3::escapeString($forecast_text);
        $humidity = (float)$humidity;
        $wind_speed = (float)$wind_speed;
        $db->exec("INSERT INTO weather_history (city, date, temp_min, temp_max, forecast, humidity, wind_speed, timestamp) VALUES ('$city', '$date', $temp_min, $temp_max, '$forecast_text', $humidity, $wind_speed, datetime('now'))");
    }
}

// API 失败时使用模拟数据
if (!$data) {
    $simulated_data = [
        'Kuala Lumpur' => ['date' => date('Y-m-d'), 'temp_min' => 25, 'temp_max' => 34, 'forecast' => 'Partly cloudy (Afternoon)', 'humidity' => 75, 'wind_speed' => 10],
        'Penang' => ['date' => date('Y-m-d'), 'temp_min' => 26, 'temp_max' => 32, 'forecast' => 'Sunny (Morning)', 'humidity' => 80, 'wind_speed' => 8],
        'Selangor' => ['date' => date('Y-m-d'), 'temp_min' => 24, 'temp_max' => 33, 'forecast' => 'Rainy (Night)', 'humidity' => 85, 'wind_speed' => 12],
        'Perak' => ['date' => date('Y-m-d'), 'temp_min' => 25, 'temp_max' => 32, 'forecast' => 'Mostly cloudy (Afternoon)', 'humidity' => 70, 'wind_speed' => 9],
        'Pahang' => ['date' => date('Y-m-d'), 'temp_min' => 24, 'temp_max' => 34, 'forecast' => 'Thunderstorms (Evening)', 'humidity' => 90, 'wind_speed' => 15],
        'Kedah' => ['date' => date('Y-m-d'), 'temp_min' => 26, 'temp_max' => 33, 'forecast' => 'Sunny (Morning)', 'humidity' => 78, 'wind_speed' => 7],
        'Kelantan' => ['date' => date('Y-m-d'), 'temp_min' => 25, 'temp_max' => 32, 'forecast' => 'Rainy (Afternoon)', 'humidity' => 82, 'wind_speed' => 11],
        'Terengganu' => ['date' => date('Y-m-d'), 'temp_min' => 24, 'temp_max' => 33, 'forecast' => 'Cloudy (Night)', 'humidity' => 88, 'wind_speed' => 13],
        'Perlis' => ['date' => date('Y-m-d'), 'temp_min' => 26, 'temp_max' => 31, 'forecast' => 'Sunny (Morning)', 'humidity' => 76, 'wind_speed' => 6],
        'Negeri Sembilan' => ['date' => date('Y-m-d'), 'temp_min' => 25, 'temp_max' => 33, 'forecast' => 'Partly cloudy (Afternoon)', 'humidity' => 80, 'wind_speed' => 10],
        'Malacca' => ['date' => date('Y-m-d'), 'temp_min' => 26, 'temp_max' => 32, 'forecast' => 'Rainy (Evening)', 'humidity' => 83, 'wind_speed' => 9],
        'Johor' => ['date' => date('Y-m-d'), 'temp_min' => 27, 'temp_max' => 34, 'forecast' => 'Sunny (Morning)', 'humidity' => 77, 'wind_speed' => 12],
        'Sabah' => ['date' => date('Y-m-d'), 'temp_min' => 24, 'temp_max' => 33, 'forecast' => 'Cloudy (Afternoon)', 'humidity' => 85, 'wind_speed' => 14],
        'Sarawak' => ['date' => date('Y-m-d'), 'temp_min' => 25, 'temp_max' => 32, 'forecast' => 'Rainy (Night)', 'humidity' => 87, 'wind_speed' => 10],
        'Labuan' => ['date' => date('Y-m-d'), 'temp_min' => 26, 'temp_max' => 31, 'forecast' => 'Sunny (Morning)', 'humidity' => 79, 'wind_speed' => 8],
        'Putrajaya' => ['date' => date('Y-m-d'), 'temp_min' => 25, 'temp_max' => 34, 'forecast' => 'Partly cloudy (Afternoon)', 'humidity' => 74, 'wind_speed' => 11]
    ];
    $data = $simulated_data[$selected_city];
}

$results = $db->query('SELECT * FROM weather_history ORDER BY timestamp DESC LIMIT 5');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Weather Viewer (Malaysia)</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            background-color: #f0f4f8;
            color: #333;
        }
        .weather-container {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        select {
            padding: 10px;
            width: 70%;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            background-color: #fff;
        }
        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #0056b3;
        }
        .weather-card {
            background: linear-gradient(135deg, #e6f0fa, #ffffff);
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .weather-card h2 {
            color: #004085;
            margin: 0 0 10px;
            font-size: 24px;
        }
        .weather-card p {
            margin: 5px 0;
            font-size: 16px;
            color: #555;
        }
        .weather-card .highlight {
            color: #007bff;
            font-weight: bold;
        }
        .history-list {
            list-style: none;
            padding: 0;
        }
        .history-list li {
            background: #fff;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="weather-container">
        <h1 style="color: #004085; text-align: center;">Malaysia Weather Viewer</h1>
        <form method="GET" style="text-align: center; margin-bottom: 20px;">
            <select name="city" required onchange="this.form.submit()">
                <?php foreach ($malaysia_regions as $city_option): ?>
                    <option value="<?php echo htmlspecialchars($city_option); ?>" <?php echo $selected_city === $city_option ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($city_option); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <div class="weather-card">
            <?php if ($data): ?>
                <h2>Weather in <?php echo htmlspecialchars($data['city'] ?? $selected_city); ?> (<?php echo htmlspecialchars($data['date']); ?>)</h2>
                <p>Min Temperature: <span class="highlight"><?php echo $data['temp_min']; ?>°C</span></p>
                <p>Max Temperature: <span class="highlight"><?php echo $data['temp_max']; ?>°C</span></p>
                <p>Forecast: <span class="highlight"><?php echo htmlspecialchars($data['forecast']); ?></span></p>
                <p>Humidity: <span class="highlight"><?php echo $data['humidity']; ?>%</span></p>
                <p>Wind Speed: <span class="highlight"><?php echo $data['wind_speed']; ?> km/h</span></p>
            <?php else: ?>
                <p class="error">Failed to load weather data. Using simulated data.</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="weather-container">
        <h2 style="color: #004085;">Recent Weather History</h2>
        <ul class="history-list">
            <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)): ?>
                <li>
                    <?php echo htmlspecialchars($row['city']); ?> (<?php echo $row['date']; ?>): 
                    Min <span class="highlight"><?php echo $row['temp_min']; ?>°C</span>, 
                    Max <span class="highlight"><?php echo $row['temp_max']; ?>°C</span>, 
                    <?php echo htmlspecialchars($row['forecast']); ?>, 
                    Humidity <span class="highlight"><?php echo $row['humidity']; ?>%</span>, 
                    Wind <span class="highlight"><?php echo $row['wind_speed']; ?> km/h</span> 
                    (<?php echo $row['timestamp']; ?>)
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
    <script>
        document.querySelector('form').addEventListener('submit', e => e.preventDefault()); // Prevent form refresh
    </script>
</body>
</html>
<?php $db->close(); ?>