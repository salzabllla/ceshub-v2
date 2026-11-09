const clientId = "cespv_web_" + Math.random().toString(16).substr(2, 8);
        const client = new Paho.MQTT.Client("broker.emqx.io", 8084, "/mqtt", clientId);

        let globalTimeout, timeoutAC, timeoutDC, timeoutBMS;
        
        // 3.5 Detik: Waktu batas paling ideal. ESP32 ngirim tiap 2 detik + 1.5 detik toleransi internet.
        const TIMEOUT_DURATION = 8000; 

        function setGlobalMqttState(status) {
            const dot = document.getElementById("mqtt-indicator");
            const label = document.getElementById("mqtt-status");
            for(const node of [dot,label]) node.dataset.connectionState = status === "live" ? "online" : status === "waiting" ? "waiting" : "offline";
            
            if (status === 'live') {
                dot.style.color = '#10b981'; label.innerText = "Terhubung (Live)"; label.style.color = '#10b981';
            } else if (status === 'offline') {
                dot.style.color = '#ef4444'; label.innerText = "Koneksi Terputus"; label.style.color = '#ef4444';
            } else if (status === 'waiting') {
                dot.style.color = '#f59e0b'; label.innerText = "Menunggu Data..."; label.style.color = '#f59e0b';
            } else if (status === 'web_error') {
                dot.style.color = '#ef4444'; label.innerText = "Server MQTT Error"; label.style.color = '#ef4444';
            }
        }

        // FUNGSI OTOMATIS MERESET ANGKA KE 0.00 KETIKA PERANGKAT MATI
        function resetNilaiSensor(sensor) {
            if (sensor === 'ac') {
                document.getElementById("rt-vA").innerText = "0.00"; document.getElementById("rt-cA").innerText = "0.000";
                document.getElementById("rt-vB").innerText = "0.00"; document.getElementById("rt-cB").innerText = "0.000";
                document.getElementById("rt-vC").innerText = "0.00"; document.getElementById("rt-cC").innerText = "0.000";
            } else if (sensor === 'dc') {
                document.getElementById("rt-dcV").innerText = "0.00"; document.getElementById("rt-dcA").innerText = "0.00";
                document.getElementById("rt-dcW").innerText = "0.00"; document.getElementById("rt-dcWh").innerText = "0.00";
            } else if (sensor === 'bms') {
                document.getElementById("rt-bmsV").innerText = "0.00"; document.getElementById("rt-bmsA").innerText = "0.00";
                document.getElementById("rt-bmsSoc").innerText = "0.00"; document.getElementById("rt-bmsTemp").innerText = "0.00";
            }
        }

        function setSensorState(sensor, isOnline) {
            const dot = document.getElementById('dot-tab-' + sensor);
            if (!dot) return;
            dot.dataset.connectionState = isOnline ? 'online' : 'offline';
            if (isOnline) {
                dot.style.backgroundColor = '#10b981'; dot.style.boxShadow = '0 0 6px #10b981'; dot.title = 'Online (Live)';
            } else {
                dot.style.backgroundColor = '#ef4444'; dot.style.boxShadow = '0 0 6px #ef4444'; dot.title = 'Offline';
                resetNilaiSensor(sensor);
            }
        }

        setGlobalMqttState('waiting');
        setSensorState('ac', false); setSensorState('dc', false); setSensorState('bms', false);

        // Jika koneksi web terputus dari MQTT, maka INSTAN (0 detik) direset ke 0 semua
        client.onConnectionLost = function(responseObject) { 
            if (responseObject.errorCode !== 0) setGlobalMqttState('web_error'); else setGlobalMqttState('offline');
            setSensorState('ac', false); setSensorState('dc', false); setSensorState('bms', false);
        };

        client.onMessageArrived = function(message) {
            setGlobalMqttState('live');
            
            // Timeout Global (jika sama sekali tidak ada data dari alat manapun selama 3,5 detik)
            clearTimeout(globalTimeout);
            globalTimeout = setTimeout(() => {
                setGlobalMqttState('offline');
                setSensorState('ac', false); setSensorState('dc', false); setSensorState('bms', false);
            }, TIMEOUT_DURATION);

            try {
                const data = JSON.parse(message.payloadString);

                // DATA PZEM-017 (DC)
                if (message.destinationName === "digitaltwin/monitoring/data") {
                    if(data.tegangan != null && data.tegangan !== undefined) {
                        setSensorState('dc', true);
                        clearTimeout(timeoutDC);
                        timeoutDC = setTimeout(() => setSensorState('dc', false), TIMEOUT_DURATION); 

                        document.getElementById("rt-dcV").innerText = data.tegangan.toFixed(2);
                        document.getElementById("rt-dcA").innerText = data.arus.toFixed(2);
                        document.getElementById("rt-dcW").innerText = data.daya.toFixed(2);
                        document.getElementById("rt-dcWh").innerText = data.energi.toFixed(2);
                    }
                }
                // DATA TEM055 (AC)
                else if (message.destinationName === "sensor/kwhmeter/data") {
                    if(data.tegangan_A != null && data.tegangan_A !== undefined) {
                        setSensorState('ac', true);
                        clearTimeout(timeoutAC);
                        timeoutAC = setTimeout(() => setSensorState('ac', false), TIMEOUT_DURATION);

                        document.getElementById("rt-vA").innerText = data.tegangan_A.toFixed(2);
                        document.getElementById("rt-cA").innerText = data.arus_A.toFixed(3);
                        document.getElementById("rt-vB").innerText = data.tegangan_B.toFixed(2);
                        document.getElementById("rt-cB").innerText = data.arus_B.toFixed(3);
                        document.getElementById("rt-vC").innerText = data.tegangan_C.toFixed(2);
                        document.getElementById("rt-cC").innerText = data.arus_C.toFixed(3);
                    }
                }
                // DATA BMS DALY R24PK
                else if (message.destinationName === "sensor/bms/data") {
                    if(data.tegangan != null && data.tegangan !== undefined) {
                        setSensorState('bms', true);
                        clearTimeout(timeoutBMS);
                        timeoutBMS = setTimeout(() => setSensorState('bms', false), TIMEOUT_DURATION);

                        document.getElementById("rt-bmsV").innerText = data.tegangan.toFixed(2);
                        document.getElementById("rt-bmsA").innerText = data.arus.toFixed(2);
                        document.getElementById("rt-bmsSoc").innerText = data.soc.toFixed(1);
                        document.getElementById("rt-bmsTemp").innerText = data.suhu.toFixed(1);
                    }
                }
            } catch (e) {}
        };

        client.connect({
            useSSL: true,
            onSuccess: function() {
                setGlobalMqttState('waiting');
                client.subscribe("sensor/kwhmeter/data");
                client.subscribe("digitaltwin/monitoring/data");
                client.subscribe("sensor/bms/data"); 
            }
        });