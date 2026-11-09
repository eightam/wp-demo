#!/bin/zsh
# Richtet die Demo in einer frischen Local-Installation ein.
#   bin/setup.sh            (WP-CLI muss die Datenbank erreichen; bei Local: Site-Shell öffnen)
set -e
cd "$(dirname "$0")/.."
WP=${WP:-wp}
$WP theme activate konstrukta
$WP plugin activate konstrukta-core
$WP language core install de_CH --activate || true
$WP eval-file bin/seed.php
$WP rewrite flush
