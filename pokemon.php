<?php
// Initialize SQLite database
$db = new SQLite3('pokemon.db');
$db->exec('CREATE TABLE IF NOT EXISTS pokemon_list (id INTEGER PRIMARY KEY, name TEXT UNIQUE)');
$db->exec('CREATE TABLE IF NOT EXISTS pokemon_history (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT, height INTEGER, weight INTEGER, type TEXT, sprite_url TEXT, timestamp TEXT)');
$db->exec('CREATE INDEX IF NOT EXISTS idx_timestamp ON pokemon_history (timestamp)');

// Function to fetch or update Pokémon list from API or database
function getPokemonList($db) {
    $pokemon_options = [];
    $result = $db->query('SELECT id, name FROM pokemon_list ORDER BY id');
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $pokemon_options[$row['id']] = $row['name'];
    }

    // If database is empty, fetch from API
    if (empty($pokemon_options)) {
        $api_url = 'https://pokeapi.co/api/v2/pokemon?limit=1000';
        $response = @file_get_contents($api_url);
        if ($response) {
            $api_data = json_decode($response, true);
            if (isset($api_data['results'])) {
                $stmt = $db->prepare('INSERT OR IGNORE INTO pokemon_list (id, name) VALUES (:id, :name)');
                foreach ($api_data['results'] as $index => $pokemon) {
                    $id = $index + 1;
                    $stmt->bindValue(':id', $id, SQLITE3_INTEGER);
                    $stmt->bindValue(':name', $pokemon['name'], SQLITE3_TEXT);
                    $stmt->execute();
                    $pokemon_options[$id] = $pokemon['name'];
                }
            }
        }
    }
    return $pokemon_options;
}

// Get Pokémon list
$pokemon_options = getPokemonList($db);

// Handle selected Pokémon (for URL-based selection)
$selected_pokemon_id = isset($_GET['id']) && array_key_exists((int)$_GET['id'], $pokemon_options) ? (int)$_GET['id'] : null;
$data = null;

// Fetch data for selected Pokémon if specified
if ($selected_pokemon_id) {
    $selected_pokemon_name = $pokemon_options[$selected_pokemon_id];
    $result = $db->querySingle(
        'SELECT name, height, weight, type, sprite_url FROM pokemon_history WHERE name = "' . SQLite3::escapeString($selected_pokemon_name) . '" ORDER BY timestamp DESC LIMIT 1',
        true
    );
    if ($result) {
        $data = $result;
    } else {
        $api_url = "https://pokeapi.co/api/v2/pokemon/$selected_pokemon_id";
        $response = @file_get_contents($api_url);
        if ($response) {
            $api_data = json_decode($response, true);
            if (is_array($api_data) && isset($api_data['name'])) {
                $data = [
                    'name' => $api_data['name'],
                    'height' => $api_data['height'],
                    'weight' => $api_data['weight'],
                    'type' => $api_data['types'][0]['type']['name'],
                    'sprite_url' => $api_data['sprites']['front_default']
                ];
                $stmt = $db->prepare('INSERT INTO pokemon_history (name, height, weight, type, sprite_url, timestamp) VALUES (:name, :height, :weight, :type, :sprite_url, datetime("now"))');
                $stmt->bindValue(':name', $data['name'], SQLITE3_TEXT);
                $stmt->bindValue(':height', $data['height'], SQLITE3_INTEGER);
                $stmt->bindValue(':weight', $data['weight'], SQLITE3_INTEGER);
                $stmt->bindValue(':type', $data['type'], SQLITE3_TEXT);
                $stmt->bindValue(':sprite_url', $data['sprite_url'], SQLITE3_TEXT);
                $stmt->execute();
            }
        }
    }
}

// Fallback to simulated data if API fails for selected Pokémon
if ($selected_pokemon_id && !$data) {
    $simulated_data = [
        1 => ['name' => 'bulbasaur', 'height' => 7, 'weight' => 69, 'type' => 'grass', 'sprite_url' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/1.png'],
        2 => ['name' => 'ivysaur', 'height' => 10, 'weight' => 130, 'type' => 'grass', 'sprite_url' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/2.png'],
        25 => ['name' => 'pikachu', 'height' => 4, 'weight' => 60, 'type' => 'electric', 'sprite_url' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/25.png'],
        132 => ['name' => 'ditto', 'height' => 3, 'weight' => 40, 'type' => 'normal', 'sprite_url' => 'https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/132.png']
    ];
    $data = $simulated_data[$selected_pokemon_id] ?? ['name' => 'unknown', 'height' => 0, 'weight' => 0, 'type' => 'unknown', 'sprite_url' => ''];
}

// Fetch recent history
$results = $db->query('SELECT * FROM pokemon_history ORDER BY timestamp DESC LIMIT 5');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pokémon Info</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            max-width: 1200px;
            margin: 20px auto;
            background-color: #f0f4f8;
            color: #333;
        }
        .pokemon-container {
            background: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .search-bar {
            text-align: center;
            margin-bottom: 20px;
        }
        .search-bar input {
            padding: 10px;
            width: 70%;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }
        .pokemon-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 20px;
            padding: 0 10px;
        }
        .pokemon-card {
            background: linear-gradient(135deg, #e6f0fa, #ffffff);
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .pokemon-card:hover {
            transform: scale(1.05);
        }
        .pokemon-card img {
            max-width: 100px;
            height: auto;
            margin-bottom: 10px;
        }
        .pokemon-card h3 {
            color: #004085;
            margin: 0 0 5px;
            font-size: 18px;
        }
        .pokemon-card p {
            margin: 0;
            font-size: 14px;
            color: #555;
        }
        .detailed-card {
            background: linear-gradient(135deg, #e6f0fa, #ffffff);
            border-radius: 10px;
            padding: 15px;
            margin-top: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
            display: <?php echo $data ? 'block' : 'none'; ?>;
        }
        .detailed-card img {
            max-width: 200px;
            height: auto;
            margin-bottom: 15px;
        }
        .detailed-card h2 {
            color: #004085;
            margin: 0 0 10px;
            font-size: 24px;
        }
        .detailed-card p {
            margin: 5px 0;
            font-size: 16px;
            color: #555;
        }
        .detailed-card .highlight {
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
        .history-list li img {
            max-width: 100px;
            height: auto;
            vertical-align: middle;
            margin-right: 10px;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="pokemon-container">
        <h1 style="color: #004085; text-align: center;">Pokémon Info</h1>
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Search Pokémon..." onkeyup="filterPokemon()">
        </div>
        <div class="pokemon-grid" id="pokemonGrid">
            <?php
            foreach ($pokemon_options as $id => $name) {
                $sprite_url = "https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/$id.png";
                echo "<div class='pokemon-card' onclick=\"window.location.href='?id=$id'\">";
                echo "<img src='$sprite_url' alt='$name'>";
                echo "<h3>" . ucfirst(htmlspecialchars($name)) . " (#$id)</h3>";
                echo "</div>";
            }
            ?>
        </div>
        <div class="detailed-card">
            <?php if ($data && $data['name'] !== 'unknown'): ?>
                <img src="<?php echo htmlspecialchars($data['sprite_url']); ?>" alt="<?php echo ucfirst(htmlspecialchars($data['name'])); ?>">
                <h2><?php echo ucfirst(htmlspecialchars($data['name'])); ?> (#<?php echo $selected_pokemon_id; ?>)</h2>
                <p>Height: <span class="highlight"><?php echo $data['height']; ?> dm</span></p>
                <p>Weight: <span class="highlight"><?php echo $data['weight']; ?> hg</span></p>
                <p>Type: <span class="highlight"><?php echo htmlspecialchars($data['type']); ?></span></p>
            <?php else: ?>
                <p class="error">Failed to load Pokémon data. Using simulated data.</p>
            <?php endif; ?>
        </div>
    </div>
    <div class="pokemon-container">
        <h2 style="color: #004085;">Recent Pokémon History</h2>
        <ul class="history-list">
            <?php while ($row = $results->fetchArray(SQLITE3_ASSOC)): ?>
                <li>
                    <img src="<?php echo htmlspecialchars($row['sprite_url']); ?>" alt="<?php echo ucfirst(htmlspecialchars($row['name'])); ?>">
                    <?php echo ucfirst(htmlspecialchars($row['name'])); ?> (#<?php
                    $id = array_search(strtolower($row['name']), $pokemon_options);
                    echo $id ? $id : 'N/A';
                    ?>): 
                    Height <span class="highlight"><?php echo $row['height']; ?> dm</span>, 
                    Weight <span class="highlight"><?php echo $row['weight']; ?> hg</span>, 
                    Type <span class="highlight"><?php echo htmlspecialchars($row['type']); ?></span> 
                    (<?php echo $row['timestamp']; ?>)
                </li>
            <?php endwhile; ?>
        </ul>
    </div>
    <script>
        function filterPokemon() {
            const input = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.getElementsByClassName('pokemon-card');
            for (let i = 0; i < cards.length; i++) {
                const name = cards[i].getElementsByTagName('h3')[0].innerText.toLowerCase();
                cards[i].style.display = name.includes(input) ? 'block' : 'none';
            }
        }
    </script>
</body>
</html>
<?php $db->close(); ?>