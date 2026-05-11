# 🛡️ LexDefensor: Arquitectura Híbrida de Ciberseguretat

![Status](https://img.shields.io/badge/Project-Hito%20Gamma-green?style=for-the-badge)
![Security](https://img.shields.io/badge/Security-Wazuh%20%2B%20CrowdSec-blue?style=for-the-badge)
![Virtualization](https://img.shields.io/badge/Virtualization-Proxmox-orange?style=for-the-badge)

**LexDefensor** és una infraestructura de seguretat híbrida dissenyada per protegir actius digitals mitjançant la interconnexió segura de seus locals, entorns de proves i el núvol.

---

## 🛰️ Topologia de Xarxa
L'arquitectura es basa en tres pilars connectats mitjançant túnels **Wireguard**:
1. **Node 1 (Local):** Serveis Crítics (Proxmox).
2. **Node 2 (Cloud):** Perímetre i Proxy (VPS).
3. **Node 3 (SOC):** Monitoratge i Pentesting (Isard/Kali).

---

## 🛠️ Guia de Repliació (Instal·lació pas a pas)

### Pas 1: Infraestructura Base (Node 1 - Proxmox)
Instal·lació dels serveis de producció en contenidors LXC o VMs:

1. **Samba AD & File Server:**
   - Desplegar Ubuntu Server.
   - Instal·lar Samba: `sudo apt install samba krb5-config winbind`.
   - Provisionar el domini: `samba-tool domain provision --use-rfc2307 --interactive`.
2. **Web Server:**
   - Desplegar contenidor LXC (Debian/Ubuntu).
   - Instal·lar Apache: `sudo apt install apache2`.

### Pas 2: Perímetre de Defensa (Node 2 - VPS)
Configuració del gateway extern i seguretat de xarxa:

1. **VPN Wireguard (L'enllaç):**
   - Instal·lar Wireguard: `sudo apt install wireguard`.
   - Generar claus i configurar `/etc/wireguard/wg0.conf` per connectar Node 1 i Node 3.
2. **Nginx Proxy Manager (NPM):**
   - Instal·lar via Docker Compose per gestionar el domini `.com/.es` i SSL (Let's Encrypt).
3. **CrowdSec (IPS):**
   - Instal·lar l'agent: `curl -s https://packagecloud.io/install/repositories/crowdsec/crowdsec/script.deb.sh | sudo bash`.
   - Instal·lar la bouncer per a Nginx: `sudo apt install crowdsec-firewall-bouncer-iptables`.

### Pas 3: Monitoratge i SOC (Node 3 - Isard/Kali)
Implementació de la vigilància activa:

1. **Wazuh (SIEM/XDR):**
   - Instal·lació ràpida (All-in-one):
     ```bash
     curl -sO [https://packages.wazuh.com/4.x/wazuh-install.sh](https://packages.wazuh.com/4.x/wazuh-install.sh) && sudo bash wazuh-install.sh -a
     ```
2. **Zabbix (Rendiment):**
   - Instal·lar Zabbix Server i connectar els agents dels altres nodes per monitorar CPU/RAM.
3. **Auditoria de Vulnerabilitats (OpenVAS en Kali):**
   - Instal·lar motor GVM: `sudo apt install gvmd gsad openvas-scanner`.
   - Inicialitzar: `sudo gvm-setup`.
   - *Nota de manteniment:* Si la DB de GVM es desactualitza, executar: `sudo -u _gvm gvmd --migrate`.

---

## 📊 Estat de la Implementació

| Objectiu | Tecnologia | Estat |
| :--- | :--- | :---: |
| **VPN Inter-Node** | Wireguard | ✅ |
| **Proxy Invers** | Nginx Proxy Manager | ✅ |
| **Identitat** | Samba Active Directory | ✅ |
| **IDS/IPS** | Wazuh + CrowdSec | ✅ |
| **Auditoria** | OpenVAS (GVM) | ✅ |

---

## 🚀 Desafiaments i Hardening

* **Migració DNS:** Abandonament de DuckDNS per dominis professionals per evitar el bloqueig de ports i millorar el SEO/Reputació d'IP.
* **Seguretat VPN:** S'utilitzen claus privades úniques per node (`Peer-to-Peer`) per evitar moviments laterals no autoritzats.
* **Hardening Proxmox:** Decisió de no utilitzar LUKS post-instal·lació per evitar corrupció de dades en l'Active Directory, movent el xifratge a nivell d'aplicació i túnels VPN.

---

## 🔒 Pentesting i Compliance
L'infraestructura se sotmet a escanejos setmanals amb **OpenVAS** i revisions de logs amb **Wazuh** per complir amb els estàndards de seguretat vigents.

---
*LexDefensor 2026 - Desenvolupat per a la seguretat i integritat de sistemes híbrids.*
