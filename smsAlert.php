<?php
include 'settings.php';

// Utilisation des paramètres chargés depuis settings.php
$api_base_url = "https://api.weather.com/v2/pws/observations/current?apiKey=e1f10a1e78da46f5b10a1e78da96f525&stationId=";
$api_params = "&numericPrecision=decimal&format=json&units=m";

$tracking_file = 'thresholds_tracking.json';

date_default_timezone_set('Europe/Paris');

function getWeatherData($stationId, $api_base_url, $api_params) {
    $url = $api_base_url . $stationId . $api_params;
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (isset($data['observations'][0])) {
        $observation = $data['observations'][0];
        $metric = $observation['metric'];

        return [
            'T'   => $metric['temp'],
            'H'   => $observation['humidity'],
            'P'   => $metric['pressure'],
            'W'   => $metric['windSpeed'],
            'G'   => $metric['windGust'],
            'R'   => $metric['precipTotal'],
            'RR'  => $metric['precipRate'],
            'S'   => $observation['solarRadiation'],
            'UTime'=> strtotime($observation['obsTimeUtc'])
        ];
    }

    return null;
}

function formatTime($timestamp) {
    return date('H:i:s', $timestamp);
}

function checkThresholdsAndSendSMS($meteo_data, $thresholds, $tracking_file, $api_user, $api_key, $stationName) {
    $tracking = [];
    if (file_exists($tracking_file)) {
        $tracking = json_decode(file_get_contents($tracking_file), true);
    }

    $message = "";
    $sendSMS = false;
    $currentTime = time();

    foreach ($thresholds as $key => $limits) {
        if (isset($meteo_data[$key])) {
            $value = $meteo_data[$key];

            // Vérifie les seuils minimaux
            for ($i = 0; $i < count($limits['min']['values']); $i += 2) {
                if ($limits['min']['enabled']) {
                    $min = $limits['min']['values'][$i];
                    $max = $limits['min']['values'][$i + 1];

                    if ($value >= $min && $value <= $max) {
                        if (!isset($tracking[$stationName][$key]['min'][$i]) ||
                            ($currentTime - ($tracking[$stationName][$key]['min'][$i]['lastAlertTime'] ?? 0) >= $limits['interval'])) {

                            $sendSMS = true;
                            $message .= "⚠️ ALERTE A  $stationName ! ⚠️\n";
                            switch ($key) {
                                case 'T':
                                    $message .= "🌡️ La température passe en dessous de {$value}°C !\n";
                                    break;
                                case 'RR':
                                    $message .= "🌦️ L'intensité de pluie passe en dessous de {$value} mm/h !\n";
                                    break;
                                case 'R':
                                    $message .= "💧 Le cumul de pluie passe en dessous de {$value} mm !\n";
                                    break;
                                case 'W':
                                    $message .= "💨 Le vent souffle en dessous de {$value} km/h !\n";
                                    break;
                                case 'P':
                                    $message .= "📈 La pression passe en dessous de {$value} hPa !\n";
                                    break;
                                case 'H':
                                    $message .= "💨 L'humidité passe en dessous de {$value} % !\n";
                                    break;
                            }

                            $tracking[$stationName][$key]['min'][$i] = [
                                'lastAlertTime' => $currentTime
                            ];
                        }
                    }
                }
            }

            // Vérifie les seuils maximaux
            for ($i = 0; $i < count($limits['max']['values']); $i += 2) {
                if ($limits['max']['enabled']) {
                    $min = $limits['max']['values'][$i];
                    $max = $limits['max']['values'][$i + 1];

                    if ($value >= $min && $value <= $max) {
                        if (!isset($tracking[$stationName][$key]['max'][$i]) ||
                            ($currentTime - ($tracking[$stationName][$key]['max'][$i]['lastAlertTime'] ?? 0) >= $limits['interval'])) {

                            $sendSMS = true;
                            $message .= "⚠️ ALERTE A  $stationName ! ⚠️\n";
                            switch ($key) {
                                case 'T':
                                    $message .= "🌡️ La température passe au-dessus de {$value}°C !\n";
                                    break;
                                case 'RR':
                                    $message .= "🌦️ L'intensité de pluie passe au-dessus de {$value} mm/h !\n";
                                    break;
                                case 'R':
                                    $message .= "💧 Le cumul de pluie passe au-dessus de {$value} mm !\n";
                                    break;
                                case 'W':
                                    $message .= "💨 Le vent souffle au-dessus de {$value} km/h !\n";
                                    break;
                                case 'P':
                                    $message .= "📈 La pression passe au-dessus de {$value} hPa !\n";
                                    break;
                                case 'H':
                                    $message .= "💨 L'humidité passe au-dessus de {$value} % !\n";
                                    break;
                            }

                            $tracking[$stationName][$key]['max'][$i] = [
                                'lastAlertTime' => $currentTime
                            ];
                        }
                    }
                }
            }
        }
    }

    if ($sendSMS) {
        // Ajoute les données détaillées au message
        $message .= "\n✅Voici le détail des relevés :\n" .
            "🌡️Température : {$meteo_data['T']}°C\n" .
            "Humidité : {$meteo_data['H']}%\n" .
            "Pression : {$meteo_data['P']} hPa\n" .
            "Vent : {$meteo_data['W']} km/h\n" .
            "Rafales : {$meteo_data['G']} km/h\n" .
            "Pluie : {$meteo_data['R']} mm\n" .
            "🌦️Intensité de pluie : {$meteo_data['RR']} mm/h\n" .
            "Rayonnement solaire : {$meteo_data['S']} W/m²\n" .
            "Dernière mise à jour : " . formatTime($meteo_data['UTime']) . "\n";

        // Envoie le SMS
        file_get_contents("https://smsapi.free-mobile.fr/sendmsg?user=$api_user&pass=$api_key&msg=" . urlencode($message));
        echo "<p style='color: green;'>SMS envoyé avec succès !</p>";
    } else {
        echo "<p style='color: red;'>Aucun seuil franchi ou intervalle minimal non atteint. Pas de SMS envoyé.</p>";
    }

    file_put_contents($tracking_file, json_encode($tracking));
}

// Récupère et vérifie les données pour chaque station
foreach ($stations as $stationId => $stationInfo) {
    $stationName = $stationInfo["name"];
    $thresholds = $stationInfo["thresholds"];
    $alertEnabled = $stationInfo["alert"]; // Vérifie si les alertes sont activées pour cette station

    if (!$meteo = getWeatherData($stationId, $api_base_url, $api_params)) die("Erreur récupération météo.");

    // Vérifiez si les alertes sont activées avant de vérifier les seuils
    if ($alertEnabled) {
        checkThresholdsAndSendSMS($meteo, $thresholds, $tracking_file, $api_user, $api_key, $stationName);
    } else {
        echo "<p style='color: orange;'>Les alertes sont désactivées pour la station $stationName. Pas de SMS envoyé.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Relevés des stations surveillées</title>
</head>
<body>
    <h1>Relevés des stations surveillées</h1>
	<p>💡Paramétrez d'abord vos alertes avec le <a href="./config.php">fichier de configuration.</a></p>
    <?php foreach ($stations as $stationId => $stationInfo): ?>
        <?php
        $stationName = $stationInfo['name'];
        $meteo_data = getWeatherData($stationId, $api_base_url, $api_params);
        ?>
        <h2><?php echo $stationName; ?></h2>
        <ul>
            <li>Température : <?php echo $meteo_data['T']; ?>°C</li>
            <li>Humidité : <?php echo $meteo_data['H']; ?>%</li>
            <li>Pression : <?php echo $meteo_data['P']; ?> hPa</li>
            <li>Vent : <?php echo $meteo_data['W']; ?> km/h</li>
            <li>Rafales : <?php echo $meteo_data['G']; ?> km/h</li>
            <li>Pluie : <?php echo $meteo_data['R']; ?> mm</li>
            <li>Intensité de pluie : <?php echo $meteo_data['RR']; ?> mm/h</li>
            <li>Rayonnement solaire : <?php echo $meteo_data['S']; ?> W/m²</li>
            <li>Dernière mise à jour : <?php echo formatTime($meteo_data['UTime']); ?></li>
        </ul>
    <?php endforeach; ?>
</body>
</html>
