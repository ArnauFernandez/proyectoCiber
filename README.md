# # 🛡️ LexDefensor: Arquitectura Híbrida de Ciberseguretat

![Status](https://img.shields.io/badge/Project-Hito%20Gamma-green?style=for-the-badge)
![Security](https://img.shields.io/badge/Security-Wazuh%20%2B%20CrowdSec-blue?style=for-the-badge)
![Virtualization](https://img.shields.io/badge/Virtualization-Proxmox-orange?style=for-the-badge)

**LexDefensor** és una infraestructura de seguretat híbrida dissenyada per protegir actius digitals mitjançant la interconnexió segura de seus locals, entorns de proves i el núvol. El projecte se centra en la centralització de la identitat (Active Directory) i la vigilància activa (SOC).

---

## 🛰️ Topologia de l'Estructura (Nodes)

L'arquitectura es divideix en tres nodes principals connectats via **VPN Wireguard**:

### 🏗️ Node 1: Proxmox VE (Seu Local)
És el nucli de serveis de la infraestructura. Gestiona la identitat i l'emmagatzematge.
* **Samba  (Ubuntu server):** Gestió centralitzada d'usuaris i polítiques.
* **Samba File Server:** Recursos compartits en xarxa.
* **Samba Client:** Estació de treball per a proves de consum de recursos.
* **Apache Web Server:** Servidor web intern per a aplicacions corporatives.
* **Nota sobre emmagatzematge:** S'ha optat per una configuració estàndard (sense LUKS) per prioritzar l'estabilitat dels serveis desplegats post-instal·lació.

### ☁️ Node 2: VPS Extern (Gateway de Seguretat)
Actua com a perímetre de defensa i punt d'accés global.
* **Nginx Proxy Manager (NPM):** Gestió de dominis professionals i certificats SSL.
* **CrowdSec:** IPS (Intrusion Prevention System) que bloqueja IPs malicioses automàticament.
* **Wireguard Server:** Orquestrador de tots els túnels VPN del projecte.

### 🧪 Node 3: VM Isard (Monitoratge & SOC)
Entorn virtualitzat dedicat exclusivament a la seguretat i salut del sistema.
* **Wazuh Manager:** SIEM/XDR per a l'anàlisi de logs i detecció d'intrusions en temps real.
* **Zabbix Server:** Monitoratge de rendiment (CPU, RAM, Red) de tots els contenidors i nodes.

---

## 📊 La Quiniela: Estat del Projecte

| Objectiu | Estat | Implementació |
| :--- | :---: | :--- |
| **Accés Extern Global** | ✅ | Domini .com/.es + Proxy Invers |
| **Identitat Centralitzada** | ✅ | Active Directory + GPO |
| **Monitoratge SIEM** | ✅ | Stack Wazuh complet |
| **Control de Rendiment** | ✅ | Dashboards de Zabbix |
| **Cifratge LUKS** | ⚠️ | Descartat (Prioritat estabilitat Proxmox) |

---

## 🛠️ Stack Tecnològic

| Categoria | Tecnologies |
| :--- | :--- |
| **Virtualització** | Proxmox VE, Isard VDI, Containers LXC |
| **Seguretat** | Wazuh, CrowdSec, Wireguard (VPN) |
| **Xarxes** | Nginx Proxy Manager, DNS Professionals |
| **Sistemes** | Windows Server (AD), Linux (Samba, Apache) |
| **Monitoratge** | Zabbix, Grafana (opcional) |

---

## 🚀 Desafiaments Superats

1.  **Bloquejos de Xarxa:** Migració de DuckDNS a dominis professionals per evitar el filtratge de xarxes corporatives.
2.  **Configuració VPN:** Resolució de conflictes d'IPs mitjançant la generació de claus úniques per a cada *peer*.
3.  **Estabilitat de Proxmox:** Decisió conscient de no implementar LUKS post-instal·lació per no comprometre la continuïtat de l'Active Directory i el servidor Samba.

---

## 🔒 Seguretat i Auditories
L'infraestructura està preparada per a auditories de seguretat, amb contractes de **Pentesting** redactats i un entorn de proves (Isard) aïllat per a simulacions d'atac i defensa.

---
*LexDefensor 2026 - Desenvolupat per a la seguretat i integritat de sistemes híbrids.*
