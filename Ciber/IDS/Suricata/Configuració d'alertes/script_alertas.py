import json
import requests
from requests.exceptions import RequestException
from watchdog.observers import Observer
from watchdog.events import FileSystemEventHandler
import time
import os
import socket

WEBHOOK_URL = ""
EVE_PATH = "/var/log/suricata/eve.json"

# Detectar automàticament la IP pública d'aquest VPS
try:
    s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    s.connect(("8.8.8.8", 80))
    VPS_IP = s.getsockname()[0]
    s.close()
except Exception:
    VPS_IP = "141.227.137.228" # Hardcoded per si falla la detecció

last_size = 0
last_alerts = {}
ALERT_COOLDOWN = 10
MAX_RETRIES = 3
DELAY_BETWEEN_ALERTS = 0.5

SEVERITY_CONFIG = {
    1: {"color": 0xFF0000, "label": "🔴 ALTA (Criticitat 1)"},
    2: {"color": 0xFFA500, "label": "🟠 MÈDIA (Criticitat 2)"},
    3: {"color": 0xFFFF00, "label": "🟡 BAIXA (Criticitat 3)"},
}

def send_webhook(payload):
    for attempt in range(1, MAX_RETRIES + 1):
        try:
            response = requests.post(WEBHOOK_URL, json=payload, timeout=5)
            response.raise_for_status()
            return True
        except RequestException as e:
            print(f"[Intento {attempt}] Error: {e}")
            time.sleep(1)
    return False

class EveHandler(FileSystemEventHandler):
    def on_modified(self, event):
        global last_size
        if event.src_path != EVE_PATH:
            return

        try:
            current_size = os.path.getsize(EVE_PATH)
        except OSError:
            return

        if current_size < last_size:
            last_size = 0

        with open(EVE_PATH, "r") as f:
            f.seek(last_size)
            lines = f.readlines()
            last_size = f.tell()

        for line in lines:
            line = line.strip()
            if not line:
                continue

            try:
                event_json = json.loads(line)
            except json.JSONDecodeError:
                continue

            if "alert" not in event_json:
                continue

            alert_data = event_json["alert"]
            signature = alert_data.get("signature", "Signatura Desconeguda")

            # Filtrar brossa de capa 2
            if "Ethertype unknown" in signature or "decoder" in alert_data.get("category", "").lower():
                continue

            category = alert_data.get("category", "Sense categoria registrada")
            severity = alert_data.get("severity", 3)

            timestamp = event_json.get("timestamp", "")
            timestamp_iso = timestamp.replace(" ", "T") if " " in timestamp else timestamp

            # Extracció inicial de xarxa
            src_ip = event_json.get("src_ip") or event_json.get("flow", {}).get("src_ip", "IP Desconeguda")
            dest_ip = event_json.get("dest_ip") or event_json.get("flow", {}).get("dest_ip", "IP Desconeguda")
            src_port = event_json.get("src_port") or event_json.get("flow", {}).get("src_port", "N/A")
            dest_port = event_json.get("dest_port") or event_json.get("flow", {}).get("dest_port", "N/A")
            proto = event_json.get("proto", "TCP")
            interface = event_json.get("in_iface") or "enp6s16"

            # 🔄 INTEL·LIGÈNCIA ANTI-INVERSIÓ:
            # Si l'origen és aquest VPS, significa que Suricata ha registrat la resposta. Girem els rols.
            if src_ip == VPS_IP:
                src_ip, dest_ip = dest_ip, src_ip
                src_port, dest_port = dest_port, src_port

            app_proto = event_json.get("app_proto") or event_json.get("flow", {}).get("service", "")
            if not app_proto or app_proto == "failed":
                app_proto = f"Port {dest_port}"
            else:
                app_proto = app_proto.upper()

            # Anti-Spam
            alert_key = f"{signature}-{src_ip}"
            now = time.time()
            if alert_key in last_alerts and now - last_alerts[alert_key] < ALERT_COOLDOWN:
                continue
            last_alerts[alert_key] = now

            fields = [
                {"name": "🚨 Firma de l'Atac", "value": f"`{signature}`", "inline": False},
                {"name": "📁 Categoria", "value": f"`{category}`", "inline": True},
                {"name": "📡 Interfície VPS", "value": f"`{interface}` ({proto})", "inline": True},
                {"name": "📍 Origen (Atacant)", "value": f"🔗 IP: `{src_ip}`\n🔌 Port: `{src_port}`", "inline": True},
                {"name": "➡️ Destí (El teu VPS)", "value": f"💻 IP: `{dest_ip}`\n🔌 Port: `{dest_port}`", "inline": True},
                {"name": "🎯 Objectiu de l'Atac", "value": f"⚙️ `{app_proto}`", "inline": False}
            ]

            sev_data = SEVERITY_CONFIG.get(severity, {"color": 0x3498DB, "label": "🔵 INFORMATIVA"})
            payload = {
                "embeds": [
                    {
                        "title": "🛡️ Alerta de Seguretat Activa (LexDefensor)",
                        "color": sev_data["color"],
                        "fields": fields,
                        "timestamp": timestamp_iso if timestamp_iso else None,
                        "footer": {"text": f"Filtre IPS actiu al VPS | Real IP: {VPS_IP}"}
                    }
                ]
            }

            send_webhook(payload)
            time.sleep(DELAY_BETWEEN_ALERTS)

if __name__ == "__main__":
    if os.path.exists(EVE_PATH):
        last_size = os.path.getsize(EVE_PATH)

    handler = EveHandler()
    observer = Observer()
    observer.schedule(handler, path=os.path.dirname(EVE_PATH), recursive=False)
    observer.start()

    print(f"🟢 Sistema intel·ligent en marxa al VPS (IP local: {VPS_IP})...")

    try:
        while True:
            time.sleep(1)
    except KeyboardInterrupt:
        observer.stop()
    observer.join()