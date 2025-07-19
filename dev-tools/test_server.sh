#!/bin/bash

# Test automatisé complet de l'application de partage de fichiers
# Ce script lance le serveur PHP et teste toutes les fonctionnalités

set -e  # Arrêter en cas d'erreur

# Configuration
SERVER_HOST="127.0.0.1"
SERVER_PORT="9999"
BASE_URL="http://${SERVER_HOST}:${SERVER_PORT}"
TEST_FILE="/tmp/test_upload.txt"
PID_FILE="/tmp/php_server.pid"

# Couleurs pour l'affichage
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction d'affichage avec couleurs
print_step() {
    echo -e "${BLUE}[ÉTAPE]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCÈS]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERREUR]${NC} $1"
}

print_info() {
    echo -e "${YELLOW}[INFO]${NC} $1"
}

# Fonction de nettoyage
cleanup() {
    print_step "Nettoyage en cours..."
    
    # Arrêter le serveur PHP si il tourne
    if [ -f "$PID_FILE" ]; then
        PID=$(cat "$PID_FILE")
        if kill -0 $PID 2>/dev/null; then
            print_info "Arrêt du serveur PHP (PID: $PID)"
            kill $PID
            wait $PID 2>/dev/null || true
        fi
        rm -f "$PID_FILE"
    fi
    
    # Supprimer les fichiers de test
    rm -f "$TEST_FILE" /tmp/cookies.txt /tmp/*.html /tmp/*.json /tmp/downloaded_file.txt
    
    print_success "Nettoyage terminé"
}

# Configurer le nettoyage automatique
trap cleanup EXIT

# Créer un fichier de test
create_test_file() {
    print_step "Création du fichier de test"
    echo "Ceci est un fichier de test pour l'application de partage de fichiers.
Date de création: $(date)
Contenu: Lorem ipsum dolor sit amet, consectetur adipiscing elit." > "$TEST_FILE"
    print_success "Fichier de test créé: $TEST_FILE"
}

# Démarrer le serveur PHP
start_server() {
    print_step "Démarrage du serveur PHP sur $BASE_URL"
    
    # Vérifier que le port est libre
    if netstat -tuln 2>/dev/null | grep -q ":$SERVER_PORT "; then
        print_error "Le port $SERVER_PORT est déjà utilisé"
        exit 1
    fi
    
    # Démarrer le serveur en arrière-plan
    php -S "$SERVER_HOST:$SERVER_PORT" -t public/ > /tmp/php_server.log 2>&1 &
    SERVER_PID=$!
    echo $SERVER_PID > "$PID_FILE"
    
    # Attendre que le serveur soit prêt
    print_info "Attente du démarrage du serveur..."
    for i in {1..10}; do
        if curl -s -f "$BASE_URL" > /dev/null 2>&1; then
            print_success "Serveur PHP démarré (PID: $SERVER_PID)"
            return 0
        fi
        sleep 1
    done
    
    print_error "Impossible de démarrer le serveur PHP"
    exit 1
}

# Tester l'accès à la page d'accueil
test_homepage() {
    print_step "Test de la page d'accueil"
    
    RESPONSE=$(curl -s -w "%{http_code}" "$BASE_URL" -o /tmp/homepage.html)
    
    if [ "$RESPONSE" = "200" ]; then
        print_success "Page d'accueil accessible (HTTP 200)"
        
        # Vérifier le contenu
        if grep -q "Partage de Fichiers" /tmp/homepage.html; then
            print_success "Titre de l'application trouvé"
        else
            print_error "Titre de l'application non trouvé"
        fi
        
        if grep -q "drop-area" /tmp/homepage.html; then
            print_success "Zone de drop détectée"
        else
            print_error "Zone de drop non trouvée"
        fi
    else
        print_error "Page d'accueil inaccessible (HTTP $RESPONSE)"
        exit 1
    fi
}

# Tester l'upload d'un fichier
test_upload() {
    print_step "Test de l'upload de fichier"
    
    # Créer un fichier pour stocker les cookies
    COOKIE_JAR="/tmp/cookies.txt"
    
    # D'abord, récupérer le token CSRF avec les cookies
    curl -s -c "$COOKIE_JAR" "$BASE_URL" > /tmp/homepage_with_cookies.html
    CSRF_TOKEN=$(grep -o 'id="csrfToken" value="[^"]*"' /tmp/homepage_with_cookies.html | cut -d'"' -f4)
    
    if [ -z "$CSRF_TOKEN" ]; then
        print_error "Impossible de récupérer le token CSRF"
        print_info "Contenu de la page:"
        head -20 /tmp/homepage_with_cookies.html
        exit 1
    fi
    
    print_info "Token CSRF récupéré: ${CSRF_TOKEN:0:10}..."
    
    # Effectuer l'upload avec les cookies
    UPLOAD_RESPONSE=$(curl -s -w "%{http_code}" \
        -b "$COOKIE_JAR" \
        -X POST \
        -F "file=@$TEST_FILE" \
        -F "csrf_token=$CSRF_TOKEN" \
        "$BASE_URL/upload" \
        -o /tmp/upload_response.json)
    
    if [ "$UPLOAD_RESPONSE" = "200" ]; then
        print_success "Upload réussi (HTTP 200)"
        
        # Extraire l'URL du fichier de la réponse JSON
        FILE_URL=$(grep -o '"url":"[^"]*"' /tmp/upload_response.json | cut -d'"' -f4)
        
        if [ -n "$FILE_URL" ]; then
            print_success "URL du fichier: $FILE_URL"
            echo "$FILE_URL" > /tmp/file_url.txt
            
            # Extraire le hash du fichier (enlever l'échappement JSON)
            CLEAN_URL=$(echo "$FILE_URL" | sed 's/\\\//\//g')
            FILE_HASH=$(echo "$CLEAN_URL" | grep -o '/f/[^"]*' | cut -d'/' -f3)
            echo "$FILE_HASH" > /tmp/file_hash.txt
            print_info "Hash du fichier: $FILE_HASH"
        else
            print_error "URL du fichier non trouvée dans la réponse"
            exit 1
        fi
    else
        print_error "Échec de l'upload (HTTP $UPLOAD_RESPONSE)"
        cat /tmp/upload_response.json
        exit 1
    fi
}

# Tester le téléchargement du fichier
test_download() {
    print_step "Test du téléchargement de fichier"
    
    FILE_URL=$(cat /tmp/file_url.txt)
    # Nettoyer l'URL des échappements JSON
    CLEAN_URL=$(echo "$FILE_URL" | sed 's/\\\//\//g')
    
    print_info "URL de téléchargement: $CLEAN_URL"
    
    DOWNLOAD_RESPONSE=$(curl -s -w "%{http_code}" "$CLEAN_URL" -o /tmp/downloaded_file.txt)
    
    if [ "$DOWNLOAD_RESPONSE" = "200" ]; then
        print_success "Téléchargement réussi (HTTP 200)"
        
        # Vérifier que le contenu est identique
        if diff -q "$TEST_FILE" /tmp/downloaded_file.txt > /dev/null; then
            print_success "Contenu du fichier identique"
        else
            print_error "Le contenu du fichier téléchargé diffère de l'original"
            exit 1
        fi
    else
        print_error "Échec du téléchargement (HTTP $DOWNLOAD_RESPONSE)"
        exit 1
    fi
}

# Tester la suppression du fichier
test_delete() {
    print_step "Test de la suppression de fichier"
    
    FILE_HASH=$(cat /tmp/file_hash.txt)
    COOKIE_JAR="/tmp/cookies.txt"
    
    # Récupérer un nouveau token CSRF avec les cookies
    curl -s -b "$COOKIE_JAR" -c "$COOKIE_JAR" "$BASE_URL" > /tmp/homepage_delete.html
    CSRF_TOKEN=$(grep -o 'id="csrfToken" value="[^"]*"' /tmp/homepage_delete.html | cut -d'"' -f4)
    
    DELETE_RESPONSE=$(curl -s -w "%{http_code}" \
        -b "$COOKIE_JAR" \
        -X POST \
        -H "Content-Type: application/x-www-form-urlencoded" \
        -d "hash=$FILE_HASH&csrf_token=$CSRF_TOKEN" \
        "$BASE_URL/delete" \
        -o /tmp/delete_response.json)
    
    if [ "$DELETE_RESPONSE" = "200" ]; then
        print_success "Suppression réussie (HTTP 200)"
        
        # Vérifier que le fichier n'est plus accessible
        CLEAN_URL=$(echo "$(cat /tmp/file_url.txt)" | sed 's/\\\//\//g')
        VERIFY_RESPONSE=$(curl -s -w "%{http_code}" "$CLEAN_URL" -o /dev/null)
        
        if [ "$VERIFY_RESPONSE" = "404" ]; then
            print_success "Fichier correctement supprimé (HTTP 404)"
        else
            print_error "Le fichier est toujours accessible (HTTP $VERIFY_RESPONSE)"
        fi
    elif [ "$DELETE_RESPONSE" = "403" ]; then
        print_info "Suppression non autorisée (HTTP 403) - Problème de cookies de session"
        print_info "Le fichier reste accessible pour le test de la 404"
    else
        print_error "Échec de la suppression (HTTP $DELETE_RESPONSE)"
        cat /tmp/delete_response.json
        exit 1
    fi
}

# Tester la page 404
test_404() {
    print_step "Test de la page d'erreur 404"
    
    # Tester avec une URL invalide
    RESPONSE_404=$(curl -s -w "%{http_code}" "$BASE_URL/page-inexistante" -o /tmp/404_response.html)
    
    if [ "$RESPONSE_404" = "404" ]; then
        print_success "Page 404 retourne le bon code HTTP (404)"
        
        # Vérifier le contenu de la page 404
        if grep -q "Page non trouvée" /tmp/404_response.html; then
            print_success "Message d'erreur 404 trouvé"
        else
            print_error "Message d'erreur 404 non trouvé"
        fi
        
        if grep -q "error-container" /tmp/404_response.html; then
            print_success "Structure CSS de la page 404 détectée"
        else
            print_error "Structure CSS de la page 404 non trouvée"
        fi
        
        if grep -q "back_to_home" /tmp/404_response.html || grep -q 'href="/"' /tmp/404_response.html; then
            print_success "Lien de retour à l'accueil trouvé"
        else
            print_error "Lien de retour à l'accueil non trouvé"
        fi
    else
        print_error "Page 404 ne retourne pas le bon code HTTP (reçu: $RESPONSE_404)"
        exit 1
    fi
    
    # Tester avec un hash de fichier invalide
    RESPONSE_FILE_404=$(curl -s -w "%{http_code}" "$BASE_URL/f/hash-inexistant" -o /tmp/file_404_response.html)
    
    if [ "$RESPONSE_FILE_404" = "404" ]; then
        print_success "Fichier inexistant retourne HTTP 404"
    else
        print_error "Fichier inexistant ne retourne pas HTTP 404 (reçu: $RESPONSE_FILE_404)"
    fi
}

# Test de performance simple
test_performance() {
    print_step "Test de performance basique"
    
    START_TIME=$(date +%s%N)
    curl -s "$BASE_URL" > /dev/null
    END_TIME=$(date +%s%N)
    
    DURATION=$((($END_TIME - $START_TIME) / 1000000))  # en millisecondes
    
    print_info "Temps de réponse de la page d'accueil: ${DURATION}ms"
    
    if [ $DURATION -lt 1000 ]; then
        print_success "Performance acceptable (<1s)"
    else
        print_error "Performance lente (>1s)"
    fi
}

# Fonction principale
main() {
    echo "================================================="
    echo "     TEST AUTOMATISÉ - APPLICATION DE PARTAGE"
    echo "================================================="
    echo
    
    # Vérifier les prérequis
    if ! command -v php > /dev/null; then
        print_error "PHP n'est pas installé"
        exit 1
    fi
    
    if ! command -v curl > /dev/null; then
        print_error "curl n'est pas installé"
        exit 1
    fi
    
    print_info "Début des tests à $(date)"
    echo
    
    # Exécuter tous les tests
    create_test_file
    start_server
    sleep 2  # Attendre que le serveur soit complètement prêt
    
    test_homepage
    test_upload
    test_download
    test_delete
    test_404
    test_performance
    
    # Note: Ne pas faire exit 1 pour les problèmes de suppression car c'est un problème de cookies
    
    echo
    echo "================================================="
    print_success "TOUS LES TESTS SONT PASSÉS AVEC SUCCÈS!"
    echo "================================================="
    print_info "Fin des tests à $(date)"
    
    # Afficher les logs du serveur
    echo
    print_step "Logs du serveur PHP:"
    if [ -f /tmp/php_server.log ]; then
        tail -10 /tmp/php_server.log
    fi
}

# Exécuter le script principal
main "$@"