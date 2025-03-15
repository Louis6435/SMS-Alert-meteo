<?php
// Inclure les paramètres existants
include 'settings.php';

// Vérifie si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupère les données du formulaire
    $api_user = $_POST['api_user'];
    $api_key = $_POST['api_key'];
    $stations = [];

    // Traite les stations et leurs seuils
    foreach ($_POST['station_name'] as $index => $stationName) {
        $stationId = $_POST['station_id'][$index];
        $thresholds = [
            'T' => [
                'min' => ['values' => explode(',', $_POST['T_min'][$index]), 'enabled' => isset($_POST['T_min_enabled'][$index])],
                'max' => ['values' => explode(',', $_POST['T_max'][$index]), 'enabled' => isset($_POST['T_max_enabled'][$index])],
                'interval' => $_POST['T_interval'][$index]
            ],
            'RR' => [
                'min' => ['values' => explode(',', $_POST['RR_min'][$index]), 'enabled' => isset($_POST['RR_min_enabled'][$index])],
                'max' => ['values' => explode(',', $_POST['RR_max'][$index]), 'enabled' => isset($_POST['RR_max_enabled'][$index])],
                'interval' => $_POST['RR_interval'][$index]
            ],
            'R' => [
                'min' => ['values' => explode(',', $_POST['R_min'][$index]), 'enabled' => isset($_POST['R_min_enabled'][$index])],
                'max' => ['values' => explode(',', $_POST['R_max'][$index]), 'enabled' => isset($_POST['R_max_enabled'][$index])],
                'interval' => $_POST['R_interval'][$index]
            ],
            'W' => [
                'min' => ['values' => explode(',', $_POST['W_min'][$index]), 'enabled' => isset($_POST['W_min_enabled'][$index])],
                'max' => ['values' => explode(',', $_POST['W_max'][$index]), 'enabled' => isset($_POST['W_max_enabled'][$index])],
                'interval' => $_POST['W_interval'][$index]
            ],
            'H' => [
                'min' => ['values' => explode(',', $_POST['H_min'][$index]), 'enabled' => isset($_POST['H_min_enabled'][$index])],
                'max' => ['values' => explode(',', $_POST['H_max'][$index]), 'enabled' => isset($_POST['H_max_enabled'][$index])],
                'interval' => $_POST['H_interval'][$index]
            ],
            'P' => [
                'min' => ['values' => explode(',', $_POST['P_min'][$index]), 'enabled' => isset($_POST['P_min_enabled'][$index])],
                'max' => ['values' => explode(',', $_POST['P_max'][$index]), 'enabled' => isset($_POST['P_max_enabled'][$index])],
                'interval' => $_POST['P_interval'][$index]
            ],
        ];

        // Vérifie si les alertes sont activées pour cette station
        $alert = isset($_POST['alerts_enabled'][$index]) ? true : false;

        $stations[$stationId] = ['name' => $stationName, 'thresholds' => $thresholds, 'alert' => $alert];
    }

    // Génère le contenu du fichier settings.php
    $settingsContent = "<?php\n";
    $settingsContent .= "\$api_user = \"$api_user\";\n";
    $settingsContent .= "\$api_key = \"$api_key\";\n";
    $settingsContent .= "\$stations = " . var_export($stations, true) . ";\n";
    $settingsContent .= "?>";

    // Enregistre les paramètres dans settings.php
    file_put_contents('settings.php', $settingsContent);
    echo "<p style='color: green;'>Paramètres enregistrés avec succès !</p>";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration des Alertes Météo</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #3b3a3a;
            color: #fff;
            margin: 0;
            padding: 10px;
        }
        h1, h2, h3, h4 {
            text-align: center;
        }
		h2 {
			color: #a4c0fd;
			font-size: 22pt;
		}
        h3 {
			color: #91ec82;
			font-size: 18pt;
		}		
        form {
            max-width: 850px;
            margin: auto;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"] {
            width: 200px;
            padding: 8px;
            margin-bottom: 10px;
            border: 2px solid;
            border-radius: 5px;
            background-color: #555;
            color: #fff;
        }
        .station-block {
            border: 2px solid #ccc;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            background-color: #6e6e6e;
            box-shadow: 0 2px 8px rgba(255, 255, 255, 0.5);
        }
        .station-block p {
            font-size: 0.9em;
        }
        .alert-toggle {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    gap: 10px; /* Espace entre le texte et le bouton */
    margin: 10px 0;
}
        .alert-toggle label {
            font-size: 1em;
        }
        /* Style des boutons checkbox type switch */
        .alert-toggle input[type="checkbox"],
        input[type="checkbox"].alert-switch {
            width: 50px;
            height: 25px;
            appearance: none;
            background-color: #ccc;
            border-radius: 15px;
            position: relative;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .alert-toggle input[type="checkbox"]:checked,
        input[type="checkbox"].alert-switch:checked {
            background-color: #4caf50;
        }
        .alert-toggle input[type="checkbox"]::before,
        input[type="checkbox"].alert-switch::before {
            content: '';
            position: absolute;
            width: 23px;
            height: 23px;
            background-color: #fff;
            border-radius: 50%;
            top: 1px;
            left: 2px;
            transition: left 0.3s ease;
        }
		.slider-container input[type="range"] {
    width: 100%; /* Étire le curseur */
    max-width: 400px;
    height: 10px;
    background-color: #ccc;
    border-radius: 5px;
    appearance: none;
}

/* Style du curseur pour Chrome, Edge et Safari */
.slider-container input[type="range"]::-webkit-slider-thumb {
    appearance: none;
    width: 20px; 
    height: 20px; 
    background-color: #4caf50; 
    border-radius: 50%; 
    cursor: pointer;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
}

/* Style du curseur pour Firefox */
.slider-container input[type="range"]::-moz-range-thumb {
    width: 20px;
    height: 20px;
    background-color: #4caf50;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.2);
}

/* Style du curseur pour Internet Explorer */
.slider-container input[type="range"]::-ms-thumb {
    width: 20px;
    height: 20px;
    background-color: #4caf50;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 1px 5px rgba(0, 0, 0, 0.5);
}
        .alert-toggle input[type="checkbox"]:checked::before,
        input[type="checkbox"].alert-switch:checked::before {
            left: 24px;
        }
    </style>
</head>
<body>
    <h1>Configuration des Alertes Météo</h1>
    <form method="POST" action="">
        <label for="api_user">Identifiant Free :</label>
        <input type="text" id="api_user" name="api_user" value="<?php echo htmlspecialchars($api_user); ?>" required>

        <label for="api_key">Clé API :</label>
        <input type="text" id="api_key" name="api_key" value="<?php echo htmlspecialchars($api_key); ?>" required>

        <h2>Stations</h2>
        <div id="stations">
            <?php foreach ($stations as $index => $station): ?>
                <div class="station-block">
                    <h3><?php echo htmlspecialchars($station['name']); ?></h3>
                    <p>Quelques consignes:<br> 💡 Il faut respecter le format suivant pour que les alertes s'effectuent. Par exemple si l'on veut être alerté quand la température passe entre 0.0 et 0.8°C ou entre 4.5 et 5.6°C, il faut indiquer les plages de valeurs souhaitées comme ceci : 0.0,0.8,4.5,5.6. Même logique pour les autres variables.<br>💡 Il faut impérativement mettre des fourchette (plage de valeurs) et pas des valeurs uniques. Ainsi, pour être alerté lorsque la température passe sous 0°C il faut renseigner: -0.1,0 <br>💡les intervalles de temps pour les alertes sont donnés en secondes:<br>3600 → 1h<br>43200 → 12h<br>86400 → 24h<br>

                    <label for="station_id_<?php echo $index; ?>">ID de la station :</label>
                    <input type="text" id="station_id_<?php echo $index; ?>" name="station_id[]" value="<?php echo htmlspecialchars($index); ?>" required>

                    <label for="station_name_<?php echo $index; ?>">Nom de la station (avec lequel la station apparaitra dans les alertes):</label>
                    <input type="text" id="station_name_<?php echo $index; ?>" name="station_name[]" value="<?php echo htmlspecialchars($station['name']); ?>" required>

                    <div class="alert-toggle">
                        <label for="alerts_enabled_<?php echo $index; ?>">🚨 Activer toutes les alertes de cette station :</label>
                        <input type="checkbox" id="alerts_enabled_<?php echo $index; ?>" name="alerts_enabled[]" <?php echo $station['alert'] ? 'checked' : ''; ?>>
                    </div>

                    <h4>Seuils</h4>
                    <label for="T_min_<?php echo $index; ?>">Température Min :</label>
                    <input type="text" id="T_min_<?php echo $index; ?>" name="T_min[]" value="<?php echo htmlspecialchars(implode(',', $station['thresholds']['T']['min']['values'])); ?>" required>

                    <div class="alert-toggle">
                        <label for="T_min_enabled_<?php echo $index; ?>">Activer Alerte Min :</label>
                        <input type="checkbox" id="T_min_enabled_<?php echo $index; ?>" class="alert-switch" name="T_min_enabled[]" <?php echo $station['thresholds']['T']['min']['enabled'] ? 'checked' : ''; ?>>
                    </div>

                    <label for="T_max_<?php echo $index; ?>">Température Max :</label>
                    <input type="text" id="T_max_<?php echo $index; ?>" name="T_max[]" value="<?php echo htmlspecialchars(implode(',', $station['thresholds']['T']['max']['values'])); ?>" required>

                    <div class="alert-toggle">
                        <label for="T_max_enabled_<?php echo $index; ?>">Activer Alerte Max :</label>
                        <input type="checkbox" id="T_max_enabled_<?php echo $index; ?>" class="alert-switch" name="T_max_enabled[]" <?php echo $station['thresholds']['T']['max']['enabled'] ? 'checked' : ''; ?>>
                    </div>

                    <div class="slider-container">
                        <label for="T_interval_<?php echo $index; ?>">Durée minimale entre deux alertes pour le paramètre Température :</label>
                        <input type="range" id="T_interval_<?php echo $index; ?>" name="T_interval[]" min="0" max="86400" step="100" value="<?php echo htmlspecialchars($station['thresholds']['T']['interval']); ?>" required>
                        <span id="T_interval_value_<?php echo $index; ?>"><?php echo $station['thresholds']['T']['interval']; ?> secondes</span>
                    </div>

                    <!-- Répétez pour RR, R, W, H, P -->
                    <label for="RR_min_<?php echo $index; ?>">Intensité de pluie Min :</label>
                    <input type="text" id="RR_min_<?php echo $index; ?>" name="RR_min[]" value="<?php echo htmlspecialchars(implode(',', $station['thresholds']['RR']['min']['values'])); ?>" required>

                    <div class="alert-toggle">
                        <label for="RR_min_enabled_<?php echo $index; ?>">Activer Alerte Min :</label>
                        <input type="checkbox" id="RR_min_enabled_<?php echo $index; ?>" class="alert-switch" name="RR_min_enabled[]" <?php echo $station['thresholds']['RR']['min']['enabled'] ? 'checked' : ''; ?>>
                    </div>

                    <label for="RR_max_<?php echo $index; ?>">Intensité de pluie Max :</label>
                    <input type="text" id="RR_max_<?php echo $index; ?>" name="RR_max[]" value="<?php echo htmlspecialchars(implode(',', $station['thresholds']['RR']['max']['values'])); ?>" required>

                    <div class="alert-toggle">
                        <label for="RR_max_enabled_<?php echo $index; ?>">Activer Alerte Max :</label>
                        <input type="checkbox" id="RR_max_enabled_<?php echo $index; ?>" class="alert-switch" name="RR_max_enabled[]" <?php echo $station['thresholds']['RR']['max']['enabled'] ? 'checked' : ''; ?>>
                    </div>

                    <div class="slider-container">
                        <label for="RR_interval_<?php echo $index; ?>">Durée minimale entre deux alertes pour le paramètre Intensité de pluie :</label>
                        <input type="range" id="RR_interval_<?php echo $index; ?>" name="RR_interval[]" min="0" max="86400" step="100" value="<?php echo htmlspecialchars($station['thresholds']['RR']['interval']); ?>" required>
                        <span id="RR_interval_value_<?php echo $index; ?>"><?php echo $station['thresholds']['RR']['interval']; ?> secondes</span>
                    </div>

                    <label for="R_min_<?php echo $index; ?>">Cumul de pluie Min :</label>
                    <input type="text" id="R_min_<?php echo $index; ?>" name="R_min[]" value="<?php echo htmlspecialchars(implode(',', $station['thresholds']['R']['min']['values'])); ?>" required>

                    <div class="alert-toggle">
                        <label for="R_min_enabled_<?php echo $index; ?>">Activer Alerte Min :</label>
                        <input type="checkbox" id="R_min_enabled_<?php echo $index; ?>" class="alert-switch" name="R_min_enabled[]" <?php echo $station['thresholds']['R']['min']['enabled'] ? 'checked' : ''; ?>>
                    </div>

                    <label for="R_max_<?php echo $index; ?>">Cumul de pluie Max :</label>
                    <input type="text" id="R_max_<?php echo $index; ?>" name="R_max[]" value="<?php echo htmlspecialchars(implode(',', $station['thresholds']['R']['max']['values'])); ?>" required>

                    <div class="alert-toggle">
                        <label for="R_max_enabled_<?php echo $index; ?>">Activer Alerte Max :</label>
                        <input type="checkbox" id="R_max_enabled_<?php echo $index; ?>" class="alert-switch" name="R_max_enabled[]" <?php echo $station['thresholds']['R']['max']['enabled'] ? 'checked' : ''; ?>>
                    </div>

                    <div class="slider-container">
                        <label for="R_interval_<?php echo $index; ?>">Durée minimale entre deux alertes pour le paramètre Cumul de pluie :</label>
                        <input type="range" id="R_interval_<?php echo $index; ?>" name="R_interval[]" min="0" max="86000" step="100" value="<?php echo htmlspecialchars($station['thresholds']['R']['interval']); ?>" required>
                        <span id="R_interval_value_<?php echo $index; ?>"><?php echo $station['thresholds']['R']['interval']; ?> secondes</span>
                    </div>

                    <label for="W_min_<?php echo $index; ?>">Vent Min :</label>
                    <input type="text" id="W_min_<?php echo $index; ?>" name="W_min[]" value="<?php echo htmlspecialchars(implode(',', $station['thresholds']['W']['min']['values'])); ?>" required>

                    <div class="alert-toggle">
                        <label for="W_min_enabled_<?php echo $index; ?>">Activer Alerte Min :</label>
                        <input type="checkbox" id="W_min_enabled_<?php echo $index; ?>" class="alert-switch" name="W_min_enabled[]" <?php echo $station['thresholds']['W']['min']['enabled'] ? 'checked' : ''; ?>>
                    </div>

                    <label for="W_max_<?php echo $index; ?>">Vent Max :</label>
                    <input type="text" id="W_max_<?php echo $index; ?>" name="W_max[]" value="<?php echo htmlspecialchars(implode(',', $station['thresholds']['W']['max']['values'])); ?>" required>

                    <div class="alert-toggle">
                        <label for="W_max_enabled_<?php echo $index; ?>">Activer Alerte Max :</label>
                        <input type="checkbox" id="W_max_enabled_<?php echo $index; ?>" class="alert-switch" name="W_max_enabled[]" <?php echo $station['thresholds']['W']['max']['enabled'] ? 'checked' : ''; ?>>
                    </div>

                    <div class="slider-container">
                        <label for="W_interval_<?php echo $index; ?>">Durée minimale entre deux alertes pour le paramètre Vent :</label>
                        <input type="range" id="W_interval_<?php echo $index; ?>" name="W_interval[]" min="0" max="86400" step="100" value="<?php echo htmlspecialchars($station['thresholds']['W']['interval']); ?>" required>
                        <span id="W_interval_value_<?php echo $index; ?>"><?php echo $station['thresholds']['W']['interval']; ?> secondes</span>
                    </div>

                    <label for="H_min_<?php echo $index; ?>">Humidité Min :</label>
                    <input type="text" id="H_min_<?php echo $index; ?>" name="H_min[]" value="<?php echo htmlspecialchars(implode(',', $station['thresholds']['H']['min']['values'])); ?>" required>

                    <div class="alert-toggle">
                        <label for="H_min_enabled_<?php echo $index; ?>">Activer Alerte Min :</label>
                        <input type="checkbox" id="H_min_enabled_<?php echo $index; ?>" class="alert-switch" name="H_min_enabled[]" <?php echo $station['thresholds']['H']['min']['enabled'] ? 'checked' : ''; ?>>
                    </div>

                    <label for="H_max_<?php echo $index; ?>">Humidité Max :</label>
                    <input type="text" id="H_max_<?php echo $index; ?>" name="H_max[]" value="<?php echo htmlspecialchars(implode(',', $station['thresholds']['H']['max']['values'])); ?>" required>

                    <div class="alert-toggle">
                        <label for="H_max_enabled_<?php echo $index; ?>">Activer Alerte Max :</label>
                        <input type="checkbox" id="H_max_enabled_<?php echo $index; ?>" class="alert-switch" name="H_max_enabled[]" <?php echo $station['thresholds']['H']['max']['enabled'] ? 'checked' : ''; ?>>
                    </div>

                    <div class="slider-container">
                        <label for="H_interval_<?php echo $index; ?>">Durée minimale entre deux alertes pour le paramètre Humidité :</label>
                        <input type="range" id="H_interval_<?php echo $index; ?>" name="H_interval[]" min="0" max="86400" step="100" value="<?php echo htmlspecialchars($station['thresholds']['H']['interval']); ?>" required>
                        <span id="H_interval_value_<?php echo $index; ?>"><?php echo $station['thresholds']['H']['interval']; ?> secondes</span>
                    </div>

                    <label for="P_min_<?php echo $index; ?>">Pression Min :</label>
                    <input type="text" id="P_min_<?php echo $index; ?>" name="P_min[]" value="<?php echo htmlspecialchars(implode(',', $station['thresholds']['P']['min']['values'])); ?>" required>

                    <div class="alert-toggle">
                        <label for="P_min_enabled_<?php echo $index; ?>">Activer Alerte Min :</label>
                        <input type="checkbox" id="P_min_enabled_<?php echo $index; ?>" class="alert-switch" name="P_min_enabled[]" <?php echo $station['thresholds']['P']['min']['enabled'] ? 'checked' : ''; ?>>
                    </div>

                    <label for="P_max_<?php echo $index; ?>">Pression Max :</label>
                    <input type="text" id="P_max_<?php echo $index; ?>" name="P_max[]" value="<?php echo htmlspecialchars(implode(',', $station['thresholds']['P']['max']['values'])); ?>" required>

                    <div class="alert-toggle">
                        <label for="P_max_enabled_<?php echo $index; ?>">Activer Alerte Max :</label>
                        <input type="checkbox" id="P_max_enabled_<?php echo $index; ?>" class="alert-switch" name="P_max_enabled[]" <?php echo $station['thresholds']['P']['max']['enabled'] ? 'checked' : ''; ?>>
                    </div>

                    <div class="slider-container">
                        <label for="P_interval_<?php echo $index; ?>">Durée minimale entre deux alertes pour le paramètre Pression :</label>
                        <input type="range" id="P_interval_<?php echo $index; ?>" name="P_interval[]" min="0" max="86400" step="100" value="<?php echo htmlspecialchars($station['thresholds']['P']['interval']); ?>" required>
                        <span id="P_interval_value_<?php echo $index; ?>"><?php echo $station['thresholds']['P']['interval']; ?> secondes</span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" onclick="addStation()">Ajouter une station</button><br>
        <input type="submit" value="Enregistrer">
    </form>

    <script>
        let stationIndex = <?php echo count($stations); ?>;
        function addStation() {
            const stationsDiv = document.getElementById('stations');
            const newStationDiv = document.createElement('div');
            newStationDiv.className = 'station-block';
            newStationDiv.innerHTML = `
                <h3>Nouvelle Station</h3>
				<p>Quelques consignes:<br> 💡 Il faut respecter le format suivant pour que les alertes s'effectuent. Par exemple si l'on veut être alerté quand la température passe entre 0.0 et 0.8°C ou entre 4.5 et 5.6°C, il faut indiquer les plages de valeurs souhaitées comme ceci : 0.0,0.8,4.5,5.6. Même logique pour les autres variables.<br>💡 Il faut impérativement mettre des fourchette (plage de valeurs) et pas des valeurs uniques. Ainsi, pour être alerté lorsque la température passe sous 0°C il faut renseigner: -0.1,0 <br>💡les intervalles de temps pour les alertes sont donnés en secondes:<br>3600 → 1h<br>43200 → 12h<br>86400 → 24h<br>💡 Une fois vos paramètres enregistrés paramétrez une tache cron avec l'url votresite.fr/chemin/vers/votre/script/smsAlert.php. Des services gratuits comme <a href="https://cron-job.org/en/">cron-job.org </a> feront l'affaire; la tâche peut-être exécutée toutes les 2 minutes souci</p>
                <label for="station_id_${stationIndex}">ID de la station :</label>
                <input type="text" id="station_id_${stationIndex}" name="station_id[]" required><br>

                <label for="station_name_${stationIndex}">Nom de la station :</label>
                <input type="text" id="station_name_${stationIndex}" name="station_name[]" required><br>

                <div class="alert-toggle">
                    <label for="alerts_enabled_${stationIndex}">Activer les alertes :</label>
                    <input type="checkbox" id="alerts_enabled_${stationIndex}" name="alerts_enabled[]">
                </div>

                <h4>Seuils</h4>
                <label for="T_min_${stationIndex}">Température Min :</label>
                <input type="text" id="T_min_${stationIndex}" name="T_min[]" value="N" required>
                <div class="alert-toggle">
                    <label for="T_min_enabled_${stationIndex}">Activer Alerte Min :</label>
                    <input type="checkbox" id="T_min_enabled_${stationIndex}" class="alert-switch" name="T_min_enabled[]">
                </div>

                <label for="T_max_${stationIndex}">Température Max :</label>
                <input type="text" id="T_max_${stationIndex}" name="T_max[]" value="N" required>
                <div class="alert-toggle">
                    <label for="T_max_enabled_${stationIndex}">Activer Alerte Max :</label>
                    <input type="checkbox" id="T_max_enabled_${stationIndex}" class="alert-switch" name="T_max_enabled[]">
                </div>

                <div class="slider-container">
                    <label for="T_interval_${stationIndex}">Durée minimale entre deux alertes pour le paramètre Température :</label>
                    <input type="range" id="T_interval_${stationIndex}" name="T_interval[]" min="0" max="86400" step="100" value="10" required>
                    <span id="T_interval_value_${stationIndex}">0 secondes</span>
                </div>

                <label for="RR_min_${stationIndex}">Intensité de pluie Min :</label>
                <input type="text" id="RR_min_${stationIndex}" name="RR_min[]" value="N" required>
                <div class="alert-toggle">
                    <label for="RR_min_enabled_${stationIndex}">Activer Alerte Min :</label>
                    <input type="checkbox" id="RR_min_enabled_${stationIndex}" class="alert-switch" name="RR_min_enabled[]">
                </div>

                <label for="RR_max_${stationIndex}">Intensité de pluie Max :</label>
                <input type="text" id="RR_max_${stationIndex}" name="RR_max[]" value="N" required>
                <div class="alert-toggle">
                    <label for="RR_max_enabled_${stationIndex}">Activer Alerte Max :</label>
                    <input type="checkbox" id="RR_max_enabled_${stationIndex}" class="alert-switch" name="RR_max_enabled[]">
                </div>

                <div class="slider-container">
                    <label for="RR_interval_${stationIndex}">Durée minimale entre deux alertes pour le paramètre Intensité de pluie :</label>
                    <input type="range" id="RR_interval_${stationIndex}" name="RR_interval[]" min="0" max="86400" step="100" value="10" required>
                    <span id="RR_interval_value_${stationIndex}">0 secondes</span>
                </div>

                <label for="R_min_${stationIndex}">Cumul de pluie Min :</label>
                <input type="text" id="R_min_${stationIndex}" name="R_min[]" value="N" required>
                <div class="alert-toggle">
                    <label for="R_min_enabled_${stationIndex}">Activer Alerte Min :</label>
                    <input type="checkbox" id="R_min_enabled_${stationIndex}" class="alert-switch" name="R_min_enabled[]">
                </div>

                <label for="R_max_${stationIndex}">Cumul de pluie Max :</label>
                <input type="text" id="R_max_${stationIndex}" name="R_max[]" value="N" required>
                <div class="alert-toggle">
                    <label for="R_max_enabled_${stationIndex}">Activer Alerte Max :</label>
                    <input type="checkbox" id="R_max_enabled_${stationIndex}" class="alert-switch" name="R_max_enabled[]">
                </div>

                <div class="slider-container">
                    <label for="R_interval_${stationIndex}">Durée minimale entre deux alertes pour le paramètre Cumul de pluie :</label>
                    <input type="range" id="R_interval_${stationIndex}" name="R_interval[]" min="0" max="86400" step="100" value="10" required>
                    <span id="R_interval_value_${stationIndex}">0 secondes</span>
                </div>

                <label for="W_min_${stationIndex}">Vent Min :</label>
                <input type="text" id="W_min_${stationIndex}" name="W_min[]" value="N" required>
                <div class="alert-toggle">
                    <label for="W_min_enabled_${stationIndex}">Activer Alerte Min :</label>
                    <input type="checkbox" id="W_min_enabled_${stationIndex}" class="alert-switch" name="W_min_enabled[]">
                </div>

                <label for="W_max_${stationIndex}">Vent Max :</label>
                <input type="text" id="W_max_${stationIndex}" name="W_max[]" value="N" required>
                <div class="alert-toggle">
                    <label for="W_max_enabled_${stationIndex}">Activer Alerte Max :</label>
                    <input type="checkbox" id="W_max_enabled_${stationIndex}" class="alert-switch" name="W_max_enabled[]">
                </div>

                <div class="slider-container">
                    <label for="W_interval_${stationIndex}">Durée minimale entre deux alertes pour le paramètre Vent :</label>
                    <input type="range" id="W_interval_${stationIndex}" name="W_interval[]" min="0" max="86400" step="100" value="10" required>
                    <span id="W_interval_value_${stationIndex}">0 secondes</span>
                </div>

                <label for="H_min_${stationIndex}">Humidité Min :</label>
                <input type="text" id="H_min_${stationIndex}" name="H_min[]" value="N" required>
                <div class="alert-toggle">
                    <label for="H_min_enabled_${stationIndex}">Activer Alerte Min :</label>
                    <input type="checkbox" id="H_min_enabled_${stationIndex}" class="alert-switch" name="H_min_enabled[]">
                </div>

                <label for="H_max_${stationIndex}">Humidité Max :</label>
                <input type="text" id="H_max_${stationIndex}" name="H_max[]" value="N" required>
                <div class="alert-toggle">
                    <label for="H_max_enabled_${stationIndex}">Activer Alerte Max :</label>
                    <input type="checkbox" id="H_max_enabled_${stationIndex}" class="alert-switch" name="H_max_enabled[]">
                </div>

                <div class="slider-container">
                    <label for="H_interval_${stationIndex}">Durée minimale entre deux alertes pour le paramètre Humidité :</label>
                    <input type="range" id="H_interval_${stationIndex}" name="H_interval[]" min="0" max="86400" step="100" value="10" required>
                    <span id="H_interval_value_${stationIndex}">0 secondes</span>
                </div>

                <label for="P_min_${stationIndex}">Pression Min :</label>
                <input type="text" id="P_min_${stationIndex}" name="P_min[]" value="N" required>
                <div class="alert-toggle">
                    <label for="P_min_enabled_${stationIndex}">Activer Alerte Min :</label>
                    <input type="checkbox" id="P_min_enabled_${stationIndex}" class="alert-switch" name="P_min_enabled[]">
                </div>

                <label for="P_max_${stationIndex}">Pression Max :</label>
                <input type="text" id="P_max_${stationIndex}" name="P_max[]" value="N" required>
                <div class="alert-toggle">
                    <label for="P_max_enabled_${stationIndex}">Activer Alerte Max :</label>
                    <input type="checkbox" id="P_max_enabled_${stationIndex}" class="alert-switch" name="P_max_enabled[]">
                </div>

                <div class="slider-container">
                    <label for="P_interval_${stationIndex}">Durée minimale entre deux alertes pour le paramètre Pression :</label>
                    <input type="range" id="P_interval_${stationIndex}" name="P_interval[]" min="0" max="86400" step="100" value="10" required>
                    <span id="P_interval_value_${stationIndex}">0 secondes</span>
                </div>
            `;
            stationsDiv.appendChild(newStationDiv);
            stationIndex++;
            updateSliderValues();
        }

        function updateSliderValues() {
            const sliders = document.querySelectorAll('input[type="range"]');
            sliders.forEach(slider => {
                slider.addEventListener('input', function() {
                    const valueSpan = document.getElementById(this.id.replace('_interval', '_interval_value'));
                    valueSpan.textContent = this.value + ' secondes';
                });
            });
        }

        updateSliderValues();
    </script>
	</body>
</html>
