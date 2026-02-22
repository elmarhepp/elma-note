.PHONY: init start stop restart migrate fresh build logs shell help \
        railway-setup railway-deploy railway-migrate railway-rollback \
        railway-logs railway-build-logs railway-vars railway-var-set \
        railway-status railway-open railway-ssh railway-redeploy railway-restart

# ── Farben ─────────────────────────────────────────────────────────────────
BOLD  := \033[1m
GREEN := \033[32m
CYAN  := \033[36m
RESET := \033[0m

help: ## Zeigt diese Hilfe
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) \
		| awk 'BEGIN {FS = ":.*?## "}; {printf "  $(CYAN)%-12s$(RESET) %s\n", $$1, $$2}'
	@echo ""

# ── Setup ──────────────────────────────────────────────────────────────────
init: ## Einmalig: Docker bauen, warten bis bereit, DB migrieren
	@echo "$(BOLD)$(GREEN)→ Elma Note initialisieren...$(RESET)"
	@echo "$(CYAN)→ Baue und starte Container (Composer-Install läuft im Container)...$(RESET)"
	@docker compose up -d --build --wait
	@echo "$(CYAN)→ Datenbank migrieren...$(RESET)"
	@docker compose exec app php artisan migrate --no-interaction
	@docker compose exec app php artisan storage:link --no-interaction
	@echo "$(CYAN)→ NPM-Pakete installieren...$(RESET)"
	@npm install --legacy-peer-deps
	@echo "$(BOLD)$(GREEN)✓ Bereit! Starte mit: make start$(RESET)"

# ── Start / Stop ───────────────────────────────────────────────────────────
start: ## App starten (Docker + Vite Dev Server)
	@echo "$(BOLD)$(GREEN)→ Starte Elma Note...$(RESET)"
	@docker compose up -d
	@echo "$(CYAN)→ Laravel:  http://localhost:8000$(RESET)"
	@echo "$(CYAN)→ Vite:     http://localhost:5173$(RESET)"
	@npm run dev

stop: ## App stoppen
	@echo "$(BOLD)→ Stoppe Elma Note...$(RESET)"
	@docker compose stop
	@echo "$(GREEN)✓ Gestoppt$(RESET)"

restart: stop start ## App neu starten

# ── Datenbank ──────────────────────────────────────────────────────────────
migrate: ## Neue Migrations ausführen
	@docker compose exec app php artisan migrate

fresh: ## DB komplett neu aufsetzen (ALLE DATEN WERDEN GELÖSCHT)
	@echo "$(BOLD)\033[31m⚠ Achtung: Alle Daten werden gelöscht!$(RESET)"
	@read -p "Fortfahren? [y/N] " confirm && [ "$$confirm" = "y" ] || exit 1
	@docker compose exec app php artisan migrate:fresh

# ── Build ──────────────────────────────────────────────────────────────────
build: ## Produktions-Assets bauen
	@npm run build

build-docker: ## Docker Production-Image bauen (wie Railway)
	@docker build -t elma-note:latest .

# ── Railway: Einrichtung ───────────────────────────────────────────────────
railway-setup: ## Einmalig: Railway-Projekt anlegen, MySQL hinzufügen, APP_KEY setzen
	@echo "$(BOLD)$(GREEN)→ Railway Projekt einrichten...$(RESET)"
	@railway login
	@railway init
	@railway add --database mysql
	@echo "$(CYAN)→ Setze APP_KEY...$(RESET)"
	@railway variable set APP_KEY=$$(php artisan key:generate --show)
	@echo "$(CYAN)→ Setze APP_ENV und APP_DEBUG...$(RESET)"
	@railway variable set APP_ENV=production APP_DEBUG=false
	@echo "$(BOLD)$(GREEN)✓ Setup abgeschlossen. Jetzt: make railway-deploy$(RESET)"

# ── Railway: Deployment ────────────────────────────────────────────────────
railway-deploy: ## Build + Deploy auf Railway (+ Migrations)
	@echo "$(BOLD)$(GREEN)→ Deploye auf Railway...$(RESET)"
	@railway up
	@$(MAKE) railway-migrate
	@echo "$(BOLD)$(GREEN)✓ Deployment abgeschlossen$(RESET)"
	@railway status

railway-migrate: ## Migrations auf Railway ausführen
	@echo "$(CYAN)→ Migrations auf Railway...$(RESET)"
	@railway run php artisan migrate --force

railway-rollback: ## Letzte Migration rückgängig machen
	@echo "$(BOLD)\033[31m⚠ Migration rollback auf Railway!$(RESET)"
	@read -p "Fortfahren? [y/N] " confirm && [ "$$confirm" = "y" ] || exit 1
	@railway run php artisan migrate:rollback --force

railway-redeploy: ## Letztes Deployment erneut deployen (ohne neu zu bauen)
	@railway redeploy

railway-restart: ## Service neu starten (ohne Rebuild)
	@railway restart

# ── Railway: Monitoring ────────────────────────────────────────────────────
railway-status: ## Status des Railway-Projekts anzeigen
	@railway status

railway-logs: ## Live-Logs vom Railway-Deployment streamen
	@railway logs --service elma-note

railway-build-logs: ## Build-Logs des letzten Deployments anzeigen
	@railway logs --build

railway-open: ## Railway-Dashboard im Browser öffnen
	@railway open

railway-ssh: ## SSH-Verbindung zum Railway-Service
	@railway ssh

# ── Railway: Umgebungsvariablen ────────────────────────────────────────────
railway-vars: ## Alle Railway-Umgebungsvariablen auflisten
	@railway variable list

railway-var-set: ## Variable setzen (make railway-var-set KEY=FOO VALUE=bar)
	@railway variable set $(KEY)=$(VALUE)

# ── Entwicklung ────────────────────────────────────────────────────────────
logs: ## Docker Logs anzeigen (folgen)
	@docker compose logs -f app

shell: ## Shell im App-Container öffnen
	@docker compose exec app bash

artisan: ## PHP Artisan Befehl ausführen (make artisan CMD="route:list")
	@docker compose exec app php artisan $(CMD)

tinker: ## Laravel Tinker starten
	@docker compose exec app php artisan tinker

test: ## Tests ausführen
	@docker compose exec app php artisan test
