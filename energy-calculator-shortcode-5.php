<?php
/**
 * Plugin Name: Energy Calculator
 * Description: A simple energy calculator for WordPress.
 * Version: 1.3
 * Author: Jesús Hernández Saure
 */

// Shortcode function
function energy_calculator_shortcode() {
    ob_start();
    ?>
    <div id="energy-calculator">
        <h2>Calculadora de Sistemas Fotovoltaicos</h2>
        <div>
            <label for="system-type">Tipo de sistema:</label>
            <select id="system-type" onchange="toggleSystemType()">
                <option value="interconnected">Interconectado</option>
                <option value="autonomous">Aislado</option>
            </select>
        </div>
        
        <div id="interconnected-form">
            <h3>Sistema Interconectado</h3>
            <form id="interconnected-calculator-form">
                <div class="form-row">
                    <div class="form-column">
                        <label for="bimonthly-consumption">Consumo Bimestral promedio (kWh):</label>
                        <p>Para calcular esto, suma los consumos de los últimos 6 bimestres y divídelos entre 6.</p>
                        <input type="number" id="bimonthly-consumption" name="bimonthly-consumption" required>
                        <br>
                        <label for="module-power">Potencia en Módulos:</label>
                        <select id="module-power" name="module-power" required>
                            <option value="400">400W</option>
                            <option value="450">450W</option>
                            <option value="500">500W</option>
                            <option value="550">550W</option>
                            <option value="600">600W</option>
                            <option value="650">650W</option>
                        </select>
                    </div>
                    <div class="form-column">
                        <div id="interconnected-result"></div>
                        <div id="inverter-result"></div>
                    </div>
                </div>
                <button type="button" onclick="calculateInterconnected()">Calcular</button>
            </form>
        </div>
        
        <div id="autonomous-form" style="display:none;">
            <h3>Sistema Autónomo</h3>
            <form id="autonomous-calculator-form">
                <div id="equipment-list">
                    <!-- Equipment inputs will be added dynamically here -->
                </div>
                <button type="button" onclick="addEquipment()">Añadir Equipo</button>
                <br><br>
                <label for="loss-factor-autonomous">Factor de pérdida:</label>
                <input type="number" id="loss-factor-autonomous" name="loss-factor-autonomous" step="0.01" required>
                <br>
                <label for="peak-sun-hours-autonomous">Horas solar pico:</label>
                <input type="number" id="peak-sun-hours-autonomous" name="peak-sun-hours-autonomous" step="0.1" required>
                <br>
                <label for="module-power-autonomous">Potencia en Módulos en kW:</label>
                <input type="number" id="module-power-autonomous" name="module-power-autonomous" step="0.001" required>
                <br>
                <button type="button" onclick="calculateAutonomous()">Calcular</button>
            </form>
            <div id="autonomous-result"></div>
        </div>
    </div>
    <script>
        function toggleSystemType() {
            var systemType = document.getElementById('system-type').value;
            if (systemType === 'interconnected') {
                document.getElementById('interconnected-form').style.display = 'block';
                document.getElementById('autonomous-form').style.display = 'none';
            } else {
                document.getElementById('interconnected-form').style.display = 'none';
                document.getElementById('autonomous-form').style.display = 'block';
            }
        }

        function calculateInterconnected() {
            var bimonthlyConsumption = document.getElementById('bimonthly-consumption').value;
            var modulePower = document.getElementById('module-power').value;
            
            var lossFactor = 1.2; // 20% de pérdida
            var peakSunHours = 5.5;
            var daysPerBimester = 60;
            var modulePowerKW = modulePower / 1000;
            
            var requiredPower = ((bimonthlyConsumption * lossFactor) / daysPerBimester) / peakSunHours;
            var moduleCount = Math.ceil(requiredPower / modulePowerKW);
            moduleCount = moduleCount % 2 === 0 ? moduleCount : moduleCount + 1; // Redondear al número par superior
            
            var inverterPower = moduleCount * modulePowerKW;
            inverterPower = Math.ceil(inverterPower); // Redondear al número entero superior
            
            document.getElementById('interconnected-result').innerHTML = 'Número de módulos necesarios: ' + moduleCount;
            document.getElementById('inverter-result').innerHTML = 'Potencia necesaria del inversor: ' + inverterPower + ' kW';
        }

        function addEquipment() {
            var equipmentList = document.getElementById('equipment-list');
            var equipmentItem = document.createElement('div');
            equipmentItem.classList.add('equipment-item');
            equipmentItem.innerHTML = `
                <label>Nombre del equipo:</label>
                <input type="text" class="equipment-name" required>
                <br>
                <label>Cantidad:</label>
                <input type="number" class="equipment-quantity" required>
                <br>
                <label>Potencia (W):</label>
                <input type="number" class="equipment-power" required>
                <br>
                <label>Horas de uso (h):</label>
                <input type="number" class="equipment-hours" required>
                <br><br>
            `;
            equipmentList.appendChild(equipmentItem);
        }

        function calculateAutonomous() {
            var equipmentItems = document.getElementsByClassName('equipment-item');
            var totalDailyConsumptionWh = 0;

            for (var i = 0; i < equipmentItems.length; i++) {
                var quantity = equipmentItems[i].getElementsByClassName('equipment-quantity')[0].value;
                var power = equipmentItems[i].getElementsByClassName('equipment-power')[0].value;
                var hours = equipmentItems[i].getElementsByClassName('equipment-hours')[0].value;
                var dailyConsumptionWh = quantity * power * hours;
                totalDailyConsumptionWh += dailyConsumptionWh;
            }

            var totalDailyConsumptionKwh = totalDailyConsumptionWh / 1000;
            var lossFactor = document.getElementById('loss-factor-autonomous').value;
            var peakSunHours = document.getElementById('peak-sun-hours-autonomous').value;
            var modulePower = document.getElementById('module-power-autonomous').value;
            
            var requiredPower = totalDailyConsumptionKwh / (modulePower * lossFactor * peakSunHours);
            var moduleCount = Math.ceil(requiredPower / 2) * 2; // Redondear al número par superior
            
            document.getElementById('autonomous-result').innerHTML = 'Número de módulos necesarios: ' + moduleCount;
        }
    </script>
    <style>
        #energy-calculator {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            background-color: #f9f9f9;
        }
        #energy-calculator h2, #energy-calculator h3 {
            text-align: center;
        }
        #energy-calculator form {
            display: flex;
            flex-direction: column;
        }
        .form-row {
            display: flex;
            justify-content: space-between;
        }
        .form-column {
            width: 48%;
        }
        #energy-calculator label {
            margin: 10px 0 5px;
        }
        #energy-calculator input, #energy-calculator select {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }
        #energy-calculator button {
            padding: 10px;
            background-color: #0073aa;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        #energy-calculator button:hover {
            background-color: #005f8d;
        }
        #interconnected-result, #inverter-result, #autonomous-result {
            margin-top: 20px;
            text-align: center;
        }
        .equipment-item {
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
    </style>
    <?php
    return ob_get_clean();
}

// Register shortcode
add_shortcode('energy_calculator', 'energy_calculator_shortcode');
?>
